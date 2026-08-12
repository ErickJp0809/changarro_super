<?php

session_start();

require_once "config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario = trim($_POST["usuario"]);
    $contrasena = $_POST["contrasena"];


    /* Buscar usuario */

    $sql = "SELECT id, nombre, usuario, contrasena, rol, estado
            FROM usuarios
            WHERE usuario = ?";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param("s", $usuario);

    $stmt->execute();

    $resultado = $stmt->get_result();


    /* Usuario encontrado */

    if ($resultado->num_rows === 1) {

        $datos_usuario = $resultado->fetch_assoc();


        /* Comprobar si está desactivado */

        if ($datos_usuario["estado"] !== "Activo") {

            header("Location: index.php?error=desactivado");
            exit();

        }


        /* Comprobar contraseña */

        if (
            password_verify(
                $contrasena,
                $datos_usuario["contrasena"]
            )
        ) {

            $_SESSION["id"] =
                $datos_usuario["id"];

            $_SESSION["nombre"] =
                $datos_usuario["nombre"];

            $_SESSION["usuario"] =
                $datos_usuario["usuario"];

            $_SESSION["rol"] =
                $datos_usuario["rol"];


            header("Location: admin/dashboard.php");
            exit();

        } else {

            header("Location: index.php?error=incorrecta");
            exit();

        }


    } else {

        /* Usuario inexistente */

        header("Location: index.php?error=incorrecta");
        exit();

    }

}


/* Si alguien entra directamente a login.php */

header("Location: index.php");
exit();

?>