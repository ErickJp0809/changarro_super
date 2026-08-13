<?php

session_start();

require_once "config/conexion.php";


/* =========================================
   VARIABLES
   ========================================= */

$errores = [];

$nombre = "";
$usuario = "";
$correo = "";
$telefono = "";


/* =========================================
   PROCESAR REGISTRO
   ========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    $nombre =
        trim(
            $_POST["nombre"] ?? ""
        );


    $usuario =
        trim(
            $_POST["usuario"] ?? ""
        );


    $correo =
        trim(
            $_POST["correo"] ?? ""
        );


    $telefono =
        trim(
            $_POST["telefono"] ?? ""
        );


    $contrasena =
        $_POST["contrasena"] ?? "";


    $confirmar =
        $_POST["confirmar_contrasena"] ?? "";


    /* =====================================
       VALIDAR NOMBRE
       ===================================== */

    if (
        $nombre === ""
    ) {

        $errores[] =
            "Ingresa tu nombre.";

    }


    /* =====================================
       VALIDAR USUARIO
       ===================================== */

    if (
        $usuario === ""
    ) {

        $errores[] =
            "Ingresa un nombre de usuario.";

    }

    elseif (
        !preg_match(
            '/^[a-zA-Z0-9._-]{3,50}$/',
            $usuario
        )
    ) {

        $errores[] =
            "El nombre de usuario debe tener entre 3 y 50 caracteres y solo puede contener letras, números, punto, guion y guion bajo.";

    }


    /* =====================================
       VALIDAR CORREO
       ===================================== */

    if (
        $correo === ""
    ) {

        $errores[] =
            "Ingresa tu correo electrónico.";

    }

    elseif (
        !filter_var(
            $correo,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $errores[] =
            "Ingresa un correo electrónico válido.";

    }


    /* =====================================
       TELÉFONO OPCIONAL
       ===================================== */

    if (
        $telefono !== ""
    ) {


        $telefono_limpio =
            preg_replace(
                '/[\s\-\(\)]/',
                "",
                $telefono
            );


        if (
            !preg_match(
                '/^[0-9]{10,15}$/',
                $telefono_limpio
            )
        ) {

            $errores[] =
                "El teléfono debe contener entre 10 y 15 números.";

        }

        else {

            $telefono =
                $telefono_limpio;

        }

    }

    else {

        $telefono = null;

    }


    /* =====================================
       VALIDAR CONTRASEÑA
       ===================================== */

    if (
        $contrasena === ""
    ) {

        $errores[] =
            "Ingresa una contraseña.";

    }

    elseif (
        strlen($contrasena) < 8
    ) {

        $errores[] =
            "La contraseña debe tener al menos 8 caracteres.";

    }


    /* =====================================
       CONFIRMAR CONTRASEÑA
       ===================================== */

    if (
        $confirmar === ""
    ) {

        $errores[] =
            "Confirma tu contraseña.";

    }

    elseif (
        $contrasena !== $confirmar
    ) {

        $errores[] =
            "Las contraseñas no coinciden.";

    }


    /* =====================================
       COMPROBAR USUARIO
       ===================================== */

    if (
        empty($errores)
    ) {


        $sql = "

            SELECT id

            FROM usuarios

            WHERE usuario = ?

            LIMIT 1

        ";


        $stmt =
            $conexion->prepare(
                $sql
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

            $errores[] =
                "Ese nombre de usuario ya está registrado.";

        }

    }


    /* =====================================
       COMPROBAR CORREO
       ===================================== */

    if (
        empty($errores)
    ) {


        $sql = "

            SELECT id

            FROM usuarios

            WHERE correo = ?

            LIMIT 1

        ";


        $stmt =
            $conexion->prepare(
                $sql
            );


        $stmt->bind_param(
            "s",
            $correo
        );


        $stmt->execute();


        $resultado =
            $stmt->get_result();


        if (
            $resultado->num_rows > 0
        ) {

            $errores[] =
                "Ese correo electrónico ya está registrado.";

        }

    }


    /* =====================================
       COMPROBAR TELÉFONO
       ===================================== */

    if (
        empty($errores) &&
        $telefono !== null
    ) {


        $sql = "

            SELECT id

            FROM usuarios

            WHERE telefono = ?

            LIMIT 1

        ";


        $stmt =
            $conexion->prepare(
                $sql
            );


        $stmt->bind_param(
            "s",
            $telefono
        );


        $stmt->execute();


        $resultado =
            $stmt->get_result();


        if (
            $resultado->num_rows > 0
        ) {

            $errores[] =
                "Ese número de teléfono ya está registrado.";

        }

    }


    /* =====================================
       CREAR CUENTA
       ===================================== */

    if (
        empty($errores)
    ) {


        /* =================================
           CONTRASEÑA SEGURA
           ================================= */

        $contrasena_hash =
            password_hash(
                $contrasena,
                PASSWORD_DEFAULT
            );


        /* =================================
           INSERTAR USUARIO
           
           YA NO SE GENERA CÓDIGO
           DE VERIFICACIÓN.
           ================================= */

        $sql = "

            INSERT INTO usuarios (

                nombre,
                usuario,
                correo,
                telefono,
                contrasena,
                rol,
                estado

            )

            VALUES (

                ?,
                ?,
                ?,
                ?,
                ?,
                'Cliente',
                'Activo'

            )

        ";


        $stmt =
            $conexion->prepare(
                $sql
            );


        if (
            !$stmt
        ) {

            $errores[] =
                "No se pudo preparar el registro.";

        }

        else {


            $stmt->bind_param(
                "sssss",
                $nombre,
                $usuario,
                $correo,
                $telefono,
                $contrasena_hash
            );


            if (
                $stmt->execute()
            ) {


                /* =================================
                   INICIAR SESIÓN AUTOMÁTICAMENTE
                   ================================= */

                $nuevo_usuario_id =
                    $conexion->insert_id;


                /*
                 * Guardamos los datos necesarios
                 * para que el cliente quede
                 * autenticado inmediatamente.
                 */

                $_SESSION["id"] =
                    $nuevo_usuario_id;

                $_SESSION["usuario"] =
                    $usuario;

                $_SESSION["nombre"] =
                    $nombre;

                $_SESSION["correo"] =
                    $correo;

                $_SESSION["rol"] =
                    "Cliente";

                $_SESSION["estado"] =
                    "Activo";


                /* =================================
                   REGISTRO + LOGIN EXITOSOS
                   ================================= */

                header(
                    "Location: clientes/dashboard_clientes.php?registro=exitoso"
                );

                exit();


            }

            else {

                $errores[] =
                    "No se pudo crear la cuenta. Intenta nuevamente.";

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
        href="css/estilos.css"
    >


    <style>

        .registro-box {

            width: 100%;

            max-width: 470px;

        }


        .registro-descripcion {

            text-align: center;

            color: #777;

            font-size: 14px;

            margin-bottom: 25px;

        }


        .mensaje-error {

            background: #ffe9e9;

            border: 1px solid #f3b4b4;

            color: #c62828;

            border-radius: 8px;

            padding: 12px;

            margin-bottom: 18px;

            font-size: 13px;

        }


        .mensaje-error ul {

            margin: 0;

            padding-left: 18px;

        }


        .campo small {

            display: block;

            margin-top: 5px;

            color: #888;

            font-size: 11px;

        }


        /* =====================================
           OJITOS DE CONTRASEÑA
           ===================================== */

        .campo-password {

            position: relative;

        }


        .campo-password input {

            padding-right: 48px !important;

        }


        .campo-password .btn-ver-password {

            position: absolute !important;

            right: 10px !important;

            bottom: 7px !important;

            width: 34px !important;

            height: 34px !important;

            min-width: 34px !important;

            max-width: 34px !important;

            padding: 0 !important;

            margin: 0 !important;

            border: none !important;

            border-radius: 50% !important;

            background: transparent !important;

            color: #777 !important;

            cursor: pointer;

            font-size: 16px !important;

            line-height: 34px !important;

            display: flex !important;

            align-items: center !important;

            justify-content: center !important;

            box-shadow: none !important;

        }


        .campo-password .btn-ver-password:hover {

            background: #f5f5f5 !important;

            color: #222 !important;

        }


        .registro-enlaces {

            text-align: center;

            margin-top: 20px;

            padding-top: 18px;

            border-top: 1px solid #eee;

            color: #666;

            font-size: 14px;

        }


        .registro-enlaces a {

            color: #e53935;

            font-weight: bold;

            text-decoration: none;

        }


        .registro-enlaces a:hover {

            text-decoration: underline;

        }

    </style>

</head>


<body>


<div class="login-container">


    <div class="login-box registro-box">


        <!-- LOGO -->

        <div class="login-logo">

            <img
                src="img/logo_changarro_transparente.png"
                alt="El Changarro"
            >

        </div>


        <h1>

            Crear cuenta

        </h1>


        <p class="registro-descripcion">

            Regístrate para realizar pedidos
            en Changarro Súper y Más.

        </p>


        <!-- =====================================
             ERRORES
             ===================================== -->

        <?php if (
            !empty($errores)
        ): ?>

            <div class="mensaje-error">

                <ul>

                    <?php foreach (
                        $errores
                        as $error
                    ): ?>

                        <li>

                            <?php

                            echo htmlspecialchars(
                                $error
                            );

                            ?>

                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <!-- =====================================
             FORMULARIO
             ===================================== -->

        <form
            method="POST"
            action="registro.php"
        >


            <!-- NOMBRE -->

            <div class="campo">

                <label
                    for="nombre"
                >

                    Nombre

                </label>


                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="<?php

                        echo htmlspecialchars(
                            $nombre
                        );

                    ?>"
                    placeholder="Ej. Erick Juarez"
                    maxlength="100"
                    autocomplete="name"
                    required
                >

            </div>


            <!-- USUARIO -->

            <div class="campo">

                <label
                    for="usuario"
                >

                    Nombre de usuario

                </label>


                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    value="<?php

                        echo htmlspecialchars(
                            $usuario
                        );

                    ?>"
                    placeholder="Ej. Erick"
                    maxlength="50"
                    autocomplete="username"
                    required
                >

            </div>


            <!-- CORREO -->

            <div class="campo">

                <label
                    for="correo"
                >

                    Correo electrónico

                </label>


                <input
                    type="email"
                    id="correo"
                    name="correo"
                    value="<?php

                        echo htmlspecialchars(
                            $correo
                        );

                    ?>"
                    placeholder="Ej. erick@gmail.com"
                    maxlength="150"
                    autocomplete="email"
                    required
                >

            </div>


            <!-- TELÉFONO -->

            <div class="campo">

                <label
                    for="telefono"
                >

                    Número de teléfono

                </label>


                <input
                    type="tel"
                    id="telefono"
                    name="telefono"
                    value="<?php

                        echo htmlspecialchars(
                            $telefono ?? ""
                        );

                    ?>"
                    placeholder="Ej. 8991234567"
                    maxlength="15"
                    autocomplete="tel"
                >


                <small>

                    Opcional. También podrás usarlo
                    para iniciar sesión.

                </small>

            </div>


            <!-- CONTRASEÑA -->

            <div class="campo campo-password">

                <label
                    for="contrasena"
                >

                    Contraseña

                </label>


                <input
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    placeholder="Mínimo 8 caracteres"
                    autocomplete="new-password"
                    required
                >


                <button
                    type="button"
                    class="btn-ver-password"
                    onclick="mostrarContrasena('contrasena', this)"
                    aria-label="Mostrar contraseña"
                    title="Mostrar contraseña"
                >

                    👁

                </button>

            </div>


            <!-- CONFIRMAR -->

            <div class="campo campo-password">

                <label
                    for="confirmar_contrasena"
                >

                    Confirmar contraseña

                </label>


                <input
                    type="password"
                    id="confirmar_contrasena"
                    name="confirmar_contrasena"
                    placeholder="Repite tu contraseña"
                    autocomplete="new-password"
                    required
                >


                <button
                    type="button"
                    class="btn-ver-password"
                    onclick="mostrarContrasena('confirmar_contrasena', this)"
                    aria-label="Mostrar contraseña"
                    title="Mostrar contraseña"
                >

                    👁

                </button>

            </div>


            <!-- BOTÓN -->

            <button
                type="submit"
            >

                Crear cuenta

            </button>


        </form>


        <!-- =====================================
             VOLVER
             ===================================== -->

        <div
            class="registro-enlaces"
        >

            ¿Ya tienes una cuenta?


            <a
                href="index.php"
            >

                Iniciar sesión

            </a>

        </div>


        <!-- =====================================
             COLORES
             ===================================== -->

        <div
            class="colores-login"
        >

            <span
                class="color-naranja"
            ></span>


            <span
                class="color-azul"
            ></span>


            <span
                class="color-rojo"
            ></span>


            <span
                class="color-amarillo"
            ></span>

        </div>


    </div>


</div>



<script>

function mostrarContrasena(id, boton) {

    const campo = document.getElementById(id);

    if (campo.type === "password") {

        campo.type = "text";

        boton.textContent = "🙈";

        boton.setAttribute(
            "aria-label",
            "Ocultar contraseña"
        );

        boton.setAttribute(
            "title",
            "Ocultar contraseña"
        );

    } else {

        campo.type = "password";

        boton.textContent = "👁";

        boton.setAttribute(
            "aria-label",
            "Mostrar contraseña"
        );

        boton.setAttribute(
            "title",
            "Mostrar contraseña"
        );

    }

}

</script>

</body>

</html>