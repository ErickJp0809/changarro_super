<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit();
}

require_once "../config/conexion.php";

if (!isset($_GET["id"])) {
    header("Location: productos.php");
    exit();
}

$id = $_GET["id"];

$sql = "UPDATE productos
        SET activo = 0
        WHERE id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: productos.php");
exit();