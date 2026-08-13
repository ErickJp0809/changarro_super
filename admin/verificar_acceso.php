<?php

session_start();


/* =========================================
   VERIFICAR SESIÓN
   ========================================= */

if (!isset($_SESSION["id"])) {

    header("Location: ../index.php");
    exit();

}


/* =========================================
   VERIFICAR ROL
   ========================================= */

$rol = $_SESSION["rol"] ?? "";


/*
 * Solamente Administradores y Empleados
 * pueden entrar al panel administrativo.
 */

if (
    $rol !== "Administrador" &&
    $rol !== "Empleado"
) {

    header("Location: ../clientes/dashboard_clientes.php");
    exit();

}

?>