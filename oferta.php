<?php
$documento = $_POST['documento'];
$conn = new mysqli("localhost", "root", "", "matricula");

$result = $conn->query("SELECT * FROM cursos");

echo "<form action='resumen.php' method='post'>";
echo "<input type='hidden' name='documento' value='$documento'>";
while ($row = $result->fetch_assoc()) {
    echo "<input type='checkbox' name='cursos[]' value='{$row['id']}'> {$row['nombre']}<br>";
}
echo "<input type='submit' value='Confirmar selección'>";
echo "</form>";
?>