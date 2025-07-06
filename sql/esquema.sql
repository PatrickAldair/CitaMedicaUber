CREATE DATABASE IF NOT EXISTS medicapp;
USE medicapp;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('paciente','doctor') NOT NULL,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  edad INT,
  especialidad VARCHAR(100),
  servicios TEXT,
  lat DECIMAL(10,8),
  lng DECIMAL(11,8)
);

CREATE TABLE citas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_paciente INT NOT NULL,
  id_doctor INT NOT NULL,
  fecha DATETIME NOT NULL,
  estado ENUM('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  FOREIGN KEY (id_paciente) REFERENCES usuarios(id),
  FOREIGN KEY (id_doctor) REFERENCES usuarios(id)
);
