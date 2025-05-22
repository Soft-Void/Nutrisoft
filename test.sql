-- 1. Crear la tabla de pacientes
CREATE TABLE IF NOT EXISTS pacientes (
  id_paciente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre           VARCHAR(100)    NOT NULL,
  edad             TINYINT UNSIGNED,
  sexo             ENUM('M','F','Otro') NOT NULL,
  telefono         VARCHAR(20),
  email            VARCHAR(100),
  direccion        VARCHAR(255),
  alergias         TEXT,
  enfermedades     TEXT,
  antecedentes     TEXT,
  metas_nutricionales TEXT,
  fecha_registro   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Tabla para guardar el historial de consultas
CREATE TABLE IF NOT EXISTS consultas (
  id_consulta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_paciente  INT UNSIGNED                NOT NULL,
  fecha        DATETIME     NOT NULL,
  nutriologo   VARCHAR(100) NOT NULL,
  notas        TEXT,
  FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS seguimiento_nutricional (
  id_seguimiento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_paciente INT UNSIGNED NOT NULL,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  peso DECIMAL(5,2) NOT NULL,
  altura DECIMAL(4,2) NOT NULL,
  imc DECIMAL(5,2) NOT NULL,
  calorias_consumidas INT NOT NULL,
  metas_alcanzadas TEXT,
  FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Tabla para gestionar nutriólogos
CREATE TABLE IF NOT EXISTS nutriologos (
  id_nutriologo INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre            VARCHAR(100)    NOT NULL,
  especialidad      VARCHAR(100),
  cedula_profesional VARCHAR(50),
  telefono          VARCHAR(20),
  email             VARCHAR(100),
  fecha_registro    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Tabla para asignar pacientes a nutriólogos
CREATE TABLE IF NOT EXISTS nutriologo_paciente (
  id_asignacion        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_nutriologo        INT UNSIGNED NOT NULL,
  id_paciente          INT UNSIGNED NOT NULL,
  fecha_asignacion     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_nutriologo) REFERENCES nutriologos(id_nutriologo) ON DELETE CASCADE,
  FOREIGN KEY (id_paciente)   REFERENCES pacientes(id_paciente)    ON DELETE CASCADE,
  UNIQUE KEY uq_asignacion (id_nutriologo, id_paciente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Tabla para gestionar citas
CREATE TABLE IF NOT EXISTS citas (
  id_cita INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_paciente INT UNSIGNED NOT NULL,
  id_nutriologo INT UNSIGNED NOT NULL,
  fecha DATETIME NOT NULL,
  observaciones TEXT,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_paciente)    REFERENCES pacientes(id_paciente)    ON DELETE CASCADE,
  FOREIGN KEY (id_nutriologo)  REFERENCES nutriologos(id_nutriologo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
