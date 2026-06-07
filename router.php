<?php
// router.php - Para el servidor integrado de PHP
$request_uri = $_SERVER['REQUEST_URI'];

// Redirigir todas las llamadas a /api/... hacia api/index.php
if (strpos($request_uri, '/api/') === 0) {
    require __DIR__ . '/api/index.php';
    exit;
}

// Para el frontend (index.html) u otros archivos estáticos
return false; // PHP server sirve el archivo directamente