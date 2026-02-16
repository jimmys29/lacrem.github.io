<?php
// Retrieve JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo "Invalid Data";
    exit();
}

$customer = $data['customer'];
$items = $data['items'];
$totals = $data['totals'];
$discountCode = $data['discountCode'];

// Configuration
$sales_email = "ventas@lacrem"; // Update this to the actual domain if needed, e.g., ventas@lacrem.com
$from_email = "no-reply@lacrem.com";

// 1. Send Email to Customer (HTML Invoice)
$to = $customer['email'];
$subject = "Confirmación de Pedido - La Crem";

$message = "
<html>
<head>
<title>Tu Pedido en La Crem</title>
<style>
    body { font-family: Arial, sans-serif; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    .total { font-weight: bold; font-size: 1.2em; }
</style>
</head>
<body>
    <h2>Gracias por tu compra, " . htmlspecialchars($customer['name']) . "!</h2>
    <p>Hemos recibido tu pedido. A continuación los detalles:</p>
    
    <h3>Detalles del Cliente</h3>
    <p><strong>Teléfono:</strong> " . htmlspecialchars($customer['phone']) . "</p>
    <p><strong>Dirección:</strong> " . htmlspecialchars($customer['address']) . "</p>
    <p><strong>Método de Pago:</strong> " . htmlspecialchars($customer['payment_method']) . "</p>

    <h3>Resumen del Pedido</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>";

foreach ($items as $item) {
    $itemTotal = $item['price'] * $item['quantity'];
    $message .= "
            <tr>
                <td>" . htmlspecialchars($item['name']) . "</td>
                <td>$" . number_format($item['price'], 2) . "</td>
                <td>" . $item['quantity'] . "</td>
                <td>$" . number_format($itemTotal, 2) . "</td>
            </tr>";
}

$message .= "
        </tbody>
    </table>
    
    <p class='total'>Subtotal: $" . number_format($totals['subtotal'], 2) . "</p>";

if ($totals['discountAmount'] > 0) {
    $message .= "<p>Descuento (" . htmlspecialchars($discountCode) . "): -$" . number_format($totals['discountAmount'], 2) . "</p>";
}

$message .= "
    <p class='total'>TOTAL: $" . number_format($totals['total'], 2) . "</p>
    
    <p>Si elegiste pago por transferencia, por favor realiza el pago a la cuenta XXX-XXXX-XXX y envía el comprobante a este correo.</p>
    <p>Gracias por preferir La Crem!</p>
</body>
</html>
";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: $from_email" . "\r\n";

mail($to, $subject, $message, $headers);

// 2. Send Alert to Sales Team (Plain Text or HTML)
$subject_sales = "Nuevo Pedido: " . $customer['name'];
$message_sales = "Nuevo pedido recibido.\n\n";
$message_sales .= "Cliente: " . $customer['name'] . "\n";
$message_sales .= "Email: " . $customer['email'] . "\n";
$message_sales .= "Teléfono: " . $customer['phone'] . "\n";
$message_sales .= "Dirección: " . $customer['address'] . "\n";
$message_sales .= "Pago: " . $customer['payment_method'] . "\n\n";
$message_sales .= "Total: $" . number_format($totals['total'], 2) . "\n";
$message_sales .= "\nRevisar panel administrativo o correo del cliente para más detalles.";

$headers_sales = "From: $from_email";

mail($sales_email, $subject_sales, $message_sales, $headers_sales);

// Response to JS
echo json_encode(["status" => "success", "message" => "Order processed"]);
?>
