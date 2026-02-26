<?php
// Configuración de archivos de datos
define('CLIENTES_FILE', __DIR__ . '/../data/clientes.json');
define('PEDIDOS_FILE', __DIR__ . '/../data/pedidos.json');

// Función para crear archivos si no existen
function init_data_files() {
    if (!file_exists(CLIENTES_FILE)) {
        file_put_contents(CLIENTES_FILE, json_encode([]));
    }
    if (!file_exists(PEDIDOS_FILE)) {
        file_put_contents(PEDIDOS_FILE, json_encode([]));
    }
}

// Función para leer clientes
function get_clientes() {
    init_data_files();
    $data = file_get_contents(CLIENTES_FILE);
    return json_decode($data, true) ?? [];
}

// Función para leer pedidos
function get_pedidos() {
    init_data_files();
    $data = file_get_contents(PEDIDOS_FILE);
    return json_decode($data, true) ?? [];
}

// Función para guardar cliente
function save_cliente($cliente) {
    init_data_files();
    $clientes = get_clientes();
    
    // Generar ID único si no existe
    if (!isset($cliente['id'])) {
        $cliente['id'] = uniqid('cli_');
    }
    
    $cliente['fecha_creacion'] = $cliente['fecha_creacion'] ?? date('Y-m-d H:i:s');
    
    // Verificar si ya existe y actualizar o agregar
    $found = false;
    foreach ($clientes as &$c) {
        if ($c['id'] === $cliente['id']) {
            $c = $cliente;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $clientes[] = $cliente;
    }
    
    if (file_put_contents(CLIENTES_FILE, json_encode($clientes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        return ['success' => true, 'id' => $cliente['id'], 'message' => 'Cliente guardado correctamente'];
    }
    return ['success' => false, 'message' => 'Error al guardar el cliente'];
}

// Función para guardar pedido
function save_pedido($pedido) {
    init_data_files();
    $pedidos = get_pedidos();
    
    // Generar ID único si no existe
    if (!isset($pedido['id'])) {
        $pedido['id'] = uniqid('ped_');
    }
    
    $pedido['fecha'] = $pedido['fecha'] ?? date('Y-m-d H:i:s');
    
    $pedidos[] = $pedido;
    
    if (file_put_contents(PEDIDOS_FILE, json_encode($pedidos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        return ['success' => true, 'id' => $pedido['id'], 'message' => 'Pedido guardado correctamente'];
    }
    return ['success' => false, 'message' => 'Error al guardar el pedido'];
}

// Función para obtener resumen de ventas por cliente
function get_resumen_ventas() {
    $pedidos = get_pedidos();
    $clientes = get_clientes();
    
    $resumen = [];
    
    foreach ($clientes as $cliente) {
        $total_productos = 0;
        $total_ventas = 0;
        $cantidad_pedidos = 0;
        
        foreach ($pedidos as $pedido) {
            if ($pedido['cliente_id'] === $cliente['id']) {
                $cantidad_pedidos++;
                
                // Sumar productos
                if (isset($pedido['items']) && is_array($pedido['items'])) {
                    foreach ($pedido['items'] as $item) {
                        $total_productos += $item['cantidad'] ?? 1;
                        $total_ventas += ($item['cantidad'] ?? 1) * ($item['precio'] ?? 0);
                    }
                }
            }
        }
        
        if ($cantidad_pedidos > 0 || $total_productos > 0) {
            $resumen[] = [
                'cliente_id' => $cliente['id'],
                'cliente_nombre' => $cliente['nombre'] ?? '',
                'cliente_email' => $cliente['email'] ?? '',
                'total_productos' => $total_productos,
                'total_ventas' => $total_ventas,
                'cantidad_pedidos' => $cantidad_pedidos
            ];
        }
    }
    
    // Ordenar por total de ventas descendente
    usort($resumen, function($a, $b) {
        return $b['total_ventas'] <=> $a['total_ventas'];
    });
    
    return $resumen;
}

// Función para eliminar cliente
function delete_cliente($cliente_id) {
    init_data_files();
    $clientes = get_clientes();
    $clientes = array_filter($clientes, function($c) use ($cliente_id) {
        return $c['id'] !== $cliente_id;
    });
    
    if (file_put_contents(CLIENTES_FILE, json_encode(array_values($clientes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        return ['success' => true, 'message' => 'Cliente eliminado correctamente'];
    }
    return ['success' => false, 'message' => 'Error al eliminar el cliente'];
}

// Función para eliminar pedido
function delete_pedido($pedido_id) {
    init_data_files();
    $pedidos = get_pedidos();
    $pedidos = array_filter($pedidos, function($p) use ($pedido_id) {
        return $p['id'] !== $pedido_id;
    });
    
    if (file_put_contents(PEDIDOS_FILE, json_encode(array_values($pedidos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        return ['success' => true, 'message' => 'Pedido eliminado correctamente'];
    }
    return ['success' => false, 'message' => 'Error al eliminar el pedido'];
}
