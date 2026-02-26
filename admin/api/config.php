<?php
/**
 * Configuración de Base de Datos
 * 
 * Descomenta y configura cuando tengas un servidor MySQL disponible
 */

// ===== CONFIGURACIÓN ACTUAL (JSON) =====
define('DB_TYPE', 'json'); // Cambiar a 'mysql' cuando sea necesario

// ===== CONFIGURACIÓN MYSQL (Descomentar cuando esté disponible) =====
/*
define('DB_TYPE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_USER', 'usuario_lacrem');
define('DB_PASS', 'contraseña_segura');
define('DB_NAME', 'lacrem_db');
define('DB_CHARSET', 'utf8mb4');
*/

// ===== FUNCIONES DE INICIALIZACIÓN =====

/**
 * Obtener conexión a base de datos
 */
function getDBConnection() {
    if (DB_TYPE === 'mysql') {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    return null;
}

/**
 * Inicializar base de datos MySQL
 */
function initMySQLDatabase() {
    $pdo = getDBConnection();
    
    $sql = [
        // Tabla de clientes
        "CREATE TABLE IF NOT EXISTS clientes (
            id VARCHAR(50) PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            telefono VARCHAR(20) NOT NULL,
            direccion TEXT,
            empresa VARCHAR(255),
            notas TEXT,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_fecha (fecha_creacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla de pedidos
        "CREATE TABLE IF NOT EXISTS pedidos (
            id VARCHAR(50) PRIMARY KEY,
            cliente_id VARCHAR(50) NOT NULL,
            descuento DECIMAL(5,2) DEFAULT 0,
            notas TEXT,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            INDEX idx_cliente (cliente_id),
            INDEX idx_fecha (fecha)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla de items del pedido
        "CREATE TABLE IF NOT EXISTS pedido_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pedido_id VARCHAR(50) NOT NULL,
            producto_id VARCHAR(50) NOT NULL,
            nombre VARCHAR(255) NOT NULL,
            cantidad INT NOT NULL,
            precio DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
            INDEX idx_pedido (pedido_id),
            INDEX idx_producto (producto_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    foreach ($sql as $statement) {
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            error_log("Error creating table: " . $e->getMessage());
        }
    }
}

/**
 * Script de migración: Pasar datos de JSON a MySQL
 */
function migrateJSONToMySQL() {
    if (DB_TYPE !== 'mysql') {
        return ['success' => false, 'message' => 'Base de datos no es MySQL'];
    }
    
    $pdo = getDBConnection();
    
    // Importar funciones JSON
    require_once __DIR__ . '/db_functions.php';
    
    // Obtener datos JSON
    $clientes = get_clientes();
    $pedidos = get_pedidos();
    
    try {
        // Transacción
        $pdo->beginTransaction();
        
        // Insertar clientes
        $stmt = $pdo->prepare("
            INSERT INTO clientes (id, nombre, email, telefono, direccion, empresa, notas, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            nombre = VALUES(nombre),
            telefono = VALUES(telefono),
            direccion = VALUES(direccion),
            empresa = VALUES(empresa),
            notas = VALUES(notas)
        ");
        
        foreach ($clientes as $cliente) {
            $stmt->execute([
                $cliente['id'] ?? uniqid('cli_'),
                $cliente['nombre'],
                $cliente['email'],
                $cliente['telefono'],
                $cliente['direccion'] ?? null,
                $cliente['empresa'] ?? null,
                $cliente['notas'] ?? null,
                $cliente['fecha_creacion'] ?? date('Y-m-d H:i:s')
            ]);
        }
        
        // Insertar pedidos e items
        $stmt_pedidos = $pdo->prepare("
            INSERT INTO pedidos (id, cliente_id, descuento, notas, fecha)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt_items = $pdo->prepare("
            INSERT INTO pedido_items (pedido_id, producto_id, nombre, cantidad, precio, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($pedidos as $pedido) {
            $stmt_pedidos->execute([
                $pedido['id'] ?? uniqid('ped_'),
                $pedido['cliente_id'],
                $pedido['descuento'] ?? 0,
                $pedido['notas'] ?? null,
                $pedido['fecha'] ?? date('Y-m-d H:i:s')
            ]);
            
            if (isset($pedido['items']) && is_array($pedido['items'])) {
                foreach ($pedido['items'] as $item) {
                    $stmt_items->execute([
                        $pedido['id'],
                        $item['producto_id'],
                        $item['nombre'],
                        $item['cantidad'],
                        $item['precio'],
                        $item['subtotal']
                    ]);
                }
            }
        }
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Migración completada exitosamente',
            'clientes_migrados' => count($clientes),
            'pedidos_migrados' => count($pedidos)
        ];
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Error en migración: ' . $e->getMessage()];
    }
}

/**
 * Obtener información de la base de datos actual
 */
function getDatabaseInfo() {
    return [
        'tipo' => DB_TYPE,
        'host' => DB_TYPE === 'mysql' ? DB_HOST : 'No aplicable',
        'base_datos' => DB_TYPE === 'mysql' ? DB_NAME : 'JSON Files',
        'archivos' => [
            'clientes' => __DIR__ . '/../data/clientes.json',
            'pedidos' => __DIR__ . '/../data/pedidos.json'
        ]
    ];
}
