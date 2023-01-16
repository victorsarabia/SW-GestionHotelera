<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__."/inc/auth.inc.php";
require_once __DIR__ . "/models/Habitacion.php";

// Recogida de parámetros con POST
$action = isset($_POST["action"]) ? $_POST["action"] : "";
$pagina = isset($_POST["pagina"]) ? $_POST["pagina"] : 1;
$num_registros = isset($_POST["num_registros"]) ? $_POST["num_registros"] : 10;
$filters = isset($_POST["filters"]) ? json_decode($_POST["filters"], true) : [];
$arrHabitacion = isset($_POST["habitacion"]) ? json_decode($_POST["habitacion"], true) : [];
//echo "<pre>";
//print_r($_POST);
//echo "</pre>";
//exit();

// Inicialización de variables
$success = true;
$data = [];
$msg = "";

// Selección de la acción elegida
try {
    switch ($action) {
        case "get":
            $data = Habitacion::find($filters, $pagina, $num_registros);
            $msg = "Listado de habitaciones";
            break;

        case "insert":
            $nombre = isset($arrHabitacion["nombre"]) ? $arrHabitacion["nombre"] : null;
            $descripcion = isset($arrHabitacion["descripcion"]) ? $arrHabitacion["descripcion"] : null;
            $cantidad = isset($arrHabitacion["cantidad"]) ? $arrHabitacion["cantidad"] : null;
            $precio = isset($arrHabitacion["precio"]) ? $arrHabitacion["precio"] : null;
            $numero_maximo_personas = isset($arrHabitacion["numero_maximo_personas"]) ? $arrHabitacion["numero_maximo_personas"] : null;
            $numero_camas = isset($arrHabitacion["numero_camas"]) ? $arrHabitacion["numero_camas"] : null;
            $fecha_baja = isset($arrHabitacion["fecha_baja"]) ? $arrHabitacion["fecha_baja"] : null;

            $habitacion = new Habitacion(null, $nombre, $descripcion, $cantidad, $precio, $numero_maximo_personas, $numero_camas, $fecha_baja);
            if ($habitacion->insert()) {
                $msg = "Habitación insertada correctamente.";
            } else {
                $success = false;
                $msg = "Error al insertar la Habitación.";
            }
            break;

        case "update":
            $id = isset($arrHabitacion["id"]) ? $arrHabitacion["id"] : null;
            $nombre = isset($arrHabitacion["nombre"]) ? $arrHabitacion["nombre"] : null;
            $descripcion = isset($arrHabitacion["descripcion"]) ? $arrHabitacion["descripcion"] : null;
            $cantidad = isset($arrHabitacion["cantidad"]) ? $arrHabitacion["cantidad"] : null;
            $precio = isset($arrHabitacion["precio"]) ? $arrHabitacion["precio"] : null;
            $numero_maximo_personas = isset($arrHabitacion["numero_maximo_personas"]) ? $arrHabitacion["numero_maximo_personas"] : null;
            $numero_camas = isset($arrHabitacion["numero_camas"]) ? $arrHabitacion["numero_camas"] : null;
            $fecha_baja = isset($arrHabitacion["fecha_baja"]) ? $arrHabitacion["fecha_baja"] : null;

            $habitacion = new Habitacion($id, $nombre, $descripcion, $cantidad, $precio, $numero_maximo_personas, $numero_camas, $fecha_baja);
            if ($habitacion->update()) {
                $msg = "Habitación actualizada correctamente.";
                $data = $habitacion->toArray();
            } else {
                $success = false;
                $msg = "Error al actualizar la Habitación.";
            }
            break;

        case "delete":
            $id = isset($arrHabitacion["id"]) ? $arrHabitacion["id"] : null;

            $habitacion = new Habitacion($id);
            if ($habitacion->delete()) {
                $msg = "Habitación borrada correctamente.";
                $data = $habitacion->toArray();
            } else {
                $success = false;
                $msg = "Error al borrar la Habitación.";
            }
            break;

        default:
            $success = false;
            $data = [];
            $msg = "Opción no soportada.";
    }

} catch (Exception $e) {
    $success = false;
    $msg = $e->getMessage();
}

$salida = array(
    "data" => $data,
    "msg" => $msg,
    "success" => $success
);

// Verifica que no falle la codificación en el JSON
if ($salida = json_encode($salida)) {
    echo $salida;

} else {
    $salida = array(
        "data" => [],
        "msg" => "Error al parsear el JSON",
        "success" => false
    );

    echo json_encode($salida);
}
