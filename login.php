<?php

session_start();

require_once "config/conexion.php";


/* =========================================
   PROCESAR LOGIN
   ========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = trim($_POST["usuario"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";


    /* =========================================
       VALIDAR CAMPOS
       ========================================= */

    if (
        $usuario === "" ||
        $contrasena === ""
    ) {

        header(
            "Location: index.php?error=incorrecta"
        );

        exit();

    }


    /* =========================================
       BUSCAR USUARIO
       ========================================= */

    $sql = "
        SELECT
            id,
            nombre,
            usuario,
            contrasena,
            rol,
            estado
        FROM usuarios
        WHERE usuario = ?
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);


    if (!$stmt) {

        header(
            "Location: index.php?error=incorrecta"
        );

        exit();

    }


    $stmt->bind_param(
        "s",
        $usuario
    );

    $stmt->execute();

    $resultado = $stmt->get_result();


    /* =========================================
       COMPROBAR SI EXISTE
       ========================================= */

    if ($resultado->num_rows !== 1) {

        header(
            "Location: index.php?error=incorrecta"
        );

        exit();

    }


    $datos_usuario =
        $resultado->fetch_assoc();


    /* =========================================
       COMPROBAR ESTADO
       ========================================= */

    if (
        $datos_usuario["estado"] !== "Activo"
    ) {

        header(
            "Location: index.php?error=desactivado"
        );

        exit();

    }


    /* =========================================
       COMPROBAR CONTRASEÑA
       ========================================= */

    if (
        !password_verify(
            $contrasena,
            $datos_usuario["contrasena"]
        )
    ) {

        header(
            "Location: index.php?error=incorrecta"
        );

        exit();

    }


    /* =========================================
       REGENERAR ID DE SESIÓN
       ========================================= */

    session_regenerate_id(true);


    /* =========================================
       GUARDAR DATOS EN SESIÓN
       ========================================= */

    $_SESSION["id"] =
        $datos_usuario["id"];

    $_SESSION["nombre"] =
        $datos_usuario["nombre"];

    $_SESSION["usuario"] =
        $datos_usuario["usuario"];

    $_SESSION["rol"] =
        $datos_usuario["rol"];


    /* =========================================
       REDIRECCIÓN SEGÚN ROL
       ========================================= */

    switch (
        $datos_usuario["rol"]
    ) {


        /* ==============================
           ADMINISTRADOR
           ============================== */

        case "Administrador":

            header(
                "Location: admin/dashboard.php"
            );

            exit();


        /* ==============================
           EMPLEADO
           ============================== */

        case "Empleado":

            header(
                "Location: admin/dashboard.php"
            );

            exit();


        /* ==============================
           CLIENTE
           ============================== */

        case "Cliente":

            header(
                "Location: clientes/dashboard_clientes.php"
            );

            exit();


        /* ==============================
           ROL DESCONOCIDO
           ============================== */

        default:

            session_unset();

            session_destroy();

            header(
                "Location: index.php?error=incorrecta"
            );

            exit();

    }

}


/* =========================================
   SI ENTRAN DIRECTAMENTE A login.php
   ========================================= */

header(
    "Location: index.php"
);

exit();

?>