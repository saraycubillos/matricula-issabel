<?php
session_start();
require("definiciones.inc");

$link = mysqli_connect(MAQUINA, USUARIO, CLAVE, DB);
if (!$link) {
    die("Error de conexión: " . mysqli_connect_error());
}

$documento = $_POST["documento"];
$nombre = $_POST["nombre"];
$cursos = $_POST["cursos"];

$result = mysqli_query($link, "SELECT id FROM usuarios WHERE documento = '$documento'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($link, "INSERT INTO usuarios (nombre, documento) VALUES ('$nombre', '$documento')");
    $id_usuario = mysqli_insert_id($link);
} else {
    $row = mysqli_fetch_assoc($result);
    $id_usuario = $row["id"];
}

foreach ($cursos as $id_curso) {
    mysqli_query($link, "INSERT INTO matriculas (id_usuario, id_curso) VALUES ($id_usuario, $id_curso)");
}

echo "<h2>Matrícula registrada exitosamente para " . htmlentities($nombre) . "</h2>";
echo "<a href='index.php'>Volver</a>";
?>
