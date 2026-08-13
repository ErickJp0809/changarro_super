<?php

session_start();

require_once "../config/conexion.php";


/* =========================================
   VERIFICAR SESIÓN
   ========================================= */

if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Cliente"
) {

    header("Location: ../index.php");

    exit();

}


$usuario_id =
    intval($_SESSION["id"]);


$mensaje = "";

$error = "";


/* =========================================
   OBTENER DATOS DEL CLIENTE
   ========================================= */

$sql_usuario = "

    SELECT
        id,
        nombre,
        usuario,
        correo,
        telefono,
        rol,
        estado,
        fecha_registro

    FROM usuarios

    WHERE id = ?

    LIMIT 1

";


$stmt_usuario =
    $conexion->prepare(
        $sql_usuario
    );


$stmt_usuario->bind_param(
    "i",
    $usuario_id
);


$stmt_usuario->execute();


$resultado_usuario =
    $stmt_usuario->get_result();


if (
    $resultado_usuario->num_rows !== 1
) {

    session_destroy();

    header("Location: ../index.php");

    exit();

}


$cliente =
    $resultado_usuario->fetch_assoc();


/* =========================================
   ACTUALIZAR INFORMACIÓN
   ========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["accion"]) &&
    $_POST["accion"] === "actualizar_datos"
) {


    $nombre =
        trim(
            $_POST["nombre"] ?? ""
        );


    $correo =
        trim(
            $_POST["correo"] ?? ""
        );


    $telefono =
        trim(
            $_POST["telefono"] ?? ""
        );


    /* =====================================
       VALIDAR NOMBRE
       ===================================== */

    if (
        $nombre === ""
    ) {

        $error =
            "El nombre no puede estar vacío.";

    }


    /* =====================================
       VALIDAR CORREO
       ===================================== */

    elseif (
        !filter_var(
            $correo,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            "Ingresa un correo electrónico válido.";

    }


    /* =====================================
       VALIDAR TELÉFONO
       ===================================== */

    if (
        $error === "" &&
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

            $error =
                "El teléfono debe contener entre 10 y 15 números.";

        } else {

            $telefono =
                $telefono_limpio;

        }

    }


    /* =====================================
       COMPROBAR CORREO DUPLICADO
       ===================================== */

    if (
        $error === ""
    ) {


        $sql_correo = "

            SELECT id

            FROM usuarios

            WHERE correo = ?

            AND id <> ?

            LIMIT 1

        ";


        $stmt_correo =
            $conexion->prepare(
                $sql_correo
            );


        $stmt_correo->bind_param(
            "si",
            $correo,
            $usuario_id
        );


        $stmt_correo->execute();


        $resultado_correo =
            $stmt_correo->get_result();


        if (
            $resultado_correo->num_rows > 0
        ) {

            $error =
                "Ese correo ya pertenece a otra cuenta.";

        }

    }


    /* =====================================
       ACTUALIZAR DATOS
       ===================================== */

    if (
        $error === ""
    ) {


        if (
            $telefono === ""
        ) {

            $telefono_db = null;

        } else {

            $telefono_db =
                $telefono;

        }


        $sql_update = "

            UPDATE usuarios

            SET
                nombre = ?,
                correo = ?,
                telefono = ?

            WHERE id = ?

        ";


        $stmt_update =
            $conexion->prepare(
                $sql_update
            );


        $stmt_update->bind_param(
            "sssi",
            $nombre,
            $correo,
            $telefono_db,
            $usuario_id
        );


        if (
            $stmt_update->execute()
        ) {


            /* ACTUALIZAR SESIÓN */

            $_SESSION["nombre"] =
                $nombre;


            $mensaje =
                "✓ Tus datos se actualizaron correctamente.";


            /* ACTUALIZAR DATOS MOSTRADOS */

            $cliente["nombre"] =
                $nombre;


            $cliente["correo"] =
                $correo;


            $cliente["telefono"] =
                $telefono_db;

        } else {

            $error =
                "No se pudieron actualizar tus datos.";

        }

    }

}


/* =========================================
   CAMBIAR CONTRASEÑA
   ========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["accion"]) &&
    $_POST["accion"] === "cambiar_contrasena"
) {


    $contrasena_actual =
        $_POST["contrasena_actual"] ?? "";


    $nueva_contrasena =
        $_POST["nueva_contrasena"] ?? "";


    $confirmar_contrasena =
        $_POST["confirmar_contrasena"] ?? "";


    /* =====================================
       VALIDAR CAMPOS
       ===================================== */

    if (
        $contrasena_actual === "" ||
        $nueva_contrasena === "" ||
        $confirmar_contrasena === ""
    ) {

        $error =
            "Completa todos los campos de contraseña.";

    }


    /* =====================================
       VALIDAR LONGITUD
       ===================================== */

    elseif (
        strlen($nueva_contrasena) < 8
    ) {

        $error =
            "La nueva contraseña debe tener al menos 8 caracteres.";

    }


    /* =====================================
       CONFIRMAR CONTRASEÑA
       ===================================== */

    elseif (
        $nueva_contrasena !==
        $confirmar_contrasena
    ) {

        $error =
            "Las nuevas contraseñas no coinciden.";

    }


    /* =====================================
       COMPROBAR CONTRASEÑA ACTUAL
       ===================================== */

    if (
        $error === ""
    ) {


        $sql_password = "

            SELECT contrasena

            FROM usuarios

            WHERE id = ?

            LIMIT 1

        ";


        $stmt_password =
            $conexion->prepare(
                $sql_password
            );


        $stmt_password->bind_param(
            "i",
            $usuario_id
        );


        $stmt_password->execute();


        $resultado_password =
            $stmt_password->get_result();


        $datos_password =
            $resultado_password->fetch_assoc();


        if (
            !$datos_password ||
            !password_verify(
                $contrasena_actual,
                $datos_password["contrasena"]
            )
        ) {

            $error =
                "La contraseña actual es incorrecta.";

        }

    }


    /* =====================================
       GUARDAR NUEVA CONTRASEÑA
       ===================================== */

    if (
        $error === ""
    ) {


        $nueva_hash =
            password_hash(
                $nueva_contrasena,
                PASSWORD_DEFAULT
            );


        $sql_password_update = "

            UPDATE usuarios

            SET contrasena = ?

            WHERE id = ?

        ";


        $stmt_password_update =
            $conexion->prepare(
                $sql_password_update
            );


        $stmt_password_update->bind_param(
            "si",
            $nueva_hash,
            $usuario_id
        );


        if (
            $stmt_password_update->execute()
        ) {

            $mensaje =
                "✓ Tu contraseña se cambió correctamente.";

        } else {

            $error =
                "No se pudo cambiar la contraseña.";

        }

    }

}


/* =========================================
   INICIAL DEL CLIENTE
   ========================================= */

$inicial =
    strtoupper(
        substr(
            $cliente["nombre"],
            0,
            1
        )
    );

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
        Mi cuenta | Changarro Súper y Más
    </title>


    <link
        rel="stylesheet"
        href="../css/clientes.css"
    >


    <style>

        /* =====================================
           CONTENIDO
           ===================================== */

        .cuenta-contenido {

            max-width: 1050px;

            margin: 0 auto;

        }


        .cuenta-encabezado {

            margin-bottom: 25px;

        }


        .cuenta-encabezado h1 {

            margin: 0 0 7px;

            font-size: 30px;

            color: #222;

        }


        .cuenta-encabezado p {

            margin: 0;

            color: #777;

        }


        /* =====================================
           MENSAJES
           ===================================== */

        .cuenta-mensaje {

            background: #e8f8ef;

            border: 1px solid #b8e5c8;

            color: #16834a;

            border-radius: 9px;

            padding: 12px 15px;

            margin-bottom: 20px;

            font-size: 14px;

        }


        .cuenta-error {

            background: #ffe9e9;

            border: 1px solid #f3b4b4;

            color: #c62828;

            border-radius: 9px;

            padding: 12px 15px;

            margin-bottom: 20px;

            font-size: 14px;

        }


        /* =====================================
           GRID
           ===================================== */

        .cuenta-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 20px;

        }


        .cuenta-card {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 14px;

            padding: 25px;

            box-sizing: border-box;

        }


        .cuenta-card-completa {

            grid-column: 1 / -1;

        }


        .cuenta-card h2 {

            margin: 0 0 20px;

            font-size: 20px;

            color: #222;

        }


        .cuenta-card-subtitulo {

            color: #777;

            font-size: 13px;

            margin-top: -12px;

            margin-bottom: 20px;

        }


        /* =====================================
           PERFIL
           ===================================== */

        .perfil-superior {

            display: flex;

            align-items: center;

            gap: 18px;

            margin-bottom: 25px;

        }


        .perfil-avatar {

            width: 65px;

            height: 65px;

            border-radius: 50%;

            background: #f7941d;

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 25px;

            font-weight: bold;

        }


        .perfil-superior h3 {

            margin: 0 0 5px;

            font-size: 20px;

        }


        .perfil-superior span {

            color: #777;

            font-size: 13px;

        }


        /* =====================================
           DATOS
           ===================================== */

        .dato {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 13px 0;

            border-bottom: 1px solid #eee;

        }


        .dato:last-child {

            border-bottom: none;

        }


        .dato-label {

            color: #777;

            font-size: 13px;

        }


        .dato-valor {

            color: #222;

            font-size: 14px;

            font-weight: 600;

            text-align: right;

        }


        /* =====================================
           CAMPOS
           ===================================== */

        .cuenta-campo {

            margin-bottom: 15px;

        }


        .cuenta-campo label {

            display: block;

            margin-bottom: 7px;

            font-size: 13px;

            font-weight: bold;

            color: #333;

        }


        .cuenta-campo input {

            width: 100%;

            height: 43px;

            padding: 0 13px;

            border: 1px solid #d8d8d8;

            border-radius: 8px;

            box-sizing: border-box;

            outline: none;

        }


        .cuenta-campo input:focus {

            border-color: #f7941d;

        }


        .cuenta-campo small {

            display: block;

            margin-top: 5px;

            color: #888;

            font-size: 11px;

        }


        /* =====================================
           BOTÓN
           ===================================== */

        .btn-guardar {

            width: 100%;

            height: 43px;

            border: none;

            border-radius: 8px;

            background: #f7941d;

            color: white;

            font-weight: bold;

            cursor: pointer;

        }


        .btn-guardar:hover {

            background: #e98212;

        }


        /* =====================================
           ACCIONES
           ===================================== */

        .cuenta-acciones {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

            margin-top: 20px;

        }


        .cuenta-acciones a {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-height: 42px;

            padding: 0 18px;

            border-radius: 8px;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }


        .btn-pedidos {

            background: #249db5;

            color: white;

        }


        .btn-salir {

            background: #e53935;

            color: white;

        }


        /* =====================================
           RESPONSIVE
           ===================================== */

        @media (
            max-width: 750px
        ) {

            .cuenta-grid {

                grid-template-columns: 1fr;

            }


            .cuenta-card-completa {

                grid-column: auto;

            }


            .dato {

                flex-direction: column;

                gap: 5px;

            }


            .dato-valor {

                text-align: left;

            }

        }

    </style>

</head>


<body>


<div class="cliente-layout">


    <!-- =====================================
         SIDEBAR
         ===================================== -->

    <aside class="cliente-sidebar">


        <!-- LOGO -->

        <div class="cliente-marca">

            <img
                src="../img/logo_changarro_transparente.png"
                alt="Changarro Súper y Más"
            >

        </div>


        <!-- MENÚ -->

        <nav class="cliente-menu">


            <a href="../index.php">

                <span>⌂</span>

                Inicio

            </a>


            <a href="productos_clientes.php">

                <span>▣</span>

                Productos

            </a>


            <a href="carrito_clientes.php">

                <span>🛒</span>

                Mi carrito

            </a>


            <a href="pedidos_clientes.php">

                <span>▤</span>

                Mis pedidos

            </a>


            <a
                href="cuenta.php"
                class="activo"
            >

                <span>♙</span>

                Mi cuenta

            </a>


        </nav>


        <!-- CERRAR SESIÓN -->

        <div class="cliente-salir">

            <a href="../logout.php">

                Cerrar sesión

            </a>

        </div>


    </aside>


    <!-- =====================================
         CONTENIDO
         ===================================== -->

    <main class="cliente-contenido">


        <div class="cuenta-contenido">


            <!-- ENCABEZADO -->

            <div class="cuenta-encabezado">

                <h1>

                    Mi cuenta

                </h1>


                <p>

                    Administra tu información
                    personal y seguridad.

                </p>

            </div>


            <!-- =================================
                 MENSAJE
                 ================================= -->

            <?php if (
                $mensaje !== ""
            ): ?>

                <div class="cuenta-mensaje">

                    <?php

                    echo htmlspecialchars(
                        $mensaje
                    );

                    ?>

                </div>

            <?php endif; ?>


            <?php if (
                $error !== ""
            ): ?>

                <div class="cuenta-error">

                    <?php

                    echo htmlspecialchars(
                        $error
                    );

                    ?>

                </div>

            <?php endif; ?>


            <!-- =================================
                 TARJETAS
                 ================================= -->

            <div class="cuenta-grid">


                <!-- =================================
                     INFORMACIÓN PERSONAL
                     ================================= -->

                <section class="cuenta-card">


                    <div class="perfil-superior">


                        <div class="perfil-avatar">

                            <?php

                            echo htmlspecialchars(
                                $inicial
                            );

                            ?>

                        </div>


                        <div>

                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $cliente["nombre"]
                                );

                                ?>

                            </h3>


                            <span>

                                @<?php

                                echo htmlspecialchars(
                                    $cliente["usuario"]
                                );

                                ?>

                            </span>

                        </div>


                    </div>


                    <h2>

                        Información personal

                    </h2>


                    <div class="dato">

                        <span class="dato-label">

                            Nombre

                        </span>


                        <span class="dato-valor">

                            <?php

                            echo htmlspecialchars(
                                $cliente["nombre"]
                            );

                            ?>

                        </span>

                    </div>


                    <div class="dato">

                        <span class="dato-label">

                            Usuario

                        </span>


                        <span class="dato-valor">

                            <?php

                            echo htmlspecialchars(
                                $cliente["usuario"]
                            );

                            ?>

                        </span>

                    </div>


                    <div class="dato">

                        <span class="dato-label">

                            Correo

                        </span>


                        <span class="dato-valor">

                            <?php

                            echo htmlspecialchars(
                                $cliente["correo"]
                            );

                            ?>

                        </span>

                    </div>


                    <div class="dato">

                        <span class="dato-label">

                            Teléfono

                        </span>


                        <span class="dato-valor">

                            <?php

                            echo (
                                !empty(
                                    $cliente["telefono"]
                                )
                            )
                            ?
                            htmlspecialchars(
                                $cliente["telefono"]
                            )
                            :
                            "No registrado";

                            ?>

                        </span>

                    </div>


                    <div class="dato">

                        <span class="dato-label">

                            Estado

                        </span>


                        <span class="dato-valor">

                            <?php

                            echo htmlspecialchars(
                                $cliente["estado"]
                            );

                            ?>

                        </span>

                    </div>


                </section>


                <!-- =================================
                     EDITAR INFORMACIÓN
                     ================================= -->

                <section class="cuenta-card">


                    <h2>

                        Editar información

                    </h2>


                    <p class="cuenta-card-subtitulo">

                        Actualiza tus datos personales.

                    </p>


                    <form
                        method="POST"
                        action="cuenta.php"
                    >


                        <input
                            type="hidden"
                            name="accion"
                            value="actualizar_datos"
                        >


                        <div class="cuenta-campo">

                            <label for="nombre">

                                Nombre

                            </label>


                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                value="<?php

                                    echo htmlspecialchars(
                                        $cliente["nombre"]
                                    );

                                ?>"
                                maxlength="100"
                                required
                            >

                        </div>


                        <div class="cuenta-campo">

                            <label for="correo">

                                Correo electrónico

                            </label>


                            <input
                                type="email"
                                id="correo"
                                name="correo"
                                value="<?php

                                    echo htmlspecialchars(
                                        $cliente["correo"]
                                    );

                                ?>"
                                maxlength="150"
                                required
                            >

                        </div>


                        <div class="cuenta-campo">

                            <label for="telefono">

                                Teléfono

                            </label>


                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                value="<?php

                                    echo htmlspecialchars(
                                        $cliente["telefono"] ?? ""
                                    );

                                ?>"
                                maxlength="15"
                            >


                            <small>

                                Puedes dejarlo vacío.

                            </small>

                        </div>


                        <button
                            type="submit"
                            class="btn-guardar"
                        >

                            Guardar cambios

                        </button>


                    </form>


                </section>


                <!-- =================================
                     CAMBIAR CONTRASEÑA
                     ================================= -->

                <section class="cuenta-card">


                    <h2>

                        🔒 Cambiar contraseña

                    </h2>


                    <p class="cuenta-card-subtitulo">

                        Cambia tu contraseña de acceso.

                    </p>


                    <form
                        method="POST"
                        action="cuenta.php"
                    >


                        <input
                            type="hidden"
                            name="accion"
                            value="cambiar_contrasena"
                        >


                        <div class="cuenta-campo">

                            <label
                                for="contrasena_actual"
                            >

                                Contraseña actual

                            </label>


                            <input
                                type="password"
                                id="contrasena_actual"
                                name="contrasena_actual"
                                autocomplete="current-password"
                                required
                            >

                        </div>


                        <div class="cuenta-campo">

                            <label
                                for="nueva_contrasena"
                            >

                                Nueva contraseña

                            </label>


                            <input
                                type="password"
                                id="nueva_contrasena"
                                name="nueva_contrasena"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >

                        </div>


                        <div class="cuenta-campo">

                            <label
                                for="confirmar_contrasena"
                            >

                                Confirmar nueva contraseña

                            </label>


                            <input
                                type="password"
                                id="confirmar_contrasena"
                                name="confirmar_contrasena"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn-guardar"
                        >

                            Cambiar contraseña

                        </button>


                    </form>


                </section>


                <!-- =================================
                     ACCIONES
                     ================================= -->

                <section
                    class="cuenta-card cuenta-card-completa"
                >


                    <h2>

                        Accesos rápidos

                    </h2>


                    <div class="cuenta-acciones">


                        <a
                            href="pedidos_clientes.php"
                            class="btn-pedidos"
                        >

                            📦 Mis pedidos

                        </a>


                        <a
                            href="../logout.php"
                            class="btn-salir"
                        >

                            Cerrar sesión

                        </a>


                    </div>


                </section>


            </div>


        </div>


    </main>


</div>


</body>

</html>