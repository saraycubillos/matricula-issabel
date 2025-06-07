#!/usr/bin/php -q
<?php
require('phpagi.php');

$agi = new AGI();
$agi->answer();
$agi->verbose("Iniciando script de matrícula", 1);

$agi->text2wav("Bienvenido al sistema de matrículas telefónicas");

require('definiciones.inc');
$link = mysqli_connect(MAQUINA, USUARIO, CLAVE);
if (!$link) {
    $agi->verbose("Error de conexión: " . mysqli_connect_error(), 1);
    $agi->text2wav("Error al conectar con la base de datos");
    $agi->hangup();
    exit;
}
mysqli_select_db($link, DB);

$agi->text2wav("Por favor digite su número de documento seguido de la tecla numeral");
$doc_input = $agi->get_data('beep', 5000, 10);
$documento = $doc_input['result'];
$agi->verbose("Documento ingresado: $documento", 1);

$query_usuario = "SELECT id, nombre FROM usuarios WHERE documento = '$documento'";
$result_usuario = mysqli_query($link, $query_usuario);

if ($result_usuario && mysqli_num_rows($result_usuario) > 0) {
    $usuario = mysqli_fetch_assoc($result_usuario);
    $id_usuario = $usuario['id'];
    $nombre_usuario = $usuario['nombre'];

    $agi->text2wav("Hola $nombre_usuario, a continuación escuchará los cursos disponibles");

    do {
        $query_oferta = "SELECT id, nombre FROM cursos";
        $result_oferta = mysqli_query($link, $query_oferta);

        $cursos_disponibles = [];
        $curso_opciones = [];

        $opcion = 1;
        while ($curso = mysqli_fetch_assoc($result_oferta)) {
            $nombre_curso = $curso['nombre'];
            $agi->text2wav("Opción $opcion: $nombre_curso");
            $curso_opciones[$opcion] = $curso['id'];
            $cursos_disponibles[$opcion] = $nombre_curso;
            $opcion++;
            sleep(1);
        }

        $agi->text2wav("Digite las opciones de los cursos que desea matricular, una por una, seguidas de la tecla numeral. Marque cero para terminar.");
        $cursos_seleccionados = [];

        while (true) {
            $respuesta = $agi->get_data('beep', 5000, 1)['result'];
            if ($respuesta == "0") break;

            if (isset($curso_opciones[$respuesta])) {
                $id_curso = $curso_opciones[$respuesta];
                if (!in_array($id_curso, $cursos_seleccionados)) {
                    $cursos_seleccionados[] = $id_curso;
                }
            } 
        }

        if (count($cursos_seleccionados) === 0) {
            $agi->text2wav("No seleccionó ningún curso");
            $agi->hangup();
            exit;
        }

        $agi->text2wav("Usted ha seleccionado los siguientes cursos");
        foreach ($cursos_seleccionados as $id_curso) {
            $nombre = $cursos_disponibles[array_search($id_curso, $curso_opciones)];
            $agi->text2wav($nombre);
            sleep(1);
        }

        $agi->text2wav("Para confirmar su matrícula digite uno. Para corregir su selección digite dos. Para cancelar digite tres.");
        $confirmacion = $agi->get_data('beep', 5000, 1)['result'];

        if ($confirmacion == "1") {
            foreach ($cursos_seleccionados as $id_curso) {
                $insert = "INSERT INTO matriculas (id_usuario, id_curso) VALUES ($id_usuario, $id_curso)";
                mysqli_query($link, $insert);
            }
            $agi->text2wav("Su matrícula ha sido confirmada. Muchas gracias.");
            break;
        } elseif ($confirmacion == "2") {
            $agi->text2wav("Puede seleccionar nuevamente los cursos.");
        } else {
            $agi->text2wav("Su matrícula ha sido cancelada. Puede intentarlo nuevamente.");
            break;
        }

    } while (true);

} else {
    $agi->text2wav("Documento no encontrado");
}

$agi->hangup();
?>
