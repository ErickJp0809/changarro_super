<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $usuario = trim($_POST["usuario"]);
    $contrasena = $_POST["contrasena"];
    $rol = $_POST["rol"];

    if (
        $nombre === "" ||
        $usuario === "" ||
        $contrasena === "" ||
        $rol === ""
    ) {

        $mensaje = "Todos los campos son obligatorios.";

    } else {

        /* Comprobar si el usuario ya existe */

        $stmt = $conexion->prepare(
            "SELECT id FROM usuarios WHERE usuario = ?"
        );

        $stmt->bind_param("s", $usuario);

        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {

            $mensaje = "Ese nombre de usuario ya existe.";

        } else {

            /* Encriptar contraseña */

            $contrasena_segura =
                password_hash(
                    $contrasena,
                    PASSWORD_DEFAULT
                );


            /* Insertar usuario */

            $stmt = $conexion->prepare(
                "INSERT INTO usuarios
                (nombre, usuario, contrasena, rol, estado)
                VALUES (?, ?, ?, ?, 'Activo')"
            );

            $stmt->bind_param(
                "ssss",
                $nombre,
                $usuario,
                $contrasena_segura,
                $rol
            );


            if ($stmt->execute()) {

                header(
                    "Location: usuarios.php"
                );

                exit();

            } else {

                $mensaje =
                    "Error al registrar el usuario.";
            }
        }
    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Agregar usuario | Changarro Súper y Más
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

    <style>

        .formulario-usuario {
            max-width: 650px;
        }

        .campo {
            margin-bottom: 18px;
        }

        .campo label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
        }

        .campo input,
        .campo select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
        }

        .mensaje-error {
            background: #ffe8e8;
            color: #b42318;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .acciones {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn-guardar {
            padding: 12px 20px;
            background: #222;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-cancelar {
            padding: 12px 20px;
            background: #eee;
            color: #222;
            border-radius: 8px;
            text-decoration: none;
        }
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-container input {
            padding-right: 45px;
        }

        .btn-mostrar-password {
            position: absolute;
            right: 10px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 18px;
            padding: 5px;
        }
    </style>

</head>

<body>

<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>


    <main class="contenido">

        <div class="encabezado">

            <div>

                <h1>Agregar usuario</h1>

                <p>
                    Registra un nuevo usuario en el sistema.
                </p>

            </div>

        </div>


        <section class="panel formulario-usuario">

            <h2>
                Información del usuario
            </h2>

            <p>
                Ingresa los datos del nuevo usuario.
            </p>


            <?php if ($mensaje !== ""): ?>

                <div class="mensaje-error">

                    <?php
                    echo htmlspecialchars($mensaje);
                    ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <div class="campo">

                    <label for="nombre">
                        Nombre completo
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        placeholder="Ej. Juan Pérez"
                        required
                    >

                </div>


                <div class="campo">

                    <label for="usuario">
                        Nombre de usuario
                    </label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        placeholder="Ej. juan"
                        required
                    >

                </div>

                <div class="campo">

                    <label for="contrasena">
                        Contraseña
                    </label>

                    <div class="password-container">

                        <input
                            type="password"
                            id="contrasena"
                            name="contrasena"
                            placeholder="Ingresa una contraseña"
                            required
                        >

                        <button
                            type="button"
                            class="btn-mostrar-password"
                            onclick="mostrarPassword()"
                            aria-label="Mostrar contraseña"
                        >
                            👁
                        </button>

                    </div>

                </div>


                <div class="campo">

                    <label for="rol">
                        Rol
                    </label>

                    <select
                        id="rol"
                        name="rol"
                        required
                    >

                        <option value="">
                            Selecciona un rol
                        </option>

                        <option value="Administrador">
                            Administrador
                        </option>

                        <option value="Empleado">
                            Empleado
                        </option>

                    </select>

                </div>


                <div class="acciones">

                    <button
                        type="submit"
                        class="btn-guardar"
                    >
                        Guardar usuario
                    </button>


                    <a
                        href="usuarios.php"
                        class="btn-cancelar"
                    >
                        Cancelar
                    </a>

                </div>


            </form>

        </section>

    </main>

</div>
                <script>

function mostrarPassword() {

    const password =
        document.getElementById("contrasena");

    const boton =
        document.querySelector(".btn-mostrar-password");

    if (password.type === "password") {

        password.type = "text";
        boton.textContent = "🙈";

    } else {

        password.type = "password";
        boton.textContent = "👁";

    }
}

</script>
</body>

</html>