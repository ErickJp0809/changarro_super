<?php

session_start();

require_once "config/conexion.php";


/* =========================================
   COMPROBAR REGISTRO PENDIENTE
   ========================================= */

if (
    !isset($_SESSION["verificacion_id"]) ||
    !isset($_SESSION["verificacion_correo"])
) {

    header("Location: registro.php");
    exit();

}


$usuario_id =
    intval($_SESSION["verificacion_id"]);

$correo =
    $_SESSION["verificacion_correo"];


$mensaje = "";

$error = "";


/* =========================================
   PROCESAR CÓDIGO
   ========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $codigo =
        trim($_POST["codigo"] ?? "");


    /* =====================================
       VALIDAR FORMATO
       ===================================== */

    if (!preg_match(
        '/^[0-9]{6}$/',
        $codigo
    )) {

        $error =
            "Ingresa un código de 6 dígitos.";

    } else {


        /* =================================
           BUSCAR CÓDIGO
           ================================= */

        $sql = "
            SELECT
                id,
                correo_verificado,
                codigo_verificacion,
                codigo_expira
            FROM usuarios
            WHERE id = ?
            AND correo = ?
            LIMIT 1
        ";


        $stmt =
            $conexion->prepare($sql);


        $stmt->bind_param(
            "is",
            $usuario_id,
            $correo
        );


        $stmt->execute();


        $resultado =
            $stmt->get_result();


        if (
            $resultado->num_rows !== 1
        ) {

            $error =
                "No se encontró la cuenta.";

        } else {


            $usuario =
                $resultado->fetch_assoc();


            /* ==============================
               YA VERIFICADO
               ============================== */

            if (
                intval(
                    $usuario["correo_verificado"]
                ) === 1
            ) {

                $mensaje =
                    "Tu correo ya está verificado.";

            }


            /* ==============================
               COMPROBAR EXPIRACIÓN
               ============================== */

            elseif (
                empty(
                    $usuario["codigo_expira"]
                ) ||
                strtotime(
                    $usuario["codigo_expira"]
                ) < time()
            ) {

                $error =
                    "El código ha expirado. Solicita uno nuevo.";

            }


            /* ==============================
               COMPROBAR CÓDIGO
               ============================== */

            elseif (
                !hash_equals(
                    (string)
                    $usuario[
                        "codigo_verificacion"
                    ],
                    $codigo
                )
            ) {

                $error =
                    "El código de verificación es incorrecto.";

            }


            /* ==============================
               VERIFICAR CUENTA
               ============================== */

            else {


                $sql_update = "
                    UPDATE usuarios
                    SET
                        correo_verificado = 1,
                        codigo_verificacion = NULL,
                        codigo_expira = NULL
                    WHERE id = ?
                ";


                $stmt_update =
                    $conexion->prepare(
                        $sql_update
                    );


                $stmt_update->bind_param(
                    "i",
                    $usuario_id
                );


                if (
                    $stmt_update->execute()
                ) {


                    /*
                     * ELIMINAMOS LA SESIÓN
                     * TEMPORAL DE VERIFICACIÓN
                     */

                    unset(
                        $_SESSION[
                            "verificacion_id"
                        ]
                    );

                    unset(
                        $_SESSION[
                            "verificacion_correo"
                        ]
                    );


                    header(
                        "Location: index.php?registro=verificado"
                    );

                    exit();

                } else {

                    $error =
                        "No se pudo verificar la cuenta.";

                }

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
        Verificar correo | Changarro Súper y Más
    </title>


    <link
        rel="stylesheet"
        href="css/estilos.css"
    >


    <style>

        .verificacion-descripcion {

            text-align: center;

            color: #777;

            font-size: 14px;

            line-height: 1.5;

            margin-bottom: 25px;

        }


        .correo-verificacion {

            color: #222;

            font-weight: bold;

        }


        .codigo-verificacion {

            text-align: center;

            font-size: 25px;

            letter-spacing: 8px;

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


        .volver-login {

            display: block;

            text-align: center;

            margin-top: 18px;

            color: #777;

            text-decoration: none;

            font-size: 13px;

        }


        .volver-login:hover {

            color: #e53935;

        }

    </style>

</head>


<body>


<div class="login-container">


    <div class="login-box">


        <!-- LOGO -->

        <div class="login-logo">

            <img
                src="img/logo_changarro_transparente.png"
                alt="El Changarro"
            >

        </div>


        <h1>

            Verifica tu correo

        </h1>


        <p class="verificacion-descripcion">

            Ingresa el código de 6 dígitos
            que enviamos a

            <br>

            <span class="correo-verificacion">

                <?php

                echo htmlspecialchars(
                    $correo
                );

                ?>

            </span>

        </p>


        <!-- ERROR -->

        <?php if ($error !== ""): ?>

            <div class="mensaje-error">

                <?php

                echo htmlspecialchars(
                    $error
                );

                ?>

            </div>

        <?php endif; ?>


        <!-- FORMULARIO -->

        <form
            action="verificar_correo.php"
            method="POST"
        >


            <div class="campo">

                <label for="codigo">

                    Código de verificación

                </label>


                <input
                    type="text"
                    id="codigo"
                    name="codigo"
                    class="codigo-verificacion"
                    placeholder="000000"
                    maxlength="6"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    required
                >

            </div>


            <button type="submit">

                Verificar correo

            </button>


        </form>


        <a
            href="index.php"
            class="volver-login"
        >

            ← Volver al inicio de sesión

        </a>


        <!-- COLORES -->

        <div class="colores-login">

            <span class="color-naranja"></span>

            <span class="color-azul"></span>

            <span class="color-rojo"></span>

            <span class="color-amarillo"></span>

        </div>


    </div>

</div>


</body>

</html>