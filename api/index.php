<?php
require_once 'config.php';

$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Eliminar parámetros GET y prefijos
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/api/', '', $path);
$segments = explode('/', trim($path, '/'));

// Respuesta por defecto
$response = [];
$status_code = 200;

try {
    // ------------------- RUTAS DE INGREDIENTES -------------------
    if ($segments[0] === 'ingredientes') {
        $id = isset($segments[1]) ? (int)$segments[1] : null;

        // GET /api/ingredientes
        if ($request_method === 'GET' && !$id) {
            $stmt = $pdo->query("SELECT * FROM ingredientes ORDER BY id");
            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // POST /api/ingredientes
        elseif ($request_method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['nombre']) || empty($data['unidad'])) {
                throw new Exception('Nombre y unidad son requeridos');
            }
            $stmt = $pdo->prepare("INSERT INTO ingredientes (nombre, unidad) VALUES (?, ?)");
            $stmt->execute([$data['nombre'], $data['unidad']]);
            $response = ['id' => $pdo->lastInsertId(), 'message' => 'Ingrediente creado'];
            $status_code = 201;
        }
        // PUT /api/ingredientes/{id}
        elseif ($request_method === 'PUT' && $id) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE ingredientes SET nombre = ?, unidad = ? WHERE id = ?");
            $stmt->execute([$data['nombre'], $data['unidad'], $id]);
            $response = ['message' => 'Ingrediente actualizado'];
        }
        // DELETE /api/ingredientes/{id}
        elseif ($request_method === 'DELETE' && $id) {
            // Verificar si está siendo usado
            $check = $pdo->prepare("SELECT COUNT(*) FROM pastel_ingrediente WHERE ingrediente_id = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                throw new Exception('No se puede eliminar el ingrediente porque está asignado a uno o más pasteles');
            }
            $stmt = $pdo->prepare("DELETE FROM ingredientes WHERE id = ?");
            $stmt->execute([$id]);
            $response = ['message' => 'Ingrediente eliminado'];
        }
        else {
            throw new Exception('Ruta no válida para ingredientes', 404);
        }
    }
    // ------------------- RUTAS DE PASTELES -------------------
    elseif ($segments[0] === 'pasteles') {
        $id = isset($segments[1]) ? (int)$segments[1] : null;

        // GET /api/pasteles (lista básica)
        if ($request_method === 'GET' && !$id) {
            $stmt = $pdo->query("SELECT id, nombre, descripcion, created_at FROM pasteles ORDER BY id");
            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // GET /api/pasteles/{id} (con sus ingredientes)
        elseif ($request_method === 'GET' && $id) {
            $stmt = $pdo->prepare("SELECT * FROM pasteles WHERE id = ?");
            $stmt->execute([$id]);
            $pastel = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$pastel) {
                throw new Exception('Pastel no encontrado', 404);
            }
            // Obtener ingredientes con cantidad
            $stmt2 = $pdo->prepare("
                SELECT i.id, i.nombre, i.unidad, pi.cantidad
                FROM pastel_ingrediente pi
                JOIN ingredientes i ON pi.ingrediente_id = i.id
                WHERE pi.pastel_id = ?
            ");
            $stmt2->execute([$id]);
            $pastel['ingredientes'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            $response = $pastel;
        }
        // POST /api/pasteles
        elseif ($request_method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['nombre'])) {
                throw new Exception('El nombre del pastel es obligatorio');
            }
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO pasteles (nombre, descripcion) VALUES (?, ?)");
                $stmt->execute([$data['nombre'], $data['descripcion'] ?? '']);
                $pastel_id = $pdo->lastInsertId();

                // Asignar ingredientes si vienen
                if (!empty($data['ingredientes'])) {
                    $insert = $pdo->prepare("INSERT INTO pastel_ingrediente (pastel_id, ingrediente_id, cantidad) VALUES (?, ?, ?)");
                    foreach ($data['ingredientes'] as $item) {
                        $insert->execute([$pastel_id, $item['id'], $item['cantidad']]);
                    }
                }
                $pdo->commit();
                $response = ['id' => $pastel_id, 'message' => 'Pastel creado'];
                $status_code = 201;
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
        // PUT /api/pasteles/{id}
        elseif ($request_method === 'PUT' && $id) {
            $data = json_decode(file_get_contents('php://input'), true);
            $pdo->beginTransaction();
            try {
                // Actualizar datos del pastel
                $stmt = $pdo->prepare("UPDATE pasteles SET nombre = ?, descripcion = ? WHERE id = ?");
                $stmt->execute([$data['nombre'], $data['descripcion'] ?? '', $id]);

                // Reemplazar ingredientes: borrar existentes e insertar nuevos
                $del = $pdo->prepare("DELETE FROM pastel_ingrediente WHERE pastel_id = ?");
                $del->execute([$id]);

                if (!empty($data['ingredientes'])) {
                    $insert = $pdo->prepare("INSERT INTO pastel_ingrediente (pastel_id, ingrediente_id, cantidad) VALUES (?, ?, ?)");
                    foreach ($data['ingredientes'] as $item) {
                        $insert->execute([$id, $item['id'], $item['cantidad']]);
                    }
                }
                $pdo->commit();
                $response = ['message' => 'Pastel actualizado'];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
        // DELETE /api/pasteles/{id}
        elseif ($request_method === 'DELETE' && $id) {
            $stmt = $pdo->prepare("DELETE FROM pasteles WHERE id = ?");
            $stmt->execute([$id]);
            $response = ['message' => 'Pastel eliminado'];
        }
        else {
            throw new Exception('Ruta no válida para pasteles', 404);
        }
    }
    // ------------------- REPORTE: todos los pasteles con ingredientes -------------------
    elseif ($segments[0] === 'reporte') {
        if ($request_method === 'GET') {
            $stmt = $pdo->query("
                SELECT p.id, p.nombre, p.descripcion,
                       i.id as ingrediente_id, i.nombre as ingrediente_nombre, i.unidad, pi.cantidad
                FROM pasteles p
                LEFT JOIN pastel_ingrediente pi ON p.id = pi.pastel_id
                LEFT JOIN ingredientes i ON pi.ingrediente_id = i.id
                ORDER BY p.id, i.id
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $reporte = [];
            foreach ($rows as $row) {
                $pid = $row['id'];
                if (!isset($reporte[$pid])) {
                    $reporte[$pid] = [
                        'id' => $pid,
                        'nombre' => $row['nombre'],
                        'descripcion' => $row['descripcion'],
                        'ingredientes' => []
                    ];
                }
                if ($row['ingrediente_id']) {
                    $reporte[$pid]['ingredientes'][] = [
                        'id' => $row['ingrediente_id'],
                        'nombre' => $row['ingrediente_nombre'],
                        'unidad' => $row['unidad'],
                        'cantidad' => $row['cantidad']
                    ];
                }
            }
            $response = array_values($reporte);
        } else {
            throw new Exception('Método no permitido', 405);
        }
    }
    else {
        throw new Exception('Endpoint no encontrado', 404);
    }
} catch (Exception $e) {
    $status_code = $e->getCode() ?: 500;
    $response = ['error' => $e->getMessage()];
}

http_response_code($status_code);
echo json_encode($response);
?>