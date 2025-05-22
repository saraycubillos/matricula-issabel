<?php
require("definiciones.inc");
header('Content-Type: application/json');

$documento = $_POST['documento'];
$cursos = $_POST['cursos'];

$conn = mysqli_connect(MAQUINA, USUARIO, CLAVE, DB);
if (!$conn) {
    echo json_encode(['error' => 'Error en la conexión']);
    exit;
}

$res = mysqli_query($conn, "SELECT id FROM usuarios WHERE documento = '$documento'");
$row = mysqli_fetch_assoc($res);
$id_usuario = $row['id'];

foreach ($cursos as $id_curso) {
    $stmt = $conn->prepare("INSERT INTO matriculas (id_usuario, id_curso) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_usuario, $id_curso);
    $stmt->execute();
}

echo json_encode(["mensaje" => "Matrícula registrada con éxito"]);
?>