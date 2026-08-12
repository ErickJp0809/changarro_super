<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/conexion.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: usuarios.php");
    exit();
}

$id = intval($_GET["id"]);

$mensaje = "";


/* =========================
   OBTENER USUARIO
   ========================= */

$sql = "
    SELECT id, nombre, usuario, rol
    FROM usuarios
    WHERE id = $id
";

$resultado = $conexion->query($sql);

if ($resultado->num_rows === 0) {
    die("El usuario no existe.");
}

$usuario = $resultado->fetch_assoc();


/* =========================
   ACTUALIZAR USUARIO
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $nombre_usuario = trim($_POST["usuario"]);
    $rol = $_POST["rol"];
    $nueva_contrasena = $_POST["contrasena"];


    if (
        $nombre === "" ||
        $nombre_usuario === "" ||
        $rol === ""
    ) {

        $mensaje =
            "Nombre, usuario y rol son obligatorios.";

    } else {

        /* Comprobar que el usuario no esté repetido */

        $stmt = $conexion->prepare("
            SELECT id
            FROM usuarios
            WHERE usuario = ?
            AND id != ?
        ");

        $stmt->bind_param(
            "si",
            $nombre_usuario,
            $id
        );

        $stmt->execute();

        $resultado_usuario =
            $stmt->get_result();


        if ($resultado_usuario->num_rows > 0) {

            $mensaje =
                "Ese nombre de usuario ya existe.";

        } else {

            /*
             * Si escribió una contraseña nueva,
             * también la actualizamos.
             */

            if ($nueva_contrasena !== "") {

                $contrasena_segura =
                    password_hash(
                        $nueva_contrasena,
                        PASSWORD_DEFAULT
                    );

                $stmt = $conexion->prepare("
                    UPDATE usuarios
                    SET
                        nombre = ?,
                        usuario = ?,
                        contrasena = ?,
                        rol = ?
                    WHERE id = ?
                ");

                $stmt->bind_param(
                    "ssssi",
                    $nombre,
                    $nombre_usuario,
                    $contrasena_segura,
                    $rol,
                    $id
                );

            } else {

                $stmt = $conexion->prepare("
                    UPDATE usuarios
                    SET
                        nombre = ?,
                        usuario = ?,
                        rol = ?
                    WHERE id = ?
                ");

                $stmt->bind_param(
                    "sssi",
                    $nombre,
                    $nombre_usuario,
                    $rol,
                    $id
                );
            }


            if ($stmt->execute()) {

                header(
                    "Location: usuarios.php"
                );

                exit();

            } else {

                $mensaje =
                    "Error al actualizar el usuario.";
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
        Editar usuario | Changarro Súper y Más
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

        .ayuda {
            margin-top: 6px;
            font-size: 13px;
            color: #777;
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

    </style>

</head>

<body>

<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>


    <main class="contenido">

        <div class="encabezado">

            <div>

                <h1>Editar usuario</h1>

                <p>
                    Modifica la información del usuario.
                </p>

            </div>

        </div>


        <section class="panel formulario-usuario">

            <h2>
                Información del usuario
            </h2>


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
                        value="<?php
                            echo htmlspecialchars(
                                $usuario["nombre"]
                            );
                        ?>"
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
                        value="<?php
                            echo htmlspecialchars(
                                $usuario["usuario"]
                            );
                        ?>"
                        required
                    >

                </div>


                <div class="campo">

                    <label for="contrasena">
                        Nueva contraseña
                    </label>

                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        placeholder="Dejar vacío para conservar la actual"
                    >

                    <div class="ayuda">
                        Solo escribe una contraseña si deseas cambiarla.
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

                        <option
                            value="Administrador"
                            <?php
                            if (
                                $usuario["rol"]
                                === "Administrador"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            Administrador
                        </option>

                        <option
                            value="Empleado"
                            <?php
                            if (
                                $usuario["rol"]
                                === "Empleado"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            Empleado
                        </option>

                    </select>

                </div>


                <div class="acciones">

                    <button
                        type="submit"
                        class="btn-guardar"
                    >
                        Guardar cambios
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

</body>

</html>