<?php
session_start();
require("definiciones.inc");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["documento"])) {
    $_SESSION["documento"] = $_POST["documento"];
    $_SESSION["nombre"] = $_POST["nombre"];
}

$link = mysqli_connect(MAQUINA, USUARIO, CLAVE, DB);
if (!$link) {
    die("Error de conexión: " . mysqli_connect_error());
}

$documento = $_SESSION["documento"];
$nombre = $_SESSION["nombre"];

$result = mysqli_query($link, "SELECT id, nombre FROM cursos");

echo "<h2>Bienvenido, " . htmlentities($nombre) . "</h2>";
echo "<form method='POST' action='confirmar.php'>";
echo "<input type='hidden' name='documento' value='" . htmlentities($documento) . "'>";
echo "<input type='hidden' name='nombre' value='" . htmlentities($nombre) . "'>";
echo "<h3>Seleccione los cursos:</h3>";
while ($row = mysqli_fetch_array($result)) {
    echo "<input type='checkbox' name='cursos[]' value='" . $row['id'] . "'> " . htmlentities($row['nombre']) . "<br>";
}
echo "<input type='submit' value='Confirmar matrícula'>";
echo "</form>";
?>
