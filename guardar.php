<?php
$conn = new mysqli("localhost", "root", "", "matricula");
$documento = $_POST['documento'];
$cursos = $_POST['cursos'];

foreach ($cursos as $curso_id) {
    $conn->query("INSERT INTO matriculas (documento, curso_id) VALUES ('$documento', $curso_id)");
}

echo "¡Matrícula registrada! Un asesor se comunicará contigo.";
?>