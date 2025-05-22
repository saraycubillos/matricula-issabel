<?php
require("definiciones.inc");
header('Content-Type: application/json');

$documento = $_POST['documento'];
$conn = mysqli_connect(MAQUINA, USUARIO, CLAVE, DB);
if (!$conn) {
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$query = "SELECT id, nombre FROM cursos";
$result = mysqli_query($conn, $query);
$cursos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cursos[] = $row;
}

$queryUser = "SELECT id, nombre FROM usuarios WHERE documento = '$documento'";
$userResult = mysqli_query($conn, $queryUser);
if ($usuario = mysqli_fetch_assoc($userResult)) {
    echo json_encode(['usuario' => $usuario, 'cursos' => $cursos]);
} else {
    echo json_encode(['error' => 'Usuario no encontrado']);
}
?>