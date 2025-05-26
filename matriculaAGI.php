#!/usr/bin/php -q
<?php
require('phpagi.php');
$agi = new AGI();
$agi->answer();
$agi->text2wav("Bienvenido");
sleep(1);

// 1. Pedir documento
$agi->text2wav("Ingrese su documento seguido de numeral");
$documento = $agi->get_data('silence', 5000, 20)['result']; // Captura 20 dígitos

// 2. Sanitizar (eliminar caracteres no numéricos)
$documento = preg_replace('/[^0-9]/', '', $documento);

// 3. Buscar en BD
require('definiciones.inc');
$link = mysqli_connect(MAQUINA, USUARIO, CLAVE);
mysqli_select_db($link, DB);
$result = mysqli_query($link, "SELECT nombre FROM usuarios WHERE documento = '$documento'");

// 4. Responder
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $agi->text2wav("Bienvenido, " . $row['nombre']);
} else {
    $agi->text2wav("Documento no encontrado");
}

$agi->hangup();
?>