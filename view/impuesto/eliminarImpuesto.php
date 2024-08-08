<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Eliminar Impuesto | SistemaPOS</title>
        <style>
            form {
                max-width: 500px;
                margin: auto;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 10px;
            }
            label, input, textarea {
                display: block;
                width: 100%;
                margin-bottom: 10px;
            }
            h2 {
                margin: auto;
                padding: 10px;
                text-align: center;
                margin-bottom: 10px;
            }
            .message {
                max-width: 500px;
                margin: auto;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                text-align: center;
                margin-bottom: 20px;
            }
            .error {
                background-color: #f8d7da;
                color: #721c24;
            }
            .success {
                background-color: #d4edda;
                color: #155724;
            }
            .menu-button {
                display: block; 
                width: max-content; 
                margin: 20px auto; 
                text-align: center; 
                padding: 10px; 
                border: 1px solid #ccc; 
                border-radius: 5px; 
                text-decoration: none; 
                color: black;
            }
        </style>
    </head>
    <body>
        <h2>Eliminar Impuesto</h2>
        <?php
            session_start();
            if (isset($_SESSION['error'])) {
                echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            } elseif (isset($_SESSION['success'])) {
                echo '<div class="message success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
        ?>
        <form action="../../controller/impuestoAction.php" method="POST">
            <input type="hidden" name="accion" value="eliminar">
            <label for="id">ID del Impuesto:</label>
            <input type="number" id="id" name="id" required>

            <button type="submit">Eliminar Impuesto</button>
        </form>
        <a href="../index.php" class="menu-button">Regresar al Menú</a>
    </body>
</html>