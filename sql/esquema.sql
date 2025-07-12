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
    lat DECIMAL(10,8),
    lng DECIMAL(11,8),
    sexo enum('femenino','masculino') DEFAULT NULL,
    alergias text DEFAULT NULL,
    enfermedades_previas text DEFAULT NULL,
    medicamentos text DEFAULT NULL,
    antecedentes_familiares text DEFAULT NULL,
    cirugias text DEFAULT NULL,
    otros_datos text DEFAULT NULL
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

  CREATE TABLE especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL
  );

  CREATE TABLE servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    especialidad_id INT NOT NULL,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
  );

