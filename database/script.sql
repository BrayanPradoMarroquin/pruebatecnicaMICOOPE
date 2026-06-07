-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS pasteleria;
USE pasteleria;

-- Tabla de pasteles
CREATE TABLE IF NOT EXISTS pasteles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de ingredientes
CREATE TABLE IF NOT EXISTS ingredientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    unidad VARCHAR(50) NOT NULL
);

-- Tabla intermedia (maestro-detalle)
CREATE TABLE IF NOT EXISTS pastel_ingrediente (
    pastel_id INT NOT NULL,
    ingrediente_id INT NOT NULL,
    cantidad VARCHAR(50) NOT NULL,
    PRIMARY KEY (pastel_id, ingrediente_id),
    FOREIGN KEY (pastel_id) REFERENCES pasteles(id) ON DELETE CASCADE,
    FOREIGN KEY (ingrediente_id) REFERENCES ingredientes(id) ON DELETE RESTRICT
);

-- Insertar algunos datos de ejemplo
INSERT INTO ingredientes (nombre, unidad) VALUES
('Harina', 'gramos'),
('Huevos', 'unidades'),
('Azúcar', 'gramos'),
('Mantequilla', 'gramos'),
('Vainilla', 'cucharaditas');

INSERT INTO pasteles (nombre, descripcion) VALUES
('Pastel de Vainilla', 'Esponjoso y suave'),
('Pastel de Chocolate', 'Intenso sabor a cacao');

INSERT INTO pastel_ingrediente (pastel_id, ingrediente_id, cantidad) VALUES
(1, 1, '250'),
(1, 2, '3'),
(1, 3, '200'),
(2, 1, '200'),
(2, 2, '2'),
(2, 3, '180'),
(2, 4, '100');