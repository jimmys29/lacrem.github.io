<?php
/**
 * Script de Migración: JSON → MySQL
 * Acceso: /admin/api/migrate.php
 */

require_once 'config.php';
require_once 'auth.php';
require_once 'db_functions.php';

header('Content-Type: application/json');

// Validar que estamos en localhost o en el servidor
$allowed_hosts = ['localhost', '127.0.0.1', $_SERVER['HTTP_HOST'] ?? ''];
$is_localhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || 
                in_array($_SERVER['HTTP_HOST'], ['localhost:8000', 'localhost:3000']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'migrate') {
        if (DB_TYPE !== 'mysql') {
            echo json_encode([
                'success' => false,
                'message' => 'MySQL no está habilitado en config.php. Primero actualiza la configuración.'
            ]);
            exit;
        }
        
        try {
            $result = migrateJSONToMySQL();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}

// Vista HTML
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración JSON → MySQL - La Crem</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #F195B2 0%, #62C3E7 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header i {
            font-size: 3em;
            color: #F195B2;
            margin-bottom: 10px;
        }

        .header h1 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 0.9em;
        }

        .info-box {
            background: #e7f3ff;
            border: 2px solid #b3d9ff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-box h3 {
            color: #F195B2;
            margin-bottom: 10px;
            font-size: 1em;
        }

        .info-box p {
            color: #555;
            margin: 8px 0;
            font-size: 0.9em;
            display: flex;
            align-items: center;
        }

        .info-box p i {
            margin-right: 8px;
            color: #62C3E7;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-migrate {
            background: linear-gradient(135deg, #F195B2 0%, #62C3E7 100%);
            color: white;
        }

        .btn-migrate:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(241, 149, 178, 0.4);
        }

        .btn-migrate:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-back {
            background: #e9ecef;
            color: #333;
        }

        .btn-back:hover {
            background: #dee2e6;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            border-left: 4px solid;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
            display: block;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
            display: block;
        }

        .alert.info {
            background: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
            display: block;
        }

        .progress {
            width: 100%;
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 15px;
            display: none;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #F195B2, #62C3E7);
            width: 0%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.8em;
            transition: width 0.3s;
        }

        .spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner i {
            font-size: 2em;
            color: #F195B2;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }

        .stat {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        .stat-number {
            font-size: 1.5em;
            font-weight: 700;
            color: #F195B2;
        }

        .stat-label {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-database"></i>
            <h1>Migración de Datos</h1>
            <p>JSON → MySQL</p>
        </div>

        <div id="message" class="alert"></div>

        <div class="info-box">
            <h3><i class="fas fa-info-circle"></i> ¿Qué hará esta herramienta?</h3>
            <p><i class="fas fa-check"></i> Leerá los datos de los archivos JSON</p>
            <p><i class="fas fa-check"></i> Los insertará en la base de datos MySQL</p>
            <p><i class="fas fa-check"></i> Mantendra los archivos JSON como respaldo</p>
        </div>

        <div class="spinner" id="spinner">
            <i class="fas fa-spinner"></i>
            <p style="margin-top: 10px; color: #666;">Migrando datos...</p>
        </div>

        <div class="progress" id="progress">
            <div class="progress-bar" id="progressBar">0%</div>
        </div>

        <form id="migrateForm">
            <div class="button-group">
                <button type="submit" class="btn-migrate" id="btnMigrate">
                    <i class="fas fa-exchange-alt"></i> Iniciar Migración
                </button>
                <button type="button" class="btn-back" onclick="window.location.href='../index.html'">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
            </div>
        </form>

        <div id="stats" style="display: none;">
            <div class="stats">
                <div class="stat">
                    <div class="stat-number" id="clientesCount">0</div>
                    <div class="stat-label">Clientes migrados</div>
                </div>
                <div class="stat">
                    <div class="stat-number" id="pedidosCount">0</div>
                    <div class="stat-label">Pedidos migrados</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('migrateForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = document.getElementById('btnMigrate');
            const spinner = document.getElementById('spinner');
            const progress = document.getElementById('progress');
            const progressBar = document.getElementById('progressBar');
            const messageDiv = document.getElementById('message');
            const statsDiv = document.getElementById('stats');

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            spinner.style.display = 'block';
            progress.style.display = 'block';
            messageDiv.style.display = 'none';

            // Simular progreso
            let progressValue = 0;
            const progressInterval = setInterval(() => {
                if (progressValue < 90) {
                    progressValue += Math.random() * 30;
                    if (progressValue > 90) progressValue = 90;
                    progressBar.style.width = progressValue + '%';
                    progressBar.textContent = Math.floor(progressValue) + '%';
                }
            }, 300);

            try {
                const response = await fetch('migrate.php', {
                    method: 'POST',
                    body: new FormData(e.target)
                });

                const result = await response.json();

                clearInterval(progressInterval);
                progressValue = 100;
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';

                spinner.style.display = 'none';

                setTimeout(() => {
                    if (result.success) {
                        messageDiv.className = 'alert success';
                        messageDiv.innerHTML = `
                            <i class="fas fa-check-circle"></i> ✅ ${result.message}
                        `;
                        
                        if (result.clientes_migrados !== undefined) {
                            document.getElementById('clientesCount').textContent = result.clientes_migrados;
                            document.getElementById('pedidosCount').textContent = result.pedidos_migrados;
                            statsDiv.style.display = 'block';
                        }

                        setTimeout(() => {
                            btn.innerHTML = '<i class="fas fa-check"></i> ✅ Migración Completada';
                            btn.style.background = '#28a745';
                        }, 500);
                    } else {
                        messageDiv.className = 'alert error';
                        messageDiv.innerHTML = `
                            <i class="fas fa-exclamation-circle"></i> ❌ ${result.message}
                        `;
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-exchange-alt"></i> Reintentar';
                        progress.style.display = 'none';
                    }

                    messageDiv.style.display = 'block';
                }, 300);

            } catch (error) {
                clearInterval(progressInterval);
                console.error('Error:', error);
                messageDiv.className = 'alert error';
                messageDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error de conexión: ${error.message}`;
                messageDiv.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-exchange-alt"></i> Reintentar';
                spinner.style.display = 'none';
                progress.style.display = 'none';
            }
        });
    </script>
</body>
</html>
