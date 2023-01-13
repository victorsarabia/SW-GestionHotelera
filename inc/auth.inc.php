<?php
require_once __DIR__ . "/../models/User.php";

// Recogida de parámetros con POST
$token = isset($_POST["token"])? $_POST["token"]:"";

// Inicialización de variables
$user = null;

if (empty($token) || !($user = User::getUserLogued($token))){
    $salida = array(
        "data" => [],
        "msg" => "Usuario no identificado.",
        "success" => false
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

    // Para que no ejecute nada más donde sea llamado
    exit();
}




