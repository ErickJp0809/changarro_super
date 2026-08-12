<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit();
}

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    die("No tienes permisos para realizar esta acción.");
}

require_once "../config/conexion.php";


/* Validar datos */

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"]) ||
    !isset($_GET["estado"])
) {
    header("Location: usuarios.php");
    exit();
}

$id = intval($_GET["id"]);
$estado = $_GET["estado"];


/* Solo permitir estos estados */

if (!in_array($estado, ["Activo", "Inactivo"])) {
    header("Location: usuarios.php");
    exit();
}


/*
 * Evitar que el administrador que está
 * actualmente conectado se desactive a sí mismo.
 */

if ($id === intval($_SESSION["id"]) && $estado === "Inactivo") {

    die("No puedes desactivar el usuario con el que estás conectado.");
}


/* Actualizar estado */

$stmt = $conexion->prepare("
    UPDATE usuarios
    SET estado = ?
    WHERE id = ?
");

$stmt->bind_param(
    "si",
    $estado,
    $id
);


if ($stmt->execute()) {

    header("Location: usuarios.php");
    exit();

} else {

    die("No se pudo cambiar el estado del usuario.");
}

?>