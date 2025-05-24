<?php
require("definiciones.inc");

session_start();

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['cursos_seleccionados'])) {
    die("Sesión inválida. <a href='index.php'>Volver al inicio</a>");
}

$id_usuario = $_SESSION['id_usuario'];
$cursos_seleccionados = $_SESSION['cursos_seleccionados'];

$link = mysqli_connect(MAQUINA, USUARIO, CLAVE); 
mysqli_select_db($link, DB);

// Obtener información del usuario
$query_usuario = "SELECT documento, nombre FROM usuarios WHERE id = $id_usuario";
$result_usuario = mysqli_query($link, $query_usuario);
$usuario = mysqli_fetch_assoc($result_usuario);

// Registrar cada matrícula
foreach ($cursos_seleccionados as $id_curso) {
    $query_insert = "INSERT INTO matriculas (id_usuario, id_curso) VALUES ($id_usuario, $id_curso)";
    mysqli_query($link, $query_insert);
}

// Limpiar la sesión
unset($_SESSION['id_usuario']);
unset($_SESSION['cursos_seleccionados']);
session_destroy();

// Obtener nombres de los cursos matriculados para mostrar
$cursos_ids = implode(",", $cursos_seleccionados);
$query_cursos = "SELECT nombre FROM cursos WHERE id IN ($cursos_ids)";
$result_cursos = mysqli_query($link, $query_cursos);
?>
<html>
<head>
    <title>Matrícula Completada</title>
    <style>
        body { background-color: #769AB4; font-family: Arial, sans-serif; }
        .container { width: 80%; margin: 40px auto; background-color: #FFFFCC; padding: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .home-btn { padding: 8px 15px; background-color: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .home-btn:hover { background-color: #0b7dda; }
    </style>
</head>
<body>
    <div class="container">
        <h2>¡Matrícula Completada con Éxito!</h2>
        <p>Usuario: <?php echo htmlentities($usuario['nombre']); ?> (Documento: <?php echo htmlentities($usuario['documento']); ?>)</p>
        
        <h3>Cursos matriculados:</h3>
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
        
        <a href="index.php" class="home-btn">Volver al Inicio</a>
    </div>
</body>
</html>
<?php
mysqli_close($link);
?>