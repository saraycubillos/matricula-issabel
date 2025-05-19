<?php
$documento = $_POST['documento'];
$cursos = $_POST['cursos'];

$conn = new mysqli("localhost", "root", "", "matricula");
$curso_nombres = [];

foreach ($cursos as $curso_id) {
    $result = $conn->query("SELECT nombre FROM cursos WHERE id = $curso_id");
    $nombre = $result->fetch_assoc()['nombre'];
    $curso_nombres[] = $nombre;
}

echo "Cursos seleccionados:<ul>";
foreach ($curso_nombres as $nombre) {
    echo "<li>$nombre</li>";
}
echo "</ul>";

echo "<form action='guardar.php' method='post'>";
echo "<input type='hidden' name='documento' value='$documento'>";
foreach ($cursos as $curso_id) {
    echo "<input type='hidden' name='cursos[]' value='$curso_id'>";
}
echo "<input type='submit' value='Confirmar'>";
echo "</form>";
?>