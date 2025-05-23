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

// Validar y registrar cada matrícula
$cursos_matriculados = [];
$cursos_duplicados = [];

foreach ($cursos_seleccionados as $id_curso) {
    // Verificar si ya está matriculado
    $query_check = "SELECT COUNT(*) as count FROM matriculas 
                   WHERE id_usuario = $id_usuario AND id_curso = $id_curso";
    $result_check = mysqli_query($link, $query_check);
    $check = mysqli_fetch_assoc($result_check);
    
    if ($check['count'] == 0) {
        $query_insert = "INSERT INTO matriculas (id_usuario, id_curso) VALUES ($id_usuario, $id_curso)";
        if (mysqli_query($link, $query_insert)) {
            $cursos_matriculados[] = $id_curso;
        }
    } else {
        $cursos_duplicados[] = $id_curso;
    }
}

// Obtener nombres de los cursos para mostrar
$cursos_info = [];
if (!empty($cursos_matriculados)) {
    $cursos_ids = implode(",", $cursos_matriculados);
    $query_cursos = "SELECT id, nombre FROM cursos WHERE id IN ($cursos_ids)";
    $result_cursos = mysqli_query($link, $query_cursos);
    while ($curso = mysqli_fetch_assoc($result_cursos)) {
        $cursos_info[] = $curso;
    }
}

// Obtener nombres de cursos duplicados
$cursos_duplicados_info = [];
if (!empty($cursos_duplicados)) {
    $duplicados_ids = implode(",", $cursos_duplicados);
    $query_duplicados = "SELECT id, nombre FROM cursos WHERE id IN ($duplicados_ids)";
    $result_duplicados = mysqli_query($link, $query_duplicados);
    while ($curso = mysqli_fetch_assoc($result_duplicados)) {
        $cursos_duplicados_info[] = $curso;
    }
}

// Limpiar la sesión
unset($_SESSION['id_usuario']);
unset($_SESSION['cursos_seleccionados']);
session_destroy();
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
        .success { color: #4CAF50; }
        .warning { color: #ff9800; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Resultado de Matrícula</h2>
        <p>Usuario: <?php echo htmlentities($usuario['nombre']); ?> (Documento: <?php echo htmlentities($usuario['documento']); ?>)</p>
        
        <?php if (!empty($cursos_info)): ?>
        <h3 class="success">Cursos matriculados exitosamente:</h3>
        <table>
            <tr>
                <th>Curso</th>
            </tr>
            <?php foreach ($cursos_info as $curso): ?>
            <tr>
                <td><?php echo htmlentities($curso['nombre']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
        
        <?php if (!empty($cursos_duplicados_info)): ?>
        <h3 class="warning">Cursos no matriculados (ya estaban registrados):</h3>
        <table>
            <tr>
                <th>Curso</th>
            </tr>
            <?php foreach ($cursos_duplicados_info as $curso): ?>
            <tr>
                <td><?php echo htmlentities($curso['nombre']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
        
        <a href="index.php" class="home-btn">Volver al Inicio</a>
    </div>
</body>
</html>
<?php
mysqli_close($link);
?>