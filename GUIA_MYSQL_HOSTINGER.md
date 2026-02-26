# 🗄️ GUÍA DE CONFIGURACIÓN MYSQL EN HOSTINGER - LA CREM

## PASO 1️⃣: Crear Base de Datos en Hostinger

### En el Panel de Control de Hostinger:

1. Accede a **cPanel** (tu.dominio.com/cpanel)
2. Busca y abre **MySQL Databases** o **Databases**
3. Haz clic en **Create Database**

**Rellena los campos:**
```
Database Name: lacrem_db
```

4. Haz clic en **Create Database** ✅

---

## PASO 2️⃣: Crear Usuario MySQL

### Sigue el mismo panel:

1. En la sección **MySQL Users**, haz clic en **Add User**

**Rellena los campos:**
```
Username: lacrem_user
Password: GeneraUnaContraseñaSegura123!
```

2. Haz clic en **Create User** ✅

---

## PASO 3️⃣: Asignar Permisos al Usuario

### En el mismo panel:

1. Busca **Add User to Database** o **Privileges**
2. Selecciona:
   - **User:** lacrem_user
   - **Database:** lacrem_db

3. Haz clic en **Add User** ✅

4. **Marca todos los permisos** (All Privileges):
   - ✅ SELECT
   - ✅ INSERT
   - ✅ UPDATE
   - ✅ DELETE
   - ✅ CREATE
   - ✅ ALTER
   - ✅ DROP

5. Haz clic en **Make Changes** ✅

---

## PASO 4️⃣: Actualizar config.php

### Edita el archivo: `/admin/api/config.php`

**Reemplaza:**
```php
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
```

**Por:**
```php
// ===== CONFIGURACIÓN ACTUAL (JSON) =====
// define('DB_TYPE', 'json'); // Cambiar a 'mysql' cuando sea necesario

// ===== CONFIGURACIÓN MYSQL (Descomentar cuando esté disponible) =====
define('DB_TYPE', 'mysql');
define('DB_HOST', 'localhost');  // Usualmente es localhost en Hostinger
define('DB_USER', 'lacrem_user');  // El usuario que creaste
define('DB_PASS', 'TuContraseñaSegura123!');  // La contraseña que creaste
define('DB_NAME', 'lacrem_db');  // El nombre de la BD que creaste
define('DB_CHARSET', 'utf8mb4');
```

---

## PASO 5️⃣: Actualizar db_functions.php

### Abre: `/admin/api/db_functions.php`

**Cambia estas funciones para usar MySQL en lugar de JSON:**

#### Para `save_cliente()`:
```php
function save_cliente($cliente) {
    if (DB_TYPE === 'mysql') {
        $pdo = getDBConnection();
        $id = $cliente['id'] ?? uniqid('cli_');
        
        $stmt = $pdo->prepare("
            INSERT INTO clientes (id, nombre, email, telefono, direccion, empresa, notas)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            nombre = VALUES(nombre),
            email = VALUES(email),
            telefono = VALUES(telefono),
            direccion = VALUES(direccion),
            empresa = VALUES(empresa),
            notas = VALUES(notas)
        ");
        
        return $stmt->execute([
            $id,
            $cliente['nombre'],
            $cliente['email'],
            $cliente['telefono'],
            $cliente['direccion'] ?? null,
            $cliente['empresa'] ?? null,
            $cliente['notas'] ?? null
        ]) ? ['success' => true, 'id' => $id] : ['success' => false];
    }
    // ... resto del código JSON
}
```

---

## PASO 6️⃣: Crear Tabla de Usuarios

### En cPanel, abre **phpMyAdmin**

1. Selecciona tu base de datos: `lacrem_db`
2. Abre la pestaña **SQL**
3. **Copia y pega este código:**

```sql
CREATE TABLE IF NOT EXISTS usuarios (
    id VARCHAR(50) PRIMARY KEY,
    usuario VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    rol VARCHAR(50) DEFAULT 'vendedor',
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar admin por defecto (usuario: admin, contraseña: admin123)
INSERT INTO usuarios (id, usuario, password, nombre, email, rol, activo)
VALUES (
    'admin_001',
    'admin',
    '$2y$10$9LJr.9qKvlHLXCAqXmfbKOVDhPvI7iEBQPkVZSEz4SqQC/L9vN.R2',
    'Administrador',
    'admin@lacrem.com',
    'admin',
    TRUE
);
```

4. Haz clic en **Go** ✅

---

## PASO 7️⃣: Crear las Tablas Restantes

### En phpMyAdmin, copia y pega:

```sql
-- Tabla de Clientes
CREATE TABLE IF NOT EXISTS clientes (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id VARCHAR(50) PRIMARY KEY,
    cliente_id VARCHAR(50) NOT NULL,
    vendedor_id VARCHAR(50),
    vendedor_nombre VARCHAR(255),
    total DECIMAL(10,2) DEFAULT 0,
    descuento DECIMAL(5,2) DEFAULT 0,
    items_count INT DEFAULT 0,
    notas TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_vendedor (vendedor_id),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Items del Pedido
CREATE TABLE IF NOT EXISTS pedido_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id VARCHAR(50) NOT NULL,
    producto_id VARCHAR(50),
    nombre VARCHAR(255) NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    INDEX idx_pedido (pedido_id),
    INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

4. Haz clic en **Go** ✅

---

## PASO 8️⃣: Migrar Datos (Si tienes datos en JSON)

### En Hostinger, sube un archivo: `/admin/api/migrate.php`

```php
<?php
require_once 'config.php';
require_once 'db_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'migrate') {
    $result = migrateJSONToMySQL();
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Migración JSON a MySQL</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .alert { padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔄 Migración de Datos: JSON → MySQL</h1>
    
    <form method="POST">
        <input type="hidden" name="action" value="migrate">
        <button type="submit">Iniciar Migración</button>
    </form>
    
    <div id="resultado"></div>

    <script>
        document.querySelector('form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const response = await fetch('migrate.php', {
                method: 'POST',
                body: new FormData(e.target)
            });
            const result = await response.json();
            const div = document.getElementById('resultado');
            const clase = result.success ? 'success' : 'error';
            div.innerHTML = `<div class="alert ${clase}">${result.message}</div>`;
        });
    </script>
</body>
</html>
```

5. Accede a: `tu-dominio.com/admin/api/migrate.php`
6. Haz clic en **Iniciar Migración**
7. Espera a que termine ✅

---

## PASO 9️⃣: Prueba la Conexión

### Crea un archivo de test: `/admin/api/test-db.php`

```php
<?php
require_once 'config.php';

try {
    if (DB_TYPE === 'mysql') {
        $pdo = getDBConnection();
        $result = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
        $count = $result->fetch()['total'];
        echo "✅ Conexión exitosa<br>";
        echo "Total de usuarios: " . $count;
    } else {
        echo "⚠️ Aún estás usando JSON";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

8. Accede a: `tu-dominio.com/admin/api/test-db.php` ✅

---

## ✅ ¡LISTO!

Tu sitio ahora está conectado a MySQL con:
- ✅ Base de datos `lacrem_db`
- ✅ Usuario `lacrem_user`
- ✅ Tablas de usuarios, clientes y pedidos
- ✅ Datos migrados desde JSON

### Para acceder al panel:
- 🌐 `tu-dominio.com/admin/login.html`
- 👤 Usuario: **admin**
- 🔐 Contraseña: **admin123**

---

## 💡 NOTAS IMPORTANTES:

1. **Seguridad:**
   - Cambia la contraseña del admin después del primer login
   - Usa una contraseña segura para el usuario MySQL
   - Mantén los datos JSON de respaldo (en local)

2. **Backup:**
   ```sql
   -- En phpMyAdmin, exporta tu base de datos regularmente
   ```

3. **Soporte Hostinger:**
   - Si tienes dudas, contacta a soporte@hostinger.com
   - Proporciona: Nombre de la BD, usuario, error específico

---

**¿Necesitas ayuda con algún paso? Cuéntame dónde estás atascado.**
