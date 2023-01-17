<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__."/inc/auth.inc.php";
require_once __DIR__ . "/models/Reserva.php";

// Recogida de parámetros con POST
$action = isset($_POST["action"]) ? $_POST["action"] : "";
$pagina = isset($_POST["pagina"]) ? $_POST["pagina"] : 1;
$num_registros = isset($_POST["num_registros"]) ? $_POST["num_registros"] : 10;
$filters = isset($_POST["filters"]) ? json_decode($_POST["filters"], true) : [];
$arrReserva = isset($_POST["reserva"]) ? json_decode($_POST["reserva"], true) : [];
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
            $data = Reserva::find($filters, $pagina, $num_registros);
            $msg = "Listado de reservas";
            break;

        case "insert":
            $fecha = isset($arrReserva["fecha"]) ? $arrReserva["fecha"] : null;
            $fecha_entrada = isset($arrReserva["fecha_entrada"]) ? $arrReserva["fecha_entrada"] : null;
            $fecha_salida = isset($arrReserva["fecha_salida"]) ? $arrReserva["fecha_salida"] : null;
            $numero_adultos = isset($arrReserva["numero_adultos"]) ? $arrReserva["numero_adultos"] : null;
            $numero_ninyos = isset($arrReserva["numero_ninyos"]) ? $arrReserva["numero_ninyos"] : null;
            $user_id = isset($arrReserva["user_id"]) ? $arrReserva["user_id"] : null;
            $fecha_baja = isset($arrReserva["fecha_baja"]) ? $arrReserva["fecha_baja"] : null;

            $reserva = new Reserva(null, $fecha, $fecha_entrada, $fecha_salida, $numero_adultos, $numero_ninyos, $user_id, $fecha_baja);
            if ($reserva->insert()) {
                $msg = "Reserva insertada correctamente.";
            } else {
                $success = false;
                $msg = "Error al insertar la Reserva.";
            }
            break;

        case "update":
            $id = isset($arrReserva["id"]) ? $arrReserva["id"] : null;
            $fecha = isset($arrReserva["fecha"]) ? $arrReserva["fecha"] : null;
            $fecha_entrada = isset($arrReserva["fecha_entrada"]) ? $arrReserva["fecha_entrada"] : null;
            $fecha_salida = isset($arrReserva["fecha_salida"]) ? $arrReserva["fecha_salida"] : null;
            $numero_adultos = isset($arrReserva["numero_adultos"]) ? $arrReserva["numero_adultos"] : null;
            $numero_ninyos = isset($arrReserva["numero_ninyos"]) ? $arrReserva["numero_ninyos"] : null;
            $user_id = isset($arrReserva["user_id"]) ? $arrReserva["user_id"] : null;
            $fecha_baja = isset($arrReserva["fecha_baja"]) ? $arrReserva["fecha_baja"] : null;

            $reserva = new Reserva($id, $fecha, $fecha_entrada, $fecha_salida, $numero_adultos, $numero_ninyos, $user_id, $fecha_baja);
            if ($reserva->update()) {
                $msg = "Reserva actualizada correctamente.";
                $data = $reserva->toArray();
            } else {
                $success = false;
                $msg = "Error al actualizar la Reserva.";
            }
            break;

        case "delete":
            $id = isset($arrReserva["id"]) ? $arrReserva["id"] : null;

            $reserva = new Reserva($id);
            if ($reserva->delete()) {
                $msg = "Reserva borrada correctamente.";
                $data = $reserva->toArray();
            } else {
                $success = false;
                $msg = "Error al borrar la Reserva.";
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
