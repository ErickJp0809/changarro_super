<?php

session_start();

require_once "../config/conexion.php";


/* =========================================
   VERIFICAR SESIÓN
   ========================================= */

if (!isset($_SESSION["id"])) {

    header("Location: ../index.php");
    exit();

}


/* =========================================
   VERIFICAR QUE SEA CLIENTE
   ========================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Cliente"
) {

    header("Location: ../admin/dashboard.php");
    exit();

}


/* =========================================
   OBTENER PRODUCTOS DISPONIBLES
   ========================================= */

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
    ORDER BY id DESC
    LIMIT 8
";

$resultado_productos =
    $conexion->query($sql_productos);


/* =========================================
   NOMBRE DEL CLIENTE
   ========================================= */

$nombre_cliente = htmlspecialchars(
    $_SESSION["nombre"]
);


/* =========================================
   INICIAL DEL CLIENTE
   ========================================= */

$inicial_cliente = strtoupper(
    substr(
        $_SESSION["nombre"],
        0,
        1
    )
);


/* =========================================
   CANTIDAD DEL CARRITO
   ========================================= */

$cantidad_carrito = 0;


if (isset($_SESSION["carrito"])) {

    foreach (
        $_SESSION["carrito"] as $item
    ) {

        $cantidad_carrito +=
            intval($item["cantidad"]);

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
        Inicio | Changarro Súper y Más
    </title>


    <link
        rel="stylesheet"
        href="../css/clientes.css"
    >


    <style>

        /* =====================================
           CONTADOR DEL CARRITO
           ===================================== */

        .contador-carrito {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 20px;

            height: 20px;

            padding: 0 5px;

            margin-left: 6px;

            border-radius: 10px;

            background: #f7941d;

            color: white;

            font-size: 11px;

            font-weight: bold;

        }


        /* =====================================
           BOTÓN DE UBICACIÓN
           ===================================== */

        .btn-ubicacion {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 12px 20px;

            border-radius: 9px;

            background: white;

            color: #222;

            text-decoration: none;

            font-size: 14px;

            font-weight: bold;

            transition: 0.2s;

        }


        .btn-ubicacion:hover {

            transform: translateY(-2px);

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.12);

        }


        /* =====================================
           BANNER DE UBICACIÓN
           ===================================== */

        .cliente-banner {

            min-height: 250px;

        }


        .banner-texto h2 {

            max-width: 620px;

        }


        .banner-texto p {

            max-width: 650px;

        }


        /* =====================================
           PRODUCTOS
           ===================================== */

        .producto-card {

            min-height: 210px;

        }

    </style>

</head>


<body>


<div class="cliente-layout">


    <!-- =========================================
         SIDEBAR
         ========================================= -->

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


            <!-- INICIO -->

            <a
                href="./dashboard_clientes.php"
                class="activo"
            >

                <span>⌂</span>

                Inicio

            </a>


            <!-- PRODUCTOS -->

            <a
                href="./productos_clientes.php"
            >

                <span>▣</span>

                Productos

            </a>


            <!-- CARRITO -->

            <a
                href="./carrito_clientes.php"
            >

                <span>🛒</span>

                Mi carrito


                <?php if (
                    $cantidad_carrito > 0
                ): ?>

                    <small
                        class="contador-carrito"
                    >

                        <?php

                        echo $cantidad_carrito;

                        ?>

                    </small>

                <?php endif; ?>


            </a>


            <!-- PEDIDOS -->

            <a
                href="./pedidos_clientes.php"
            >

                <span>▤</span>

                Mis pedidos

            </a>


            <!-- CUENTA -->

            <a
                href="./cuenta.php"
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


    <!-- =========================================
         CONTENIDO
         ========================================= -->

    <main class="cliente-contenido">


        <!-- =========================================
             ENCABEZADO
             ========================================= -->

        <header class="cliente-encabezado">


            <div>

                <h1>

                    Bienvenido,
                    <?php

                    echo $nombre_cliente;

                    ?>

                </h1>


                <p>

                    Encuentra tus productos favoritos
                    en Changarro Súper y Más.

                </p>

            </div>


            <!-- SOLO CÍRCULO CON INICIAL -->

            <div class="cliente-avatar">

                <?php

                echo htmlspecialchars(
                    $inicial_cliente
                );

                ?>

            </div>


        </header>


        <!-- =========================================
             UBICACIÓN DE LA TIENDA
             ========================================= -->

        <section class="cliente-banner">


            <div class="banner-texto">


                <span>

                    CHANGARRO SÚPER Y MÁS

                </span>


                <h2>

                    Visítanos en nuestra
                    tienda física.

                </h2>


                <p>

                    Encuentra nuestra tienda y consulta
                    fácilmente nuestra ubicación en
                    Google Maps.

                </p>


                <!-- =================================
                     GOOGLE MAPS
                     ================================= -->

                <a
                    href="https://maps.app.goo.gl/cTHxGLh2dLv8Jw9M9"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-ubicacion"
                >

                    📍 Ver ubicación en Google Maps

                </a>


            </div>


            <!-- =====================================
                 LOGO
                 ===================================== -->

            <div class="banner-logo">

                <img
                    src="../img/logo_changarro_transparente.png"
                    alt="Changarro Súper y Más"
                >

            </div>


        </section>


        <!-- =========================================
             PRODUCTOS DISPONIBLES
             ========================================= -->

        <section class="cliente-seccion">


            <!-- CABECERA -->

            <div class="cliente-seccion-header">


                <div>

                    <h2>

                        Productos disponibles

                    </h2>


                    <p>

                        Algunos de nuestros productos.

                    </p>

                </div>


                <a
                    href="./productos_clientes.php"
                    class="ver-todos"
                >

                    Ver todos →

                </a>


            </div>


            <!-- =====================================
                 PRODUCTOS
                 ===================================== -->

            <div class="productos-grid">


            <?php if (
                $resultado_productos &&
                $resultado_productos->num_rows > 0
            ): ?>


                <?php while (
                    $producto =
                    $resultado_productos->fetch_assoc()
                ): ?>


                    <article
                        class="producto-card"
                    >


                        <!-- ICONO -->

                        <div class="producto-icono">

                            🛒

                        </div>


                        <!-- INFORMACIÓN -->

                        <div class="producto-info">


                            <span
                                class="producto-categoria"
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


                            <span
                                class="producto-codigo"
                            >

                                Código:

                                <?php

                                echo htmlspecialchars(
                                    $producto["codigo"]
                                );

                                ?>

                            </span>


                            <!-- PARTE INFERIOR -->

                            <div class="producto-abajo">


                                <strong>

                                    $

                                    <?php

                                    echo number_format(
                                        $producto["precio_venta"],
                                        2
                                    );

                                    ?>

                                </strong>


                                <span class="disponible">

                                    <?php

                                    echo intval(
                                        $producto["stock"]
                                    );

                                    ?>

                                    disponibles

                                </span>


                            </div>


                        </div>


                    </article>


                <?php endwhile; ?>


            <?php else: ?>


                <div
                    class="sin-productos-cliente"
                >


                    <h3>

                        No hay productos disponibles.

                    </h3>


                    <p>

                        Actualmente no hay productos
                        disponibles para comprar.

                    </p>


                </div>


            <?php endif; ?>


            </div>


        </section>


    </main>


</div>


</body>

</html>