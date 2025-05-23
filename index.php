<?php
require("definiciones.inc");
?>
<html>
<head>
    <title>Sistema de Matrículas</title>
    <style>
        body { background-color: #769AB4; font-family: Arial, sans-serif; }
        .container { width: 80%; margin: 40px auto; background-color: #FFFFCC; padding: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        input[type="submit"] { padding: 8px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        input[type="submit"]:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sistema de Matrículas</h2>
        <form action="verificar_usuario.php" method="post">
            <p>Ingrese su número de documento:</p>
            <input type="text" name="documento" required>
            <input type="submit" value="Continuar">
        </form>
    </div>
</body>
</html>