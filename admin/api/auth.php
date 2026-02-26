<?php
session_start();

// Configuración
define('USUARIOS_FILE', __DIR__ . '/../data/usuarios.json');
define('SESSION_TIMEOUT', 3600); // 1 hora

/**
 * Inicializar archivo de usuarios si no existe
 */
function init_usuarios() {
    if (!file_exists(USUARIOS_FILE)) {
        // Crear usuario administrador por defecto
        $usuarios = [
            [
                'id' => 'admin_001',
                'usuario' => 'admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'nombre' => 'Administrador',
                'email' => 'admin@lacrem.com',
                'rol' => 'admin',
                'activo' => true,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]
        ];
        
        file_put_contents(USUARIOS_FILE, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

/**
 * Obtener todos los usuarios
 */
function get_usuarios() {
    init_usuarios();
    $data = file_get_contents(USUARIOS_FILE);
    return json_decode($data, true) ?? [];
}

/**
 * Validar login
 */
function validar_login($usuario, $password) {
    $usuarios = get_usuarios();
    
    foreach ($usuarios as $u) {
        if ($u['usuario'] === $usuario && $u['activo']) {
            if (password_verify($password, $u['password'])) {
                // Login exitoso
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['usuario'] = $u['usuario'];
                $_SESSION['nombre'] = $u['nombre'];
                $_SESSION['rol'] = $u['rol'];
                $_SESSION['last_activity'] = time();
                
                return ['success' => true, 'message' => 'Login exitoso', 'user' => $u];
            }
        }
    }
    
    return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
}

/**
 * Verificar si el usuario está autenticado
 */
function is_authenticated() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Verificar timeout de sesión
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Requiere autenticación (usar al inicio de páginas protegidas)
 */
function require_auth() {
    if (!is_authenticated()) {
        header('Location: login.html');
        exit();
    }
}

/**
 * Cerrar sesión
 */
function logout() {
    session_unset();
    session_destroy();
    return ['success' => true, 'message' => 'Sesión cerrada correctamente'];
}

/**
 * Obtener usuario actual
 */
function get_current_user() {
    if (is_authenticated()) {
        return [
            'id' => $_SESSION['user_id'],
            'usuario' => $_SESSION['usuario'],
            'nombre' => $_SESSION['nombre'],
            'rol' => $_SESSION['rol']
        ];
    }
    return null;
}

/**
 * Verificar si el usuario tiene un rol específico
 */
function has_role($rol) {
    if (!is_authenticated()) {
        return false;
    }
    return $_SESSION['rol'] === $rol || $_SESSION['rol'] === 'admin';
}

/**
 * Crear nuevo usuario
 */
function crear_usuario($datos) {
    init_usuarios();
    $usuarios = get_usuarios();
    
    // Verificar si el usuario ya existe
    foreach ($usuarios as $u) {
        if ($u['usuario'] === $datos['usuario']) {
            return ['success' => false, 'message' => 'El usuario ya existe'];
        }
    }
    
    // Crear nuevo usuario
    $nuevo_usuario = [
        'id' => uniqid('usr_'),
        'usuario' => $datos['usuario'],
        'password' => password_hash($datos['password'], PASSWORD_DEFAULT),
        'nombre' => $datos['nombre'],
        'email' => $datos['email'],
        'rol' => $datos['rol'] ?? 'vendedor',
        'activo' => true,
        'fecha_creacion' => date('Y-m-d H:i:s')
    ];
    
    $usuarios[] = $nuevo_usuario;
    
    if (file_put_contents(USUARIOS_FILE, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        return ['success' => true, 'message' => 'Usuario creado correctamente', 'id' => $nuevo_usuario['id']];
    }
    
    return ['success' => false, 'message' => 'Error al crear el usuario'];
}

/**
 * Actualizar usuario
 */
function actualizar_usuario($id, $datos) {
    init_usuarios();
    $usuarios = get_usuarios();
    $actualizado = false;
    
    foreach ($usuarios as &$u) {
        if ($u['id'] === $id) {
            // Actualizar solo campos permitidos
            if (isset($datos['nombre'])) $u['nombre'] = $datos['nombre'];
            if (isset($datos['email'])) $u['email'] = $datos['email'];
            if (isset($datos['activo'])) $u['activo'] = $datos['activo'];
            if (isset($datos['rol'])) $u['rol'] = $datos['rol'];
            
            // Solo actualizar password si se proporciona uno nuevo
            if (isset($datos['password']) && !empty($datos['password'])) {
                $u['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
            }
            
            $actualizado = true;
            break;
        }
    }
    
    if ($actualizado) {
        if (file_put_contents(USUARIOS_FILE, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            return ['success' => true, 'message' => 'Usuario actualizado correctamente'];
        }
    }
    
    return ['success' => false, 'message' => 'Usuario no encontrado o error al actualizar'];
}

/**
 * Eliminar usuario
 */
function eliminar_usuario($id) {
    if ($id === 'admin_001') {
        return ['success' => false, 'message' => 'No se puede eliminar el usuario administrador principal'];
    }
    
    init_usuarios();
    $usuarios = get_usuarios();
    $usuarios = array_filter($usuarios, function($u) use ($id) {
        return $u['id'] !== $id;
    });
    
    if (file_put_contents(USUARIOS_FILE, json_encode(array_values($usuarios), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        return ['success' => true, 'message' => 'Usuario eliminado correctamente'];
    }
    
    return ['success' => false, 'message' => 'Error al eliminar el usuario'];
}
