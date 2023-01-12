<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . "/models/User.php";

// Recogida de parámetros con POST
$action = isset($_POST["action"])? $_POST["action"]:"";
$pagina = isset($_POST["pagina"])? $_POST["pagina"]:1;
$num_registros = isset($_POST["num_registros"])? $_POST["num_registros"]:10;
$filters = isset($_POST["filters"])? json_decode($_POST["filters"], true):[];
$arrUser = isset($_POST["user"])? json_decode($_POST["user"], true):[];
//echo "<pre>";
//print_r($_POST);
//echo "</pre>";

// Inicialización de variables
$success = true;
$data = [];
$msg = "";

// Selección de la acción elegida
try {
    switch ($action){
        case "get":
            $data = User::find($filters, $pagina, $num_registros);
            $msg = "Listado de usuarios";
            break;

        case "insert":
            $email = isset($arrUser["email"])?$arrUser["email"]:null;
            $password = isset($arrUser["password"])?$arrUser["password"]:null;
            $nombre = isset($arrUser["nombre"])?$arrUser["nombre"]:null;
            $apellidos = isset($arrUser["apellidos"])?$arrUser["apellidos"]:null;
            $telefono = isset($arrUser["telefono"])?$arrUser["telefono"]:null;
            $fecha_baja = isset($arrUser["fecha_baja"])?$arrUser["fecha_baja"]:null;

            $user = new User($email, $password, null, null, $nombre, $apellidos, $telefono, $fecha_baja);
            if ($user->insert()) {
                $msg = "Usuario insertado correctamente.";
            } else {
                $success = false;
                $msg = "Error al insertar el usuario.";
            }
            break;

        case "update":
            $email = isset($arrUser["email"])?$arrUser["email"]:null;
            $password = isset($arrUser["password"])?$arrUser["password"]:null;
            $token = isset($arrUser["token"])?$arrUser["token"]:null;
            $fecha_validez_token = isset($arrUser["fecha_validez_token"])?$arrUser["fecha_validez_token"]:null;
            $nombre = isset($arrUser["nombre"])?$arrUser["nombre"]:null;
            $apellidos = isset($arrUser["apellidos"])?$arrUser["apellidos"]:null;
            $telefono = isset($arrUser["telefono"])?$arrUser["telefono"]:null;
            $fecha_baja = isset($arrUser["fecha_baja"])?$arrUser["fecha_baja"]:null;

            $user = new User($email, $password, $token, $fecha_validez_token, $nombre, $apellidos, $telefono, $fecha_baja);
            if ($user->update()) {
                $msg = "Usuario actualizado correctamente.";
                $data = $user->toArray();
            } else {
                $success = false;
                $msg = "Error al actualizar el usuario.";
            }
            break;

        case "delete":
            $email = isset($arrUser["email"])?$arrUser["email"]:null;

            $user = new User($email);
            if ($user->delete()) {
                $msg = "Usuario borrado correctamente.";
                $data = $user->toArray();
            } else {
                $success = false;
                $msg = "Error al borrar el usuario.";
            }
            break;

        case "login":
            $email = isset($arrUser["email"])?$arrUser["email"]:null;
            $password = isset($arrUser["password"])?$arrUser["password"]:null;

            $user = new User($email, $password);
            $user = $user->login();

            if (!empty($user->getToken())) {
                $msg = "Usuario logueado correctamente.";
                $data = $user->toArray();
            } else {
                $success = false;
                $msg = "Error, usuario o contraseña incorrecta o sin permisos.";
            }
            break;

        case "logout":
            $email = isset($arrUser["email"])?$arrUser["email"]:null;
            $password = isset($arrUser["password"])?$arrUser["password"]:null;

            $user = new User($email, $password);

            if ($user->logout()) {
                $msg = "Sesión cerrada correctamente.";
            } else {
                $success = false;
                $msg = "Error al cerrar la sesión.";
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
if ($salida= json_encode($salida)){
    echo $salida;

} else {
    $salida = array(
        "data" => [],
        "msg" => "Error al parsear el JSON",
        "success" => false
    );

    echo json_encode($salida);
}

