-- Tabla de roles: Almacena los diferentes roles de usuario dentro del sistema (ej., administrador, usuario).
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Identificador único para cada rol
    nombre VARCHAR(50) NOT NULL UNIQUE -- Nombre del rol (ej., "administrador", "usuario"), único en la tabla
);

-- Insertar roles iniciales
INSERT INTO roles (nombre) VALUES ('administrador'), ('usuario');

-- Tabla de usuarios: Registra las cuentas de los usuarios que acceden al sistema.
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Identificador único del usuario
    nombre VARCHAR(100) NOT NULL, -- Nombre del usuario
    apellido VARCHAR(100) NOT NULL, -- Apellido del usuario
    documento VARCHAR(20) NOT NULL UNIQUE, -- Documento de identidad, único
    jerarquia VARCHAR(100), -- Rango o jerarquía del usuario (opcional)
    rol_id INT NOT NULL, -- Relación con la tabla roles (determina permisos)
    clave VARCHAR(255) NOT NULL, -- Clave encriptada del usuario (antes "contraseña")
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de creación del usuario
    FOREIGN KEY (rol_id) REFERENCES roles(id) -- Relación con la tabla roles
);

-- Tabla de personas
CREATE TABLE personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    documento VARCHAR(20) NOT NULL UNIQUE,
    edad INT,
    sexo ENUM('masculino', 'femenino', 'otro'),
    foto VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de domicilios
CREATE TABLE domicilios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    persona_id INT NOT NULL,
    calle VARCHAR(100) NOT NULL,
    numeracion VARCHAR(20),
    barrio_villa VARCHAR(100),
    mzna VARCHAR(20),
    casa VARCHAR(20),
    departamento VARCHAR(50),
    provincia VARCHAR(100),
    FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE
);

-- Tabla de prófugos
CREATE TABLE profugos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    persona_id INT NOT NULL,
    pdf_captura VARCHAR(255) NOT NULL,
    fecha_declarado_profugo DATE NOT NULL,
    domicilio_alternativo TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE
);

-- Tabla de detenidos
CREATE TABLE detenidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    persona_id INT NOT NULL,
    motivo_detencion TEXT NOT NULL,
    legajo VARCHAR(50),
    unidad_fiscal VARCHAR(100),
    fecha_detencion DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE
);

-- Tabla de vehículos con pedido de secuestro
CREATE TABLE vehiculos_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    dominio VARCHAR(20) NOT NULL UNIQUE,
    color VARCHAR(50),
    titular_id INT NOT NULL,
    foto_vehiculo VARCHAR(255),
    pdf_secuestro VARCHAR(255) NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (titular_id) REFERENCES personas(id) ON DELETE CASCADE
);

-- Si la tabla usuarios ya existe y solo quieres modificar la columna "contraseña" a "clave":
ALTER TABLE usuarios CHANGE contraseña clave VARCHAR(255) NOT NULL;
