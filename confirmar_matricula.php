<?php
require("definiciones.inc");

if (!isset($_POST['cursos']) || empty($_POST['cursos'])) {
    die("No ha seleccionado ningún curso. <a href='verificar_usuario.php?documento=".$_POST['documento']."'>Volver</a>");
}

$id_usuario = $_POST['id_usuario'];
$documento = $_POST['documento'];
$cursos_seleccionados = $_POST['cursos'];

$link = mysqli_connect(MAQUINA, USUARIO, CLAVE); 
mysqli_select_db($link, DB);

// Obtener información del usuario
$query_usuario = "SELECT nombre FROM usuarios WHERE id = $id_usuario";
$result_usuario = mysqli_query($link, $query_usuario);
$usuario = mysqli_fetch_assoc($result_usuario);
$nombre_usuario = $usuario['nombre'];

// Obtener nombres de los cursos seleccionados
$cursos_ids = implode(",", $cursos_seleccionados);
$query_cursos = "SELECT id, nombre FROM cursos WHERE id IN ($cursos_ids)";
$result_cursos = mysqli_query($link, $query_cursos);

// Guardar los cursos en sesión para el paso de confirmación
session_start();
$_SESSION['id_usuario'] = $id_usuario;
$_SESSION['cursos_seleccionados'] = $cursos_seleccionados;
?>
<html>
<head>
    <title>Confirmar Matrícula</title>
    <style>
        body { background-color: #769AB4; font-family: Arial, sans-serif; }
        .container { width: 80%; margin: 40px auto; background-color: #FFFFCC; padding: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .button-group { margin-top: 20px; }
        .confirm-btn { padding: 8px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .confirm-btn:hover { background-color: #45a049; }
        .correct-btn { padding: 8px 15px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .correct-btn:hover { background-color: #d32f2f; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Confirmar Matrícula</h2>
        <p>Usuario: <?php echo htmlentities($nombre_usuario); ?> (Documento: <?php echo htmlentities($documento); ?>)</p>
        
        <h3>Cursos seleccionados:</h3>
        <table>
            <tr>
                <th>Curso</th>
            </tr>
            <?php while ($curso = mysqli_fetch_assoc($result_cursos)): ?>
            <tr>
                <td><?php echo htmlentities($curso['nombre']); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        
        <div class="button-group">
            <form action="procesar_matricula.php" method="post" style="display: inline;">
                <input type="submit" class="confirm-btn" value="Confirmar Matrícula">
            </form>
            <form action="verificar_usuario.php" method="post" style="display: inline; margin-left: 10px;">
                <input type="hidden" name="documento" value="<?php echo htmlentities($documento); ?>">
                <input type="submit" class="correct-btn" value="Corregir Selección">
            </form>
        </div>
    </div>
</body>
</html>
<?php
mysqli_close($link);
?>