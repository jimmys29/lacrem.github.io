<?php
header('Content-Type: application/json');
require_once 'db_functions.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'guardar_cliente':
        $cliente = json_decode(file_get_contents('php://input'), true);
        echo json_encode(save_cliente($cliente));
        break;
    
    case 'obtener_clientes':
        echo json_encode(get_clientes());
        break;
    
    case 'guardar_pedido':
        $pedido = json_decode(file_get_contents('php://input'), true);
        echo json_encode(save_pedido($pedido));
        break;
    
    case 'obtener_pedidos':
        echo json_encode(get_pedidos());
        break;
    
    case 'obtener_resumen':
        echo json_encode(get_resumen_ventas());
        break;
    
    case 'eliminar_cliente':
        $cliente_id = $_POST['cliente_id'] ?? '';
        echo json_encode(delete_cliente($cliente_id));
        break;
    
    case 'eliminar_pedido':
        $pedido_id = $_POST['pedido_id'] ?? '';
        echo json_encode(delete_pedido($pedido_id));
        break;
    
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
