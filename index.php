<?php

session_start();

require_once "config/conexion.php";


/* =========================================
   VERIFICAR SI HAY CLIENTE LOGUEADO
   ========================================= */

$cliente_logueado = (

    isset($_SESSION["id"]) &&

    isset($_SESSION["rol"]) &&

    $_SESSION["rol"] === "Cliente"

);


/* =========================================
   MENSAJES DEL LOGIN
   ========================================= */

$mensaje = "";


if (isset($_GET["error"])) {


    if (
        $_GET["error"] === "desactivado"
    ) {

        $mensaje =
            "⚠️ Este usuario está desactivado. Contacta al administrador.";

    }


    elseif (
        $_GET["error"] === "incorrecta"
    ) {

        $mensaje =
            "❌ Correo, usuario, teléfono o contraseña incorrectos.";

    }


    elseif (
        $_GET["error"] === "1"
    ) {

        $mensaje =
            "❌ Correo, usuario, teléfono o contraseña incorrectos.";

    }

}


/* =========================================
   BUSCADOR
   ========================================= */

$busqueda = "";


if (
    isset($_GET["buscar"])
) {

    $busqueda =
        trim(
            $_GET["buscar"]
        );

}


/* =========================================
   OBTENER PRODUCTOS
   ========================================= */

if (
    $busqueda !== ""
) {


    $busqueda_sql =
        "%" . $busqueda . "%";


    $sql_productos = "

        SELECT

            id,
            codigo,
            nombre,
            categoria,
            precio_venta,
            stock

        FROM productos

        WHERE activo = 1

        AND stock > 0

        AND (

            nombre LIKE ?

            OR categoria LIKE ?

            OR codigo LIKE ?

        )

        ORDER BY nombre ASC

    ";


    $stmt_productos =
        $conexion->prepare(
            $sql_productos
        );


    $stmt_productos->bind_param(
        "sss",
        $busqueda_sql,
        $busqueda_sql,
        $busqueda_sql
    );


    $stmt_productos->execute();


    $productos =
        $stmt_productos->get_result();


} else {


    $sql_productos = "

        SELECT

            id,
            codigo,
            nombre,
            categoria,
            precio_venta,
            stock

        FROM productos

        WHERE activo = 1

        AND stock > 0

        ORDER BY nombre ASC

    ";


    $productos =
        $conexion->query(
            $sql_productos
        );

}


/* =========================================
   CATEGORÍAS
   ========================================= */

$sql_categorias = "

    SELECT DISTINCT
        categoria

    FROM productos

    WHERE activo = 1

    AND stock > 0

    ORDER BY categoria ASC

";


$categorias =
    $conexion->query(
        $sql_categorias
    );


/* =========================================
   CARRITO
   ========================================= */

$cantidad_carrito = 0;


if (
    isset($_SESSION["carrito"]) &&
    is_array($_SESSION["carrito"])
) {


    foreach (
        $_SESSION["carrito"] as $item
    ) {

        $cantidad_carrito +=
            intval(
                $item["cantidad"]
            );

    }

}


/* =========================================
   INICIAL DEL CLIENTE
   ========================================= */

$inicial_cliente = "";


if (
    $cliente_logueado
) {

    $inicial_cliente =
        strtoupper(
            substr(
                $_SESSION["nombre"],
                0,
                1
            )
        );

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

        Changarro Súper y Más

    </title>


    <link
        rel="stylesheet"
        href="css/clientes.css"
    >


    <style>


        /* =====================================
           CABECERA PÚBLICA
           ===================================== */

        .publico-header {

            background: white;

            border-bottom: 1px solid #e8e8e8;

            padding: 15px 30px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            position: sticky;

            top: 0;

            z-index: 100;

        }


        .publico-logo {

            display: flex;

            align-items: center;

            gap: 12px;

        }


        .publico-logo img {

            width: 55px;

            height: 55px;

            object-fit: contain;

        }


        .publico-logo strong {

            font-size: 20px;

            color: #222;

        }


        .publico-nav {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .publico-nav a,
        .publico-nav button {

            border: none;

            background: transparent;

            padding: 10px 14px;

            border-radius: 8px;

            color: #555;

            text-decoration: none;

            font-size: 14px;

            cursor: pointer;

        }


        .publico-nav a:hover,
        .publico-nav button:hover {

            background: #f5f5f5;

        }


        .btn-login-header {

            background: #f7941d !important;

            color: white !important;

            font-weight: bold;

        }


        .btn-login-header:hover {

            background: #e98212 !important;

        }


        .btn-cuenta-header {

            background: #249db5 !important;

            color: white !important;

            font-weight: bold;

        }


        /* =====================================
           CONTENIDO
           ===================================== */

        .tienda-publica {

            min-height: calc(
                100vh - 86px
            );

            padding: 35px;

            background: #f5f6f8;

        }


        .tienda-contenido {

            max-width: 1250px;

            margin: 0 auto;

        }


        .tienda-bienvenida {

            margin-bottom: 25px;

        }


        .tienda-bienvenida h1 {

            margin: 0 0 8px;

            font-size: 32px;

            color: #222;

        }


        .tienda-bienvenida p {

            margin: 0;

            color: #777;

        }


        /* =====================================
           BUSCADOR
           ===================================== */

        .buscador-productos {

            display: flex;

            gap: 10px;

            margin-bottom: 18px;

        }


        .buscador-productos input {

            flex: 1;

            height: 45px;

            padding: 0 15px;

            border: 1px solid #ddd;

            border-radius: 8px;

            background: white;

            outline: none;

            box-sizing: border-box;

        }


        .buscador-productos button {

            height: 45px;

            padding: 0 20px;

            border: none;

            border-radius: 8px;

            background: #f7941d;

            color: white;

            font-weight: bold;

            cursor: pointer;

        }


        .buscador-productos button:hover {

            background: #e98212;

        }


        .limpiar-busqueda {

            display: flex;

            align-items: center;

            padding: 0 10px;

            color: #249db5;

            text-decoration: none;

        }


        /* =====================================
           CATEGORÍAS
           ===================================== */

        .categorias {

            display: flex;

            flex-wrap: wrap;

            gap: 8px;

            margin-bottom: 25px;

        }


        .categorias a {

            padding: 8px 14px;

            border-radius: 20px;

            background: white;

            border: 1px solid #e5e5e5;

            color: #555;

            text-decoration: none;

            font-size: 13px;

        }


        .categorias a:hover {

            background: #f7941d;

            color: white;

            border-color: #f7941d;

        }


        /* =====================================
           GRID PRODUCTOS
           ===================================== */

        .catalogo-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(
                        220px,
                        1fr
                    )
                );

            gap: 18px;

        }


        .catalogo-producto {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 14px;

            padding: 18px;

            display: flex;

            flex-direction: column;

            min-height: 250px;

            box-sizing: border-box;

        }


        .catalogo-producto-icono {

            height: 80px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 45px;

            margin-bottom: 10px;

        }


        .catalogo-producto-info {

            flex: 1;

        }


        .catalogo-categoria {

            display: inline-block;

            margin-bottom: 7px;

            font-size: 11px;

            color: #249db5;

            font-weight: bold;

            text-transform: uppercase;

        }


        .catalogo-producto h3 {

            margin: 0 0 7px;

            font-size: 17px;

            color: #222;

        }


        .catalogo-codigo {

            margin: 0 0 8px;

            color: #999;

            font-size: 12px;

        }


        .catalogo-stock {

            color: #16834a;

            font-size: 12px;

            font-weight: 600;

        }


        .catalogo-producto-footer {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            margin-top: 15px;

        }


        .catalogo-producto-footer strong {

            font-size: 20px;

            color: #222;

        }


        .btn-agregar-carrito {

            border: none;

            border-radius: 8px;

            padding: 9px 12px;

            background: #f7941d;

            color: white;

            font-size: 12px;

            font-weight: bold;

            cursor: pointer;

        }


        .btn-agregar-carrito:hover {

            background: #e98212;

        }


        /* =====================================
           MODAL LOGIN
           ===================================== */

        .modal-login {

            display: none;

            position: fixed;

            inset: 0;

            z-index: 9999;

            background:
                rgba(
                    0,
                    0,
                    0,
                    .58
                );

            align-items: center;

            justify-content: center;

            padding: 20px;

            box-sizing: border-box;

        }


        .modal-login.mostrar {

            display: flex;

        }


        .modal-login-box {

            position: relative;

            width: 100%;

            max-width: 430px;

            background: white;

            border-radius: 18px;

            padding: 32px;

            box-sizing: border-box;

            box-shadow:
                0 20px 60px
                rgba(
                    0,
                    0,
                    0,
                    .25
                );

        }


        .modal-cerrar {

            position: absolute;

            top: 12px;

            right: 15px;

            width: 35px;

            height: 35px;

            border: none;

            border-radius: 50%;

            background: #f2f2f2;

            color: #555;

            font-size: 23px;

            cursor: pointer;

        }


        .modal-cerrar:hover {

            background: #e5e5e5;

        }


        .modal-logo {

            text-align: center;

            margin-bottom: 10px;

        }


        .modal-logo img {

            width: 80px;

            height: 80px;

            object-fit: contain;

        }


        .modal-login-box h2 {

            margin: 5px 0 8px;

            text-align: center;

            font-size: 25px;

            color: #222;

        }


        .modal-subtitulo {

            text-align: center;

            color: #777;

            font-size: 13px;

            margin-bottom: 20px;

        }


        .modal-campo {

            margin-bottom: 15px;

        }


        .modal-campo label {

            display: block;

            margin-bottom: 7px;

            font-size: 13px;

            font-weight: bold;

            color: #333;

        }


        .modal-campo input {

            width: 100%;

            height: 44px;

            padding: 0 13px;

            border: 1px solid #d8d8d8;

            border-radius: 8px;

            box-sizing: border-box;

            outline: none;

        }


        .modal-campo input:focus {

            border-color: #f7941d;

        }


        .modal-login-submit {

            width: 100%;

            height: 45px;

            border: none;

            border-radius: 8px;

            background: #f7941d;

            color: white;

            font-size: 14px;

            font-weight: bold;

            cursor: pointer;

            margin-top: 5px;

        }


        .modal-login-submit:hover {

            background: #e98212;

        }


        .modal-error {

            padding: 10px;

            margin-bottom: 15px;

            border-radius: 8px;

            background: #ffe9e9;

            color: #d52f2f;

            font-size: 13px;

            text-align: center;

        }


        .modal-enlaces {

            text-align: center;

            margin-top: 18px;

        }


        .modal-enlaces .olvido {

            display: inline-block;

            color: #777;

            text-decoration: none;

            font-size: 13px;

            margin-bottom: 17px;

        }


        .modal-enlaces .olvido:hover {

            color: #e53935;

            text-decoration: underline;

        }


        .modal-crear {

            padding-top: 17px;

            border-top: 1px solid #eeeeee;

            color: #666;

            font-size: 14px;

        }


        .modal-crear a {

            color: #e53935;

            font-weight: bold;

            text-decoration: none;

            margin-left: 4px;

        }


        .modal-crear a:hover {

            text-decoration: underline;

        }


        /* =====================================
           CARRITO
           ===================================== */

        .contador-carrito {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 19px;

            height: 19px;

            margin-left: 4px;

            padding: 0 5px;

            border-radius: 10px;

            background: #f7941d;

            color: white;

            font-size: 10px;

            font-weight: bold;

        }


        /* =====================================
           RESPONSIVE
           ===================================== */

        @media (
            max-width: 700px
        ) {


            .publico-header {

                padding: 12px 15px;

            }


            .publico-logo strong {

                display: none;

            }


            .publico-nav {

                gap: 3px;

            }


            .publico-nav a,
            .publico-nav button {

                padding: 8px;

                font-size: 12px;

            }


            .tienda-publica {

                padding: 20px 15px;

            }


            .tienda-bienvenida h1 {

                font-size: 25px;

            }


            .catalogo-grid {

                grid-template-columns:
                    repeat(
                        2,
                        1fr
                    );

            }


            .modal-login-box {

                padding: 25px 20px;

            }

        }


    </style>


</head>


<body>


<!-- =========================================
     HEADER
     ========================================= -->

<header class="publico-header">


    <!-- LOGO -->

    <div class="publico-logo">


        <img
            src="img/logo_changarro_transparente.png"
            alt="Changarro Súper y Más"
        >


        <strong>

            Changarro Súper y Más

        </strong>


    </div>


    <!-- NAVEGACIÓN -->

    <nav class="publico-nav">


        <a href="#productos">

            Productos

        </a>


        <a href="#categorias">

            Categorías

        </a>


        <?php if (
            $cliente_logueado
        ): ?>


            <a
                href="clientes/carrito_clientes.php"
            >

                🛒 Carrito


                <?php if (
                    $cantidad_carrito > 0
                ): ?>

                    <span
                        class="contador-carrito"
                    >

                        <?php

                        echo $cantidad_carrito;

                        ?>

                    </span>

                <?php endif; ?>


            </a>


            <a
                href="clientes/pedidos_clientes.php"
            >

                Mis pedidos

            </a>


            <button
                type="button"
                onclick="window.location.href='logout.php'"
            >

                Cerrar sesión

            </button>


        <?php else: ?>


            <button
                type="button"
                class="btn-login-header"
                onclick="mostrarModalLogin()"
            >

                Iniciar sesión

            </button>


            <a
                href="registro.php"
                class="btn-cuenta-header"
            >

                Crear cuenta

            </a>


        <?php endif; ?>


    </nav>


</header>


<!-- =========================================
     TIENDA
     ========================================= -->

<main class="tienda-publica">


    <div class="tienda-contenido">


        <!-- BIENVENIDA -->

        <section
            class="tienda-bienvenida"
        >


            <?php if (
                $cliente_logueado
            ): ?>


                <h1>

                    ¡Hola,

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["nombre"]
                    );

                    ?>!

                </h1>


                <p>

                    Explora nuestros productos
                    y realiza tu pedido.

                </p>


            <?php else: ?>


                <h1>

                    Bienvenido a
                    Changarro Súper y Más

                </h1>


                <p>

                    Explora nuestros productos
                    y encuentra lo que necesitas.

                    No necesitas una cuenta
                    para navegar.

                </p>


            <?php endif; ?>


        </section>


        <!-- =====================================
             BUSCADOR
             ===================================== -->

        <form
            method="GET"
            class="buscador-productos"
        >


            <input
                type="text"
                name="buscar"
                value="<?php

                    echo htmlspecialchars(
                        $busqueda
                    );

                ?>"
                placeholder="Buscar producto, categoría o código..."
            >


            <button
                type="submit"
            >

                Buscar

            </button>


            <?php if (
                $busqueda !== ""
            ): ?>

                <a
                    href="index.php"
                    class="limpiar-busqueda"
                >

                    Limpiar

                </a>

            <?php endif; ?>


        </form>


        <!-- =====================================
             CATEGORÍAS
             ===================================== -->

        <div
            id="categorias"
            class="categorias"
        >


            <a
                href="index.php"
            >

                Todos

            </a>


            <?php if (
                $categorias &&
                $categorias->num_rows > 0
            ): ?>


                <?php while (
                    $categoria =
                    $categorias->fetch_assoc()
                ): ?>


                    <a
                        href="index.php?buscar=<?php

                            echo urlencode(
                                $categoria["categoria"]
                            );

                        ?>"
                    >

                        <?php

                        echo htmlspecialchars(
                            $categoria["categoria"]
                        );

                        ?>

                    </a>


                <?php endwhile; ?>


            <?php endif; ?>


        </div>


        <!-- =====================================
             PRODUCTOS
             ===================================== -->

        <section
            id="productos"
        >


            <div
                class="catalogo-grid"
            >


                <?php if (
                    $productos &&
                    $productos->num_rows > 0
                ): ?>


                    <?php while (
                        $producto =
                        $productos->fetch_assoc()
                    ): ?>


                        <article
                            class="catalogo-producto"
                        >


                            <!-- ICONO -->

                            <div
                                class="catalogo-producto-icono"
                            >

                                🛒

                            </div>


                            <!-- INFORMACIÓN -->

                            <div
                                class="catalogo-producto-info"
                            >


                                <span
                                    class="catalogo-categoria"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $producto["categoria"]
                                    );

                                    ?>

                                </span>


                                <h3>

                                    <?php

                                    echo htmlspecialchars(
                                        $producto["nombre"]
                                    );

                                    ?>

                                </h3>


                                <p
                                    class="catalogo-codigo"
                                >

                                    Código:

                                    <?php

                                    echo htmlspecialchars(
                                        $producto["codigo"]
                                    );

                                    ?>

                                </p>


                                <div
                                    class="catalogo-stock"
                                >

                                    <?php

                                    echo intval(
                                        $producto["stock"]
                                    );

                                    ?>

                                    disponibles

                                </div>


                            </div>


                            <!-- PRECIO -->

                            <div
                                class="catalogo-producto-footer"
                            >


                                <strong>

                                    $

                                    <?php

                                    echo number_format(
                                        $producto["precio_venta"],
                                        2
                                    );

                                    ?>

                                </strong>


                                <?php if (
                                    $cliente_logueado
                                ): ?>


                                    <!-- CLIENTE LOGUEADO -->

                                    <form
                                        method="POST"
                                        action="clientes/carrito_clientes.php"
                                    >


                                        <input
                                            type="hidden"
                                            name="accion"
                                            value="agregar"
                                        >


                                        <input
                                            type="hidden"
                                            name="producto_id"
                                            value="<?php

                                                echo intval(
                                                    $producto["id"]
                                                );

                                            ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="cantidad"
                                            value="1"
                                        >


                                        <button
                                            type="submit"
                                            class="btn-agregar-carrito"
                                        >

                                            + Agregar

                                        </button>


                                    </form>


                                <?php else: ?>


                                    <!-- VISITANTE -->

                                    <button
                                        type="button"
                                        class="btn-agregar-carrito"
                                        onclick="mostrarModalLogin()"
                                    >

                                        + Agregar

                                    </button>


                                <?php endif; ?>


                            </div>


                        </article>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div
                        style="
                            background:white;
                            padding:40px;
                            border-radius:14px;
                            text-align:center;
                            grid-column:1/-1;
                        "
                    >

                        <h3>

                            No encontramos productos.

                        </h3>


                        <p>

                            Intenta buscar otro producto
                            o categoría.

                        </p>

                    </div>


                <?php endif; ?>


            </div>


        </section>


    </div>


</main>


<!-- =========================================
     MODAL LOGIN
     ========================================= -->

<div
    id="modalLogin"
    class="modal-login
        <?php

        echo (
            $mensaje !== ""
        )
        ? "mostrar"
        : "";

        ?>"
>


    <div
        class="modal-login-box"
    >


        <!-- CERRAR -->

        <button
            type="button"
            class="modal-cerrar"
            onclick="cerrarModalLogin()"
        >

            ×

        </button>


        <!-- LOGO -->

        <div
            class="modal-logo"
        >

            <img
                src="img/logo_changarro_transparente.png"
                alt="El Changarro"
            >

        </div>


        <h2>

            Iniciar sesión

        </h2>


        <p
            class="modal-subtitulo"
        >

            Inicia sesión para agregar
            productos al carrito.

        </p>


        <!-- =================================
             MENSAJE
             ================================= -->

        <?php if (
            $mensaje !== ""
        ): ?>

            <div
                class="modal-error"
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
            action="login.php"
            method="POST"
        >


            <!-- USUARIO -->

            <div
                class="modal-campo"
            >


                <label
                    for="usuario"
                >

                    Correo, usuario o teléfono

                </label>


                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    placeholder="Ingresa tu correo, usuario o teléfono"
                    autocomplete="username"
                    required
                >


            </div>


            <!-- CONTRASEÑA -->

            <div
                class="modal-campo"
            >


                <label
                    for="contrasena"
                >

                    Contraseña

                </label>


                <input
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    placeholder="Ingresa tu contraseña"
                    autocomplete="current-password"
                    required
                >


            </div>


            <!-- BOTÓN -->

            <button
                type="submit"
                class="modal-login-submit"
            >

                Iniciar sesión

            </button>


        </form>


        <!-- =================================
             ENLACES
             ================================= -->

        <div
            class="modal-enlaces"
        >


            <a
                href="recuperar_contrasena.php"
                class="olvido"
            >

                ¿Olvidaste tu contraseña?

            </a>


            <div
                class="modal-crear"
            >

                ¿No tienes una cuenta?


                <a
                    href="registro.php"
                >

                    Crear cuenta

                </a>


            </div>


        </div>


    </div>


</div>


<script>


/* =========================================
   MOSTRAR LOGIN
   ========================================= */

function mostrarModalLogin() {

    const modal =
        document.getElementById(
            "modalLogin"
        );


    modal.classList.add(
        "mostrar"
    );


    setTimeout(
        function() {

            const usuario =
                document.getElementById(
                    "usuario"
                );


            if (usuario) {

                usuario.focus();

            }

        },
        100
    );

}


/* =========================================
   CERRAR LOGIN
   ========================================= */

function cerrarModalLogin() {

    const modal =
        document.getElementById(
            "modalLogin"
        );


    modal.classList.remove(
        "mostrar"
    );

}


/* =========================================
   CERRAR AL HACER CLICK FUERA
   ========================================= */

document
    .getElementById("modalLogin")
    .addEventListener(
        "click",
        function(event) {

            if (
                event.target === this
            ) {

                cerrarModalLogin();

            }

        }
    );


/* =========================================
   CERRAR CON ESC
   ========================================= */

document.addEventListener(
    "keydown",
    function(event) {

        if (
            event.key === "Escape"
        ) {

            cerrarModalLogin();

        }

    }
);

</script>


</body>

</html>