CREATE DATABASE IF NOT EXISTS biblioteca;
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'tu_clave';
GRANT ALL PRIVILEGES ON biblioteca.* TO 'app_user'@'localhost';
FLUSH PRIVILEGES;
