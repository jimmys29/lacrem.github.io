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
function get_pedidos($vendedor_id = null) {
    init_data_files();
    $data = file_get_contents(PEDIDOS_FILE);
    $pedidos = json_decode($data, true) ?? [];
    
    // Filtrar por vendedor si se especifica
    if ($vendedor_id) {
        $pedidos = array_filter($pedidos, function($p) use ($vendedor_id) {
            return isset($p['vendedor_id']) && $p['vendedor_id'] === $vendedor_id;
        });
    }
    
    return array_values($pedidos);
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
function get_resumen_ventas($vendedor_id = null) {
    $pedidos = get_pedidos($vendedor_id);
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

// Función para obtener reportes avanzados con filtros
function get_reportes_avanzados($filtros = []) {
    $pedidos = get_pedidos();
    $clientes = get_clientes();
    $usuarios = get_usuarios();
    
    // Aplicar filtros de fecha
    $fecha_inicio = $filtros['fecha_inicio'] ?? null;
    $fecha_fin = $filtros['fecha_fin'] ?? null;
    $vendedor_id = $filtros['vendedor_id'] ?? null;
    $periodo = $filtros['periodo'] ?? null; // 'diario', 'semanal', 'mensual'
    
    // Calcular fechas según periodo
    if ($periodo) {
        $ahora = new DateTime();
        switch ($periodo) {
            case 'diario':
                $fecha_inicio = $ahora->format('Y-m-d') . ' 00:00:00';
                $fecha_fin = $ahora->format('Y-m-d') . ' 23:59:59';
                break;
            case 'semanal':
                $inicio_semana = clone $ahora;
                $inicio_semana->modify('monday this week');
                $fecha_inicio = $inicio_semana->format('Y-m-d') . ' 00:00:00';
                $fecha_fin = $ahora->format('Y-m-d') . ' 23:59:59';
                break;
            case 'mensual':
                $fecha_inicio = $ahora->format('Y-m-01') . ' 00:00:00';
                $fecha_fin = $ahora->format('Y-m-d') . ' 23:59:59';
                break;
        }
    }
    
    // Filtrar pedidos
    $pedidos_filtrados = array_filter($pedidos, function($p) use ($fecha_inicio, $fecha_fin, $vendedor_id) {
        $cumple_filtros = true;
        
        if ($fecha_inicio && isset($p['fecha'])) {
            $cumple_filtros = $cumple_filtros && ($p['fecha'] >= $fecha_inicio);
        }
        
        if ($fecha_fin && isset($p['fecha'])) {
            $cumple_filtros = $cumple_filtros && ($p['fecha'] <= $fecha_fin);
        }
        
        if ($vendedor_id && isset($p['vendedor_id'])) {
            $cumple_filtros = $cumple_filtros && ($p['vendedor_id'] === $vendedor_id);
        }
        
        return $cumple_filtros;
    });
    
    // Calcular métricas
    $total_ventas = 0;
    $total_productos = 0;
    $ventas_por_vendedor = [];
    $ventas_por_dia = [];
    $productos_mas_vendidos = [];
    
    foreach ($pedidos_filtrados as $pedido) {
        // Total de ventas y productos
        if (isset($pedido['items']) && is_array($pedido['items'])) {
            $subtotal_pedido = 0;
            foreach ($pedido['items'] as $item) {
                $cantidad = $item['cantidad'] ?? 1;
                $precio = $item['precio'] ?? 0;
                $subtotal = $cantidad * $precio;
                
                $total_productos += $cantidad;
                $subtotal_pedido += $subtotal;
                
                // Productos más vendidos
                $producto_id = $item['producto_id'] ?? $item['nombre'];
                if (!isset($productos_mas_vendidos[$producto_id])) {
                    $productos_mas_vendidos[$producto_id] = [
                        'nombre' => $item['nombre'],
                        'cantidad' => 0,
                        'ventas' => 0
                    ];
                }
                $productos_mas_vendidos[$producto_id]['cantidad'] += $cantidad;
                $productos_mas_vendidos[$producto_id]['ventas'] += $subtotal;
            }
            
            // Aplicar descuento
            $descuento = ($pedido['descuento'] ?? 0) / 100;
            $total_pedido = $subtotal_pedido * (1 - $descuento);
            $total_ventas += $total_pedido;
        }
        
        // Ventas por vendedor
        $vendedor_id_pedido = $pedido['vendedor_id'] ?? 'sin_asignar';
        $vendedor_nombre = $pedido['vendedor_nombre'] ?? 'Sin asignar';
        
        if (!isset($ventas_por_vendedor[$vendedor_id_pedido])) {
            $ventas_por_vendedor[$vendedor_id_pedido] = [
                'vendedor_id' => $vendedor_id_pedido,
                'vendedor_nombre' => $vendedor_nombre,
                'cantidad_pedidos' => 0,
                'total_ventas' => 0,
                'total_productos' => 0
            ];
        }
        
        $ventas_por_vendedor[$vendedor_id_pedido]['cantidad_pedidos']++;
        $ventas_por_vendedor[$vendedor_id_pedido]['total_ventas'] += $total_pedido ?? 0;
        
        // Ventas por día
        $fecha = isset($pedido['fecha']) ? date('Y-m-d', strtotime($pedido['fecha'])) : date('Y-m-d');
        if (!isset($ventas_por_dia[$fecha])) {
            $ventas_por_dia[$fecha] = [
                'fecha' => $fecha,
                'cantidad_pedidos' => 0,
                'total_ventas' => 0
            ];
        }
        $ventas_por_dia[$fecha]['cantidad_pedidos']++;
        $ventas_por_dia[$fecha]['total_ventas'] += $total_pedido ?? 0;
    }
    
    // Ordenar productos más vendidos
    uasort($productos_mas_vendidos, function($a, $b) {
        return $b['cantidad'] <=> $a['cantidad'];
    });
    
    // Ordenar vendedores por ventas
    uasort($ventas_por_vendedor, function($a, $b) {
        return $b['total_ventas'] <=> $a['total_ventas'];
    });
    
    return [
        'resumen' => [
            'total_pedidos' => count($pedidos_filtrados),
            'total_ventas' => round($total_ventas, 2),
            'total_productos' => $total_productos,
            'promedio_por_pedido' => count($pedidos_filtrados) > 0 ? round($total_ventas / count($pedidos_filtrados), 2) : 0
        ],
        'ventas_por_vendedor' => array_values($ventas_por_vendedor),
        'ventas_por_dia' => array_values($ventas_por_dia),
        'productos_mas_vendidos' => array_slice(array_values($productos_mas_vendidos), 0, 10),
        'filtros_aplicados' => $filtros
    ];
}

// Función para exportar reportes
function exportar_reporte($formato, $filtros) {
    $datos = get_reportes_avanzados($filtros);
    
    if ($formato === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_ventas_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Resumen general
        fputcsv($output, ['RESUMEN GENERAL']);
        fputcsv($output, ['Total Pedidos', $datos['resumen']['total_pedidos']]);
        fputcsv($output, ['Total Ventas', '$' . $datos['resumen']['total_ventas']]);
        fputcsv($output, ['Total Productos', $datos['resumen']['total_productos']]);
        fputcsv($output, ['Promedio por Pedido', '$' . $datos['resumen']['promedio_por_pedido']]);
        fputcsv($output, []);
        
        // Ventas por vendedor
        fputcsv($output, ['VENTAS POR VENDEDOR']);
        fputcsv($output, ['Vendedor', 'Pedidos', 'Total Ventas', 'Productos']);
        foreach ($datos['ventas_por_vendedor'] as $v) {
            fputcsv($output, [
                $v['vendedor_nombre'],
                $v['cantidad_pedidos'],
                '$' . round($v['total_ventas'], 2),
                $v['total_productos'] ?? 0
            ]);
        }
        fputcsv($output, []);
        
        // Productos más vendidos
        fputcsv($output, ['PRODUCTOS MÁS VENDIDOS']);
        fputcsv($output, ['Producto', 'Cantidad', 'Ventas']);
        foreach ($datos['productos_mas_vendidos'] as $p) {
            fputcsv($output, [
                $p['nombre'],
                $p['cantidad'],
                '$' . round($p['ventas'], 2)
            ]);
        }
        
        fclose($output);
        exit();
    } elseif ($formato === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_ventas_' . date('Y-m-d') . '.json"');
        echo json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
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
