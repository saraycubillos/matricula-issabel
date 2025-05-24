#!/usr/bin/php -q
<?php
require('phpagi.php');
$agi = new AGI();
$agi->answer();
sleep(1);

// Configuración de la base de datos
require('definiciones.inc');
$link = mysqli_connect(MAQUINA, USUARIO, CLAVE);
mysqli_select_db($link, DB);

// Función para convertir texto a voz con pausas adecuadas
function speak($agi, $text, $pause = 1) {
    $agi->text2wav($text);
    sleep($pause);
}

// Función para capturar entrada DTMF
function get_input($agi, $max_digits = 10, $timeout = 5000) {
    $input = $agi->get_data('silence/1', $timeout, $max_digits);
    return $input['result'];
}

// Bienvenida
speak($agi, "Bienvenido al sistema de matrículas telefónicas");

// Paso 1: Solicitar documento del usuario
speak($agi, "Por favor, ingrese su número de documento seguido de la tecla numeral");
$documento = get_input($agi, 20);

// Verificar usuario
$query = "SELECT id, nombre FROM usuarios WHERE documento = '$documento'";
$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) == 0) {
    speak($agi, "Lo siento, no se encontró un usuario con ese documento");
    sleep(1);
    speak($agi, "Gracias por utilizar nuestro sistema");
    $agi->hangup();
    exit;
}

$usuario = mysqli_fetch_assoc($result);
$id_usuario = $usuario['id'];
$nombre_usuario = $usuario['nombre'];

// Paso 2: Mostrar cursos disponibles
speak($agi, "Estimado $nombre_usuario, estos son los cursos disponibles");

// Obtener cursos no matriculados
$query_cursos = "SELECT c.id, c.nombre 
                FROM cursos c
                LEFT JOIN matriculas m ON c.id = m.id_curso AND m.id_usuario = $id_usuario
                WHERE m.id IS NULL
                ORDER BY c.id";
$result_cursos = mysqli_query($link, $query_cursos);

$cursos = array();
$counter = 1;
while ($row = mysqli_fetch_assoc($result_cursos)) {
    $cursos[$counter] = $row;
    speak($agi, "Para seleccionar $row[nombre], presione $counter");
    $counter++;
}

if (empty($cursos)) {
    speak($agi, "Actualmente no hay cursos disponibles para matrícula");
    sleep(1);
    speak($agi, "Gracias por utilizar nuestro sistema");
    $agi->hangup();
    exit;
}

// Paso 3: Selección de cursos
speak($agi, "Por favor, ingrese los números de los cursos que desea matricular, separados por comas, seguido de la tecla numeral");
speak($agi, "Por ejemplo, para seleccionar los cursos 1 y 3, marque 1 coma 3 numeral");

$seleccion = get_input($agi, 50); // Permite hasta 50 caracteres de entrada

// Procesar selección
$seleccion = rtrim($seleccion, '#');
$cursos_seleccionados = explode(',', $seleccion);
$cursos_validos = array();

foreach ($cursos_seleccionados as $numero) {
    $numero = trim($numero);
    if (is_numeric($numero) && isset($cursos[$numero])) {
        $cursos_validos[] = $cursos[$numero]['id'];
    }
}

// Paso 4: Confirmación
if (!empty($cursos_validos)) {
    speak($agi, "Usted ha seleccionado los siguientes cursos:");
    
    foreach ($cursos_validos as $curso_id) {
        $query_nombre = "SELECT nombre FROM cursos WHERE id = $curso_id";
        $result_nombre = mysqli_query($link, $query_nombre);
        $nombre_curso = mysqli_fetch_assoc($result_nombre)['nombre'];
        speak($agi, $nombre_curso);
    }
    
    speak($agi, "Para confirmar la matrícula, presione 1");
    speak($agi, "Para corregir la selección, presione 2");
    $confirmacion = get_input($agi, 1);
    
    if ($confirmacion == '1') {
        // Registrar matrículas
        foreach ($cursos_validos as $curso_id) {
            mysqli_query($link, "INSERT INTO matriculas (id_usuario, id_curso) VALUES ($id_usuario, $curso_id)");
        }
        
        speak($agi, "Matrícula confirmada exitosamente");
        sleep(1);
        speak($agi, "Gracias por utilizar nuestro sistema");
    } elseif ($confirmacion == '2') {
        // Volver a selección de cursos (implementación simplificada)
        speak($agi, "Redirigiendo para corregir la selección");
        // En un sistema real, aquí iría la lógica para repetir el proceso
    } else {
        speak($agi, "Opción no válida");
    }
} else {
    speak($agi, "No ha seleccionado cursos válidos");
}

// Despedida
sleep(1);
speak($agi, "Gracias por utilizar el sistema de matrículas telefónicas. Hasta pronto");
$agi->hangup();
?>