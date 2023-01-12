<?php
session_start();

if (!isset($_SESSION["user_id"])){
    //TODO Redirigir a login en el frontend
    //TODO Devolver JSON con el error de autorización
    exit();
}


