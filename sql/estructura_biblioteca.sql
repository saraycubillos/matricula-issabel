
CREATE DATABASE IF NOT EXISTS biblioteca;
USE biblioteca;

DROP TABLE IF EXISTS matriculas;
DROP TABLE IF EXISTS cursos;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    documento VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE matriculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_curso INT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_curso) REFERENCES cursos(id)
);

INSERT INTO usuarios (documento, nombre) VALUES
('123456789', 'Juan Pérez'),
('987654321', 'María Gómez');

INSERT INTO cursos (nombre) VALUES
('Python para Principiantes'),
('Introducción a la Inteligencia Artificial'),
('Desarrollo Web con Flask'),
('Bases de Datos con MySQL'),
('Machine Learning Intermedio');
