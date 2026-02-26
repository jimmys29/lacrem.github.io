<?php
header('Content-Type: application/json');
require_once 'db_functions.php';
require_once 'auth.php';

$action = $_GET['action'] ?? '';

// Acciones públicas (sin autenticación)
$public_actions = ['login'];

// Verificar autenticación para acciones protegidas
if (!in_array($action, $public_actions)) {
    if (!is_authenticated()) {
        echo json_encode(['success' => false, 'error' => 'No autenticado', 'redirect' => 'login.html']);
        exit();
    }
}

switch ($action) {
    // ===== AUTENTICACIÓN =====
    case 'login':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(validar_login($data['usuario'], $data['password']));
        break;
    
    case 'logout':
        echo json_encode(logout());
        break;
    
    case 'get_current_user':
        echo json_encode(get_current_user());
        break;
    
    case 'check_auth':
        echo json_encode(['authenticated' => is_authenticated(), 'user' => get_current_user()]);
        break;
    
    // ===== USUARIOS / VENDEDORES =====
    case 'crear_usuario':
        if (!has_role('admin')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para crear usuarios']);
            break;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(crear_usuario($data));
        break;
    
    case 'obtener_usuarios':
        echo json_encode(get_usuarios());
        break;
    
    case 'actualizar_usuario':
        if (!has_role('admin')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
            break;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $_POST['id'] ?? $data['id'] ?? '';
        echo json_encode(actualizar_usuario($id, $data));
        break;
    
    case 'eliminar_usuario':
        if (!has_role('admin')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
            break;
        }
        $id = $_POST['id'] ?? '';
        echo json_encode(eliminar_usuario($id));
        break;
    
    // ===== CLIENTES =====
    case 'guardar_cliente':
        $cliente = json_decode(file_get_contents('php://input'), true);
        echo json_encode(save_cliente($cliente));
        break;
    
    case 'obtener_clientes':
        echo json_encode(get_clientes());
        break;
    
    case 'eliminar_cliente':
        $cliente_id = $_POST['cliente_id'] ?? '';
        echo json_encode(delete_cliente($cliente_id));
        break;
    
    // ===== PEDIDOS =====
    case 'guardar_pedido':
        $pedido = json_decode(file_get_contents('php://input'), true);
        // Agregar vendedor si no está presente
        if (!isset($pedido['vendedor_id'])) {
            $user = get_current_user();
            $pedido['vendedor_id'] = $user['id'];
            $pedido['vendedor_nombre'] = $user['nombre'];
        }
        echo json_encode(save_pedido($pedido));
        break;
    
    case 'obtener_pedidos':
        $vendedor_id = $_GET['vendedor_id'] ?? null;
        echo json_encode(get_pedidos($vendedor_id));
        break;
    
    case 'eliminar_pedido':
        $pedido_id = $_POST['pedido_id'] ?? '';
        echo json_encode(delete_pedido($pedido_id));
        break;
    
    // ===== REPORTES =====
    case 'obtener_resumen':
        $vendedor_id = $_GET['vendedor_id'] ?? null;
        echo json_encode(get_resumen_ventas($vendedor_id));
        break;
    
    case 'obtener_reportes_avanzados':
        $filtros = json_decode(file_get_contents('php://input'), true);
        echo json_encode(get_reportes_avanzados($filtros));
        break;
    
    case 'exportar_reporte':
        $formato = $_GET['formato'] ?? 'csv';
        $filtros = json_decode(file_get_contents('php://input'), true);
        exportar_reporte($formato, $filtros);
        break;
    
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
