CREATE DATABASE IF NOT EXISTS biblioteca;
USE biblioteca;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100),
  documento VARCHAR(20) UNIQUE
);

CREATE TABLE IF NOT EXISTS cursos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS matriculas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT,
  id_curso INT,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
  FOREIGN KEY (id_curso) REFERENCES cursos(id)
);

-- Insertar cursos
INSERT INTO cursos (nombre) VALUES 
  ('Matematicas'),
  ('Fisica'),
  ('Quimica'),
  ('Programacion'),
  ('Historia'),
  ('Literatura');

-- Insertar usuarios
INSERT INTO usuarios (nombre, documento) VALUES 
  ('Ana Gomez', '123456789'),
  ('Carlos Perez', '987654321'),
  ('Laura Martinez', '1122334455');
