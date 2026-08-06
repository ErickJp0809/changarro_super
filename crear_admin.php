<?php

require_once "config/conexion.php";

$nombre = "Administrador";
$usuario = "admin";
$contrasena = "admin123";
$rol = "administrador";

$contrasena_segura = password_hash($contrasena, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, usuario, contrasena, rol)
        VALUES (?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param(
    "ssss",
    $nombre,
    $usuario,
    $contrasena_segura,
    $rol
);

if ($stmt->execute()) {
    echo "Administrador creado correctamente.";
} else {
    echo "Error al crear administrador: " . $stmt->error;
}

$stmt->close();
$conexion->close();

?>