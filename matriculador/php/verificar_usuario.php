<?php
require("definiciones.inc");

$documento = $_POST['documento'];
$link = mysqli_connect(MAQUINA, USUARIO, CLAVE); 
mysqli_select_db($link, DB);

// Verificar si el usuario existe
$query = "SELECT id, nombre FROM usuarios WHERE documento = '$documento'";
$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) == 0) {
    die("Usuario no encontrado. <a href='index.php'>Volver</a>");
}

$usuario = mysqli_fetch_assoc($result);
$id_usuario = $usuario['id'];
$nombre_usuario = $usuario['nombre'];

// Obtener cursos disponibles
$query_cursos = "SELECT c.id, c.nombre, 
                (SELECT COUNT(*) FROM matriculas m WHERE m.id_curso = c.id AND m.id_usuario = $id_usuario) as matriculado
                FROM cursos c";
$result_cursos = mysqli_query($link, $query_cursos);

if (mysqli_num_rows($result_cursos) == 0) {
    die("No hay cursos disponibles en este momento.");
}
?>
<html>
<head>
    <title>Oferta de Cursos</title>
    <style>
        body { background-color: #769AB4; font-family: Arial, sans-serif; }
        .container { width: 80%; margin: 40px auto; background-color: #FFFFCC; padding: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        input[type="submit"] { padding: 8px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        input[type="submit"]:hover { background-color: #45a049; }
        .matriculado { color: #888; font-style: italic; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Oferta de Cursos para <?php echo htmlentities($nombre_usuario); ?></h2>
        <form action="confirmar_matricula.php" method="post">
            <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
            <input type="hidden" name="documento" value="<?php echo htmlentities($documento); ?>">
            
            <table>
                <tr>
                    <th>Seleccionar</th>
                    <th>Curso</th>
                    <th>Estado</th>
                </tr>
                <?php while ($curso = mysqli_fetch_assoc($result_cursos)): ?>
                <tr>
                    <td>
                        <?php if ($curso['matriculado'] == 0): ?>
                            <input type="checkbox" name="cursos[]" value="<?php echo $curso['id']; ?>">
                        <?php else: ?>
                            <input type="checkbox" disabled>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlentities($curso['nombre']); ?></td>
                    <td class="<?php echo $curso['matriculado'] ? 'matriculado' : ''; ?>">
                        <?php echo $curso['matriculado'] ? 'Ya matriculado' : 'Disponible'; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            
            <input type="submit" value="Continuar">
        </form>
    </div>
</body>
</html>
<?php
mysqli_close($link);
?>