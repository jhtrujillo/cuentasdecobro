-- Script de creación de la base de datos y tablas
CREATE DATABASE IF NOT EXISTS cuentas_cobro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cuentas_cobro;

-- Tabla para almacenar emisores (acreedores) predeterminados
CREATE TABLE IF NOT EXISTS emisores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    documento VARCHAR(50) NOT NULL, -- C.C. o NIT
    banco VARCHAR(100) NOT NULL,
    tipo_cuenta VARCHAR(50) NOT NULL, -- Ahorros / Corriente
    numero_cuenta VARCHAR(100) NOT NULL,
    firma_base64 LONGTEXT NULL, -- Firma guardada en formato base64
    es_predeterminado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para registrar cada Cuenta de Cobro
CREATE TABLE IF NOT EXISTS cuentas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_cuenta INT NOT NULL, -- Consecutivo numérico
    fecha DATE NOT NULL,
    deudor_nombre VARCHAR(255) NOT NULL, -- Cliente que debe pagar
    deudor_nit VARCHAR(50) NOT NULL, -- NIT / C.C. del cliente
    acreedor_nombre VARCHAR(255) NOT NULL, -- Persona que cobra (Emisor)
    acreedor_documento VARCHAR(50) NOT NULL, -- C.C. o NIT de la persona que cobra
    valor DECIMAL(15, 2) NOT NULL, -- Valor numérico (ej. 4741188)
    valor_letras VARCHAR(500) NOT NULL, -- Valor convertido a letras
    concepto TEXT NOT NULL, 
    rango_fechas VARCHAR(255) NULL,  -- Descripción detallada del cobro y datos bancarios
    firma_base64 LONGTEXT NULL, -- Copia de la firma usada en esta cuenta (base64)
    pagado TINYINT(1) DEFAULT 0, -- Estado de cobro (0 = Pendiente, 1 = Cobrado)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para gestionar clientes (deudores frecuentes)
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    nit VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de semilla para clientes
INSERT INTO clientes (nombre, nit) VALUES ('GRUAS Y TRANSPORTE DE COLOMBIA', '900667447-6');

-- Tabla para gestionar gastos mensuales
CREATE TABLE IF NOT EXISTS gastos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    concepto VARCHAR(255) NOT NULL,
    valor DECIMAL(15, 2) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    ejecutado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para gestionar otros ingresos no relacionados a cuentas de cobro
CREATE TABLE IF NOT EXISTS otros_ingresos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    concepto VARCHAR(255) NOT NULL,
    valor DECIMAL(15, 2) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

