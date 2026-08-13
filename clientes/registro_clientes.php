<?php

session_start();

require_once "../config/conexion.php";

$mensaje = "";
$tipo_mensaje = "";


/* =========================================
   SI YA HAY SESIÓN
   ========================================= */

if (isset($_SESSION["id"])) {

    if ($_SESSION["rol"] === "Cliente") {

        header(
            "Location: dashboard_clientes.php"
        );

        exit();

    }

    if (
        $_SESSION["rol"] === "Administrador" ||
        $_SESSION["rol"] === "Empleado"
    ) {

        header(
            "Location: ../admin/dashboard.php"
        );

        exit();

    }

}


/* =========================================
   REGISTRO
   ========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre =
        trim($_POST["nombre"]);

    $usuario =
        trim($_POST["usuario"]);

    $contrasena =
        $_POST["contrasena"];

    $confirmar_contrasena =
        $_POST["confirmar_contrasena"];


    /* =====================================
       VALIDACIONES
       ===================================== */

    if (
        $nombre === "" ||
        $usuario === "" ||
        $contrasena === "" ||
        $confirmar_contrasena === ""
    ) {

        $mensaje =
            "Todos los campos son obligatorios.";

        $tipo_mensaje = "error";

    } elseif (
        strlen($usuario) < 4
    ) {

        $mensaje =
            "El usuario debe tener al menos 4 caracteres.";

        $tipo_mensaje = "error";

    } elseif (
        strlen($contrasena) < 6
    ) {

        $mensaje =
            "La contraseña debe tener al menos 6 caracteres.";

        $tipo_mensaje = "error";

    } elseif (
        $contrasena !== $confirmar_contrasena
    ) {

        $mensaje =
            "Las contraseñas no coinciden.";

        $tipo_mensaje = "error";

    } else {


        /* =================================
           COMPROBAR USUARIO
           ================================= */

        $stmt = $conexion->prepare(
            "SELECT id
             FROM usuarios
             WHERE usuario = ?"
        );

        $stmt->bind_param(
            "s",
            $usuario
        );

        $stmt->execute();

        $resultado =
            $stmt->get_result();


        if (
            $resultado->num_rows > 0
        ) {

            $mensaje =
                "Ese nombre de usuario ya está registrado.";

            $tipo_mensaje = "error";

        } else {


            /* ==============================
               ENCRIPTAR CONTRASEÑA
               ============================== */

            $contrasena_segura =
                password_hash(
                    $contrasena,
                    PASSWORD_DEFAULT
                );


            /* ==============================
               CREAR CLIENTE
               ============================== */

            $rol = "Cliente";

            $estado = "Activo";


            $stmt = $conexion->prepare(
                "INSERT INTO usuarios
                (
                    nombre,
                    usuario,
                    contrasena,
                    rol,
                    estado
                )
                VALUES (?, ?, ?, ?, ?)"
            );


            $stmt->bind_param(
                "sssss",
                $nombre,
                $usuario,
                $contrasena_segura,
                $rol,
                $estado
            );


            if ($stmt->execute()) {


                /* ==========================
                   REGISTRO EXITOSO
                   ========================== */

                header(
                    "Location: ../index.php?registro=exitoso"
                );

                exit();


            } else {

                $mensaje =
                    "No fue posible crear la cuenta. Inténtalo nuevamente.";

                $tipo_mensaje = "error";

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
        Crear cuenta | Changarro Súper y Más
    </title>

    <link
        rel="stylesheet"
        href="../css/estilos.css"
    >

    <style>

        /* =================================
           REGISTRO
           ================================= */

        .registro-box {

            width: 100%;

            max-width: 430px;

            background: #ffffff;

            padding: 30px 40px;

            border-radius: 20px;

            border: 1px solid #e8e8e8;

            box-shadow:
                0 15px 45px
                rgba(0, 0, 0, 0.10);

        }


        .registro-logo {

            width: 100%;

            height: 110px;

            display: flex;

            justify-content: center;

            align-items: center;

            margin-bottom: 10px;

            overflow: hidden;

        }


        .registro-logo img {

            width: 100px;

            height: 100px;

            object-fit: contain;

        }


        .registro-box h1 {

            text-align: center;

            font-size: 25px;

            margin-bottom: 7px;

            color: #222;

        }


        .registro-subtitulo {

            text-align: center;

            color: #777;

            font-size: 14px;

            margin-bottom: 25px;

        }


        .registro-campo {

            margin-bottom: 17px;

        }


        .registro-campo label {

            display: block;

            margin-bottom: 7px;

            font-size: 14px;

            font-weight: 600;

            color: #333;

        }


        .registro-campo input {

            width: 100%;

            height: 47px;

            padding: 0 14px;

            border: 1px solid #d8d8d8;

            border-radius: 9px;

            outline: none;

            font-family: inherit;

            font-size: 14px;

        }


        .registro-campo input:focus {

            border-color: #f7941d;

            box-shadow:
                0 0 0 3px
                rgba(247, 148, 29, 0.14);

        }


        .registro-password {

            position: relative;

        }


        .registro-password input {

            padding-right: 45px;

        }


        .btn-mostrar-password {

            position: absolute;

            right: 8px;

            top: 5px;

            width: 36px;

            height: 36px;

            border: none;

            background: transparent;

            cursor: pointer;

            font-size: 17px;

        }


        .btn-registrar {

            width: 100%;

            height: 48px;

            margin-top: 5px;

            border: none;

            border-radius: 9px;

            background: #f7941d;

            color: white;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.2s;

        }


        .btn-registrar:hover {

            background: #e98212;

            transform: translateY(-1px);

        }


        .mensaje-registro {

            margin-bottom: 18px;

            padding: 12px 14px;

            border-radius: 9px;

            text-align: center;

            font-size: 13px;

        }


        .mensaje-registro.error {

            background: #fff1f0;

            color: #b42318;

            border: 1px solid #ffd0cc;

        }


        .registro-login {

            text-align: center;

            margin-top: 22px;

            font-size: 13px;

            color: #777;

        }


        .registro-login a {

            color: #249db5;

            font-weight: bold;

            text-decoration: none;

        }


        .registro-login a:hover {

            text-decoration: underline;

        }


        .registro-colores {

            display: flex;

            justify-content: center;

            gap: 6px;

            margin-top: 20px;

        }


        .registro-colores span {

            width: 25px;

            height: 4px;

            border-radius: 10px;

        }


        .registro-naranja {

            background: #f7941d;

        }


        .registro-azul {

            background: #249db5;

        }


        .registro-rojo {

            background: #d93662;

        }


        .registro-amarillo {

            background: #f9c52b;

        }


        @media (max-width: 500px) {

            .registro-box {

                padding: 25px;

            }

        }

    </style>

</head>


<body>


<div class="login-container">


    <div class="registro-box">


        <!-- =================================
             LOGO
             ================================= -->

        <div class="registro-logo">

            <img
                src="../img/logo_changarro_transparente.png"
                alt="Changarro Súper y Más"
            >

        </div>


        <!-- =================================
             TÍTULO
             ================================= -->

        <h1>
            Crear cuenta
        </h1>


        <p class="registro-subtitulo">

            Regístrate para comenzar a comprar.

        </p>


        <!-- =================================
             MENSAJE
             ================================= -->

        <?php if ($mensaje !== ""): ?>

            <div
                class="mensaje-registro <?php echo $tipo_mensaje; ?>"
            >

                <?php

                echo htmlspecialchars(
                    $mensaje
                );

                ?>

            </div>

        <?php endif; ?>


        <!-- =================================
             FORMULARIO
             ================================= -->

        <form
            method="POST"
            autocomplete="off"
        >


            <!-- NOMBRE -->

            <div class="registro-campo">

                <label for="nombre">
                    Nombre completo
                </label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Ej. Juan Pérez"
                    maxlength="100"
                    value="<?php
                        echo isset($_POST["nombre"])
                            ? htmlspecialchars($_POST["nombre"])
                            : "";
                    ?>"
                    required
                >

            </div>


            <!-- USUARIO -->

            <div class="registro-campo">

                <label for="usuario">
                    Nombre de usuario
                </label>

                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    placeholder="Ej. juan123"
                    maxlength="50"
                    value="<?php
                        echo isset($_POST["usuario"])
                            ? htmlspecialchars($_POST["usuario"])
                            : "";
                    ?>"
                    required
                >

            </div>


            <!-- CONTRASEÑA -->

            <div class="registro-campo">

                <label for="contrasena">
                    Contraseña
                </label>


                <div class="registro-password">

                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        placeholder="Mínimo 6 caracteres"
                        required
                    >


                    <button
                        type="button"
                        class="btn-mostrar-password"
                        onclick="mostrarPassword(
                            'contrasena',
                            this
                        )"
                    >
                        👁
                    </button>

                </div>

            </div>


            <!-- CONFIRMAR CONTRASEÑA -->

            <div class="registro-campo">

                <label for="confirmar_contrasena">
                    Confirmar contraseña
                </label>


                <div class="registro-password">

                    <input
                        type="password"
                        id="confirmar_contrasena"
                        name="confirmar_contrasena"
                        placeholder="Repite tu contraseña"
                        required
                    >


                    <button
                        type="button"
                        class="btn-mostrar-password"
                        onclick="mostrarPassword(
                            'confirmar_contrasena',
                            this
                        )"
                    >
                        👁
                    </button>

                </div>

            </div>


            <!-- BOTÓN -->

            <button
                type="submit"
                class="btn-registrar"
            >

                Crear cuenta

            </button>


        </form>


        <!-- =================================
             LOGIN
             ================================= -->

        <div class="registro-login">

            ¿Ya tienes una cuenta?

            <a href="../index.php">

                Iniciar sesión

            </a>

        </div>


        <!-- =================================
             COLORES
             ================================= -->

        <div class="registro-colores">

            <span class="registro-naranja"></span>

            <span class="registro-azul"></span>

            <span class="registro-rojo"></span>

            <span class="registro-amarillo"></span>

        </div>


    </div>


</div>


<script>

function mostrarPassword(
    id,
    boton
) {

    const campo =
        document.getElementById(id);


    if (
        campo.type === "password"
    ) {

        campo.type = "text";

        boton.textContent = "🙈";

    } else {

        campo.type = "password";

        boton.textContent = "👁";

    }

}

</script>


</body>

</html>