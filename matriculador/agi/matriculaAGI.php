#!/usr/bin/php -q
<?php
require('phpagi.php');

/**
 * Helper que delega la síntesis en el script googletts.agi ya probado.
 * $agi->exec('AGI', "googletts.agi,\"Texto a decir\",es");
 */
function sayTTS(AGI $agi, string $text, string $lang = 'es'): void
{
    $escaped = str_replace('"', '\"', $text); // escapado seguro de comillas dobles
    $agi->exec('AGI', "googletts.agi,\"$escaped\",$lang");
}

$agi = new AGI();
$agi->answer();
$agi->verbose("Iniciando script de matrícula", 1);

sayTTS($agi, 'Bienvenido al sistema de matrículas telefónicas');

require('definiciones.inc');
$link = mysqli_connect(MAQUINA, USUARIO, CLAVE);
if (!$link) {
    $agi->verbose('Error de conexión: ' . mysqli_connect_error(), 1);
    sayTTS($agi, 'Error al conectar con la base de datos');
    $agi->hangup();
    exit;
}
mysqli_select_db($link, DB);

sayTTS($agi, 'Por favor digite su número de documento seguido de la tecla numeral');
$doc_input = $agi->get_data('beep', 5000, 10);
$documento = $doc_input['result'] ?? '';
$agi->verbose("Documento ingresado: $documento", 1);

$query_usuario   = "SELECT id, nombre FROM usuarios WHERE documento = '" . mysqli_real_escape_string($link, $documento) . "'";
$result_usuario  = mysqli_query($link, $query_usuario);

if ($result_usuario && mysqli_num_rows($result_usuario) > 0) {
    $usuario       = mysqli_fetch_assoc($result_usuario);
    $id_usuario    = $usuario['id'];
    $nombre_usuario = $usuario['nombre'];

    sayTTS($agi, "Hola $nombre_usuario, a continuación escuchará los cursos disponibles");

    do {
        // 1. Listar cursos disponibles
        $query_oferta  = 'SELECT id, nombre FROM cursos';
        $result_oferta = mysqli_query($link, $query_oferta);

        $curso_opciones     = [];
        $cursos_disponibles = [];
        $opcion             = 1;

        while ($curso = mysqli_fetch_assoc($result_oferta)) {
            $curso_opciones[$opcion]     = $curso['id'];
            $cursos_disponibles[$opcion] = $curso['nombre'];
            sayTTS($agi, "Opción $opcion: {$curso['nombre']}");
            $opcion++;
            usleep(500000); // Espera 0,5 s entre opciones para claridad
        }

        // 2. Solicitar selección
        sayTTS($agi, 'Digite las opciones de los cursos que desea matricular, una por una, seguidas de la tecla numeral. Marque cero para terminar.');
        $cursos_seleccionados = [];

        while (true) {
            $respuesta = $agi->get_data('beep', 5000, 1)['result'] ?? '';
            if ($respuesta === '0') {
                break;
            }
            if (isset($curso_opciones[$respuesta]) && !in_array($curso_opciones[$respuesta], $cursos_seleccionados, true)) {
                $cursos_seleccionados[] = $curso_opciones[$respuesta];
            }
        }

        if (empty($cursos_seleccionados)) {
            sayTTS($agi, 'No seleccionó ningún curso');
            $agi->hangup();
            exit;
        }

        // 3. Confirmación
        sayTTS($agi, 'Usted ha seleccionado los siguientes cursos');
        foreach ($cursos_seleccionados as $id_curso) {
            $idx    = array_search($id_curso, $curso_opciones, true);
            $nombre = $cursos_disponibles[$idx] ?? 'Curso';
            sayTTS($agi, $nombre);
            usleep(500000);
        }

        sayTTS($agi, 'Para confirmar su matrícula digite uno. Para corregir su selección digite dos. Para cancelar digite tres.');
        $confirmacion = $agi->get_data('beep', 5000, 1)['result'] ?? '';

        if ($confirmacion === '1') {
            foreach ($cursos_seleccionados as $id_curso) {
                $insert = "INSERT INTO matriculas (id_usuario, id_curso) VALUES ($id_usuario, $id_curso)";
                mysqli_query($link, $insert);
            }
            sayTTS($agi, 'Su matrícula ha sido confirmada. Muchas gracias.');
            break;
        } elseif ($confirmacion === '2') {
            sayTTS($agi, 'Puede seleccionar nuevamente los cursos.');
        } else {
            sayTTS($agi, 'Su matrícula ha sido cancelada. Puede intentarlo nuevamente.');
            break;
        }
    } while (true);

} else {
    sayTTS($agi, 'Documento no encontrado');
}

$agi->hangup();
?>
