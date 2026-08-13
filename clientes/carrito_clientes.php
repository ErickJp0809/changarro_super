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
   VERIFICAR CLIENTE
   ========================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Cliente"
) {

    header("Location: ../admin/dashboard.php");
    exit();

}


/* =========================================
   INICIALIZAR CARRITO
   ========================================= */

if (!isset($_SESSION["carrito"])) {

    $_SESSION["carrito"] = [];

}


/* =========================================
   AGREGAR PRODUCTO
   ========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["accion"]) &&
    $_POST["accion"] === "agregar"
) {

    $producto_id = intval(
        $_POST["producto_id"] ?? 0
    );

    $cantidad = intval(
        $_POST["cantidad"] ?? 1
    );


    if ($cantidad < 1) {

        $cantidad = 1;

    }


    /* =====================================
       BUSCAR PRODUCTO
       ===================================== */

    $sql = "
        SELECT
            id,
            codigo,
            nombre,
            categoria,
            precio_venta,
            stock
        FROM productos
        WHERE id = ?
        AND activo = 1
        AND stock > 0
        LIMIT 1
    ";


    $stmt = $conexion->prepare($sql);


    if (!$stmt) {

        die("Error en la consulta.");

    }


    $stmt->bind_param(
        "i",
        $producto_id
    );


    $stmt->execute();


    $resultado = $stmt->get_result();


    if ($resultado->num_rows === 1) {

        $producto =
            $resultado->fetch_assoc();


        $stock =
            intval($producto["stock"]);


        /* =================================
           PRODUCTO YA EXISTE
           ================================= */

        if (
            isset(
                $_SESSION["carrito"][$producto_id]
            )
        ) {

            $cantidad_actual =
                intval(
                    $_SESSION["carrito"][$producto_id]["cantidad"]
                );


            $nueva_cantidad =
                $cantidad_actual + $cantidad;


            /* No superar stock */

            if (
                $nueva_cantidad > $stock
            ) {

                $nueva_cantidad =
                    $stock;

            }


            $_SESSION["carrito"][$producto_id]["cantidad"] =
                $nueva_cantidad;


            $_SESSION["carrito"][$producto_id]["stock"] =
                $stock;


            /* Actualizar precio por si cambió */

            $_SESSION["carrito"][$producto_id]["precio"] =
                $producto["precio_venta"];

        }


        /* =================================
           PRODUCTO NUEVO
           ================================= */

        else {

            if ($cantidad > $stock) {

                $cantidad = $stock;

            }


            $_SESSION["carrito"][$producto_id] = [

                "id" =>
                    $producto["id"],

                "codigo" =>
                    $producto["codigo"],

                "nombre" =>
                    $producto["nombre"],

                "categoria" =>
                    $producto["categoria"],

                "precio" =>
                    $producto["precio_venta"],

                "cantidad" =>
                    $cantidad,

                "stock" =>
                    $stock

            ];

        }

    }


    /*
     * IMPORTANTE:
     *
     * NO mandamos al carrito.
     *
     * Regresamos al catálogo para
     * que el cliente pueda seguir comprando.
     */

    header(
        "Location: productos_clientes.php"
    );

    exit();

}


/* =========================================
   ACTUALIZAR CANTIDAD
   ========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["accion"]) &&
    $_POST["accion"] === "actualizar"
) {

    $producto_id =
        intval($_POST["producto_id"] ?? 0);


    $cantidad =
        intval($_POST["cantidad"] ?? 0);


    if (
        isset(
            $_SESSION["carrito"][$producto_id]
        )
    ) {


        /* ================================
           ELIMINAR SI ES 0
           ================================ */

        if ($cantidad <= 0) {

            unset(
                $_SESSION["carrito"][$producto_id]
            );

        }

        else {


            /* ================================
               CONSULTAR STOCK ACTUAL
               ================================ */

            $sql = "
                SELECT
                    id,
                    codigo,
                    nombre,
                    categoria,
                    precio_venta,
                    stock,
                    activo
                FROM productos
                WHERE id = ?
                LIMIT 1
            ";


            $stmt =
                $conexion->prepare($sql);


            if ($stmt) {

                $stmt->bind_param(
                    "i",
                    $producto_id
                );


                $stmt->execute();


                $resultado =
                    $stmt->get_result();


                if (
                    $resultado->num_rows === 1
                ) {

                    $producto =
                        $resultado->fetch_assoc();


                    $stock =
                        intval(
                            $producto["stock"]
                        );


                    /* =========================
                       PRODUCTO DESACTIVADO
                       ========================= */

                    if (
                        intval(
                            $producto["activo"]
                        ) !== 1 ||
                        $stock <= 0
                    ) {

                        unset(
                            $_SESSION["carrito"][$producto_id]
                        );

                    }

                    else {


                        /* =========================
                           NO SUPERAR STOCK
                           ========================= */

                        if (
                            $cantidad > $stock
                        ) {

                            $cantidad =
                                $stock;

                        }


                        $_SESSION["carrito"][$producto_id]["cantidad"] =
                            $cantidad;


                        $_SESSION["carrito"][$producto_id]["stock"] =
                            $stock;


                        /* Actualizar información */

                        $_SESSION["carrito"][$producto_id]["nombre"] =
                            $producto["nombre"];


                        $_SESSION["carrito"][$producto_id]["categoria"] =
                            $producto["categoria"];


                        $_SESSION["carrito"][$producto_id]["codigo"] =
                            $producto["codigo"];


                        $_SESSION["carrito"][$producto_id]["precio"] =
                            $producto["precio_venta"];

                    }

                }

                else {

                    unset(
                        $_SESSION["carrito"][$producto_id]
                    );

                }

            }

        }

    }


    header(
        "Location: carrito_clientes.php"
    );

    exit();

}


/* =========================================
   ELIMINAR PRODUCTO
   ========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["accion"]) &&
    $_POST["accion"] === "eliminar"
) {

    $producto_id =
        intval($_POST["producto_id"] ?? 0);


    if (
        isset(
            $_SESSION["carrito"][$producto_id]
        )
    ) {

        unset(
            $_SESSION["carrito"][$producto_id]
        );

    }


    header(
        "Location: carrito_clientes.php"
    );

    exit();

}


/* =========================================
   VACIAR CARRITO
   ========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["accion"]) &&
    $_POST["accion"] === "vaciar"
) {

    $_SESSION["carrito"] = [];


    header(
        "Location: carrito_clientes.php"
    );

    exit();

}


/* =========================================
   CALCULAR CARRITO
   ========================================= */

$total = 0;

$cantidad_productos = 0;


foreach (
    $_SESSION["carrito"]
    as $item
) {

    $precio =
        floatval(
            $item["precio"]
        );


    $cantidad =
        intval(
            $item["cantidad"]
        );


    $subtotal =
        $precio * $cantidad;


    $total += $subtotal;


    $cantidad_productos +=
        $cantidad;

}


/* =========================================
   INICIAL DEL CLIENTE
   ========================================= */

$inicial =
    strtoupper(
        substr(
            $_SESSION["nombre"],
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
        Mi carrito | Changarro Súper y Más
    </title>


    <link
        rel="stylesheet"
        href="../css/clientes.css"
    >


    <style>

        .carrito-contenedor {

            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                320px;

            gap: 22px;

            align-items: start;

        }


        .carrito-productos {

            background: #ffffff;

            border: 1px solid #e8e8e8;

            border-radius: 16px;

            padding: 25px;

        }


        .carrito-titulo {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;

        }


        .carrito-titulo h2 {

            font-size: 20px;

            margin-bottom: 5px;

        }


        .carrito-titulo p {

            color: #888;

            font-size: 13px;

        }


        .btn-vaciar {

            border: none;

            background: transparent;

            color: #d93662;

            font-size: 13px;

            font-weight: bold;

            cursor: pointer;

        }


        .btn-vaciar:hover {

            text-decoration: underline;

        }


        .carrito-item {

            display: grid;

            grid-template-columns:
                65px
                minmax(180px, 1fr)
                100px
                100px
                35px;

            gap: 16px;

            align-items: center;

            padding: 18px 0;

            border-top: 1px solid #eeeeee;

        }


        .carrito-item-icono {

            width: 60px;

            height: 60px;

            display: flex;

            justify-content: center;

            align-items: center;

            background: #fff3e5;

            border-radius: 12px;

            font-size: 26px;

        }


        .carrito-item-info span {

            display: block;

            color: #f7941d;

            font-size: 11px;

            font-weight: bold;

            text-transform: uppercase;

            margin-bottom: 4px;

        }


        .carrito-item-info h3 {

            font-size: 15px;

            margin-bottom: 5px;

        }


        .carrito-item-info p {

            color: #999;

            font-size: 11px;

            margin-bottom: 7px;

        }


        .carrito-item-info strong {

            font-size: 13px;

        }


        .carrito-cantidad {

            display: flex;

            flex-direction: column;

            gap: 6px;

        }


        .carrito-cantidad label {

            color: #777;

            font-size: 11px;

        }


        .carrito-cantidad input {

            width: 75px;

            height: 38px;

            padding: 0 8px;

            border: 1px solid #d8d8d8;

            border-radius: 8px;

            outline: none;

            font-size: 14px;

            text-align: center;

        }


        .carrito-cantidad input:focus {

            border-color: #f7941d;

            box-shadow:
                0 0 0 3px
                rgba(247, 148, 29, 0.12);

        }


        .carrito-subtotal {

            display: flex;

            flex-direction: column;

            gap: 5px;

        }


        .carrito-subtotal span {

            color: #888;

            font-size: 11px;

        }


        .carrito-subtotal strong {

            font-size: 16px;

        }


        .btn-eliminar-carrito {

            width: 32px;

            height: 32px;

            border: none;

            border-radius: 8px;

            background: #fff0f2;

            color: #d93662;

            font-size: 22px;

            line-height: 1;

            cursor: pointer;

        }


        .btn-eliminar-carrito:hover {

            background: #d93662;

            color: white;

        }


        .carrito-resumen {

            background: #ffffff;

            border: 1px solid #e8e8e8;

            border-radius: 16px;

            padding: 25px;

            position: sticky;

            top: 25px;

        }


        .carrito-resumen h2 {

            font-size: 20px;

            margin-bottom: 22px;

        }


        .resumen-linea {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 12px 0;

            border-bottom: 1px solid #eeeeee;

            font-size: 14px;

        }


        .resumen-linea span {

            color: #777;

        }


        .resumen-linea.total {

            border-bottom: none;

            padding-top: 18px;

        }


        .resumen-linea.total span {

            color: #222;

            font-size: 16px;

            font-weight: bold;

        }


        .resumen-linea.total strong {

            color: #f7941d;

            font-size: 24px;

        }


        .btn-finalizar {

            display: flex;

            align-items: center;

            justify-content: center;

            width: 100%;

            height: 47px;

            margin-top: 15px;

            border: none;

            border-radius: 9px;

            background: #f7941d;

            color: white;

            font-size: 14px;

            font-weight: bold;

            text-decoration: none;

            cursor: pointer;

        }


        .btn-finalizar:hover {

            background: #e98212;

        }


        .btn-seguir-comprando {

            display: block;

            margin-top: 15px;

            color: #249db5;

            text-align: center;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }


        .btn-seguir-comprando:hover {

            text-decoration: underline;

        }


        .carrito-vacio {

            grid-column: 1 / -1;

            background: #ffffff;

            border: 1px solid #e8e8e8;

            border-radius: 16px;

            padding: 70px 30px;

            text-align: center;

        }


        .carrito-vacio-icono {

            width: 75px;

            height: 75px;

            display: flex;

            justify-content: center;

            align-items: center;

            margin: 0 auto 20px;

            border-radius: 18px;

            background: #fff3e5;

            font-size: 35px;

        }


        .carrito-vacio h2 {

            font-size: 22px;

            margin-bottom: 8px;

        }


        .carrito-vacio p {

            color: #888;

            font-size: 14px;

            margin-bottom: 25px;

        }


        .btn-comprar {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 12px 20px;

            border-radius: 9px;

            background: #f7941d;

            color: white;

            text-decoration: none;

            font-weight: bold;

            font-size: 14px;

        }


        .btn-comprar:hover {

            background: #e98212;

        }


        @media (max-width: 1050px) {

            .carrito-contenedor {

                grid-template-columns: 1fr;

            }


            .carrito-resumen {

                position: static;

            }

        }


        @media (max-width: 750px) {

            .carrito-item {

                grid-template-columns:
                    55px
                    1fr
                    35px;

                gap: 12px;

            }


            .carrito-item-icono {

                width: 52px;

                height: 52px;

            }


            .carrito-cantidad {

                grid-column: 2;

            }


            .carrito-subtotal {

                grid-column: 2;

            }


            .btn-eliminar-carrito {

                grid-column: 3;

                grid-row: 1;

            }

        }

    </style>

</head>


<body>


<div class="cliente-layout">


    <!-- =========================================
         SIDEBAR
         ========================================= -->

    <aside class="cliente-sidebar">


        <div class="cliente-marca">

            <img
                src="../img/logo_changarro_transparente.png"
                alt="Changarro Súper y Más"
            >

        </div>


        <nav class="cliente-menu">


            <a
                href="dashboard_clientes.php"
            >

                <span>⌂</span>

                Inicio

            </a>


            <a
                href="productos_clientes.php"
            >

                <span>▣</span>

                Productos

            </a>


            <a
                href="carrito_clientes.php"
                class="activo"
            >

                <span>🛒</span>

                Mi carrito

                <?php if (
                    $cantidad_productos > 0
                ): ?>

                    <small
                        class="contador-carrito"
                    >

                        <?php

                        echo $cantidad_productos;

                        ?>

                    </small>

                <?php endif; ?>

            </a>


            <a
                href="pedidos_clientes.php"
            >

                <span>▤</span>

                Mis pedidos

            </a>


            <a
                href="cuenta.php"
            >

                <span>♙</span>

                Mi cuenta

            </a>


        </nav>


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


        <header class="cliente-encabezado">


            <div>

                <h1>
                    Mi carrito
                </h1>

                <p>
                    Revisa los productos que deseas comprar.
                </p>

            </div>


            <!-- SOLO CÍRCULO CON INICIAL -->

            <div class="cliente-avatar">

                <?php

                echo htmlspecialchars(
                    $inicial
                );

                ?>

            </div>


        </header>


        <!-- =========================================
             CARRITO
             ========================================= -->

        <section class="carrito-contenedor">


        <?php if (
            count($_SESSION["carrito"]) > 0
        ): ?>


            <!-- =====================================
                 PRODUCTOS
                 ===================================== -->

            <div class="carrito-productos">


                <div class="carrito-titulo">


                    <div>

                        <h2>
                            Productos seleccionados
                        </h2>

                        <p>

                            <?php

                            echo $cantidad_productos;

                            ?>

                            producto(s)

                        </p>

                    </div>


                    <form
                        method="POST"
                        onsubmit="
                            return confirm(
                                '¿Deseas vaciar todo el carrito?'
                            );
                        "
                    >

                        <input
                            type="hidden"
                            name="accion"
                            value="vaciar"
                        >


                        <button
                            type="submit"
                            class="btn-vaciar"
                        >

                            Vaciar carrito

                        </button>

                    </form>


                </div>


                <?php foreach (
                    $_SESSION["carrito"]
                    as $item
                ): ?>


                    <?php

                    $subtotal =
                        floatval(
                            $item["precio"]
                        ) *
                        intval(
                            $item["cantidad"]
                        );

                    ?>


                    <div class="carrito-item">


                        <!-- ICONO -->

                        <div class="carrito-item-icono">

                            🛒

                        </div>


                        <!-- INFORMACIÓN -->

                        <div class="carrito-item-info">


                            <span>

                                <?php

                                echo htmlspecialchars(
                                    $item["categoria"]
                                );

                                ?>

                            </span>


                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $item["nombre"]
                                );

                                ?>

                            </h3>


                            <p>

                                Código:

                                <?php

                                echo htmlspecialchars(
                                    $item["codigo"]
                                );

                                ?>

                            </p>


                            <strong>

                                $

                                <?php

                                echo number_format(
                                    $item["precio"],
                                    2
                                );

                                ?>

                                c/u

                            </strong>


                        </div>


                        <!-- =================================
                             CANTIDAD
                             ================================= -->

                        <form
                            method="POST"
                            class="carrito-cantidad"
                        >

                            <input
                                type="hidden"
                                name="accion"
                                value="actualizar"
                            >


                            <input
                                type="hidden"
                                name="producto_id"
                                value="<?php

                                    echo $item["id"];

                                ?>"
                            >


                            <label>
                                Cantidad
                            </label>


                            <input
                                type="number"
                                name="cantidad"
                                value="<?php

                                    echo $item["cantidad"];

                                ?>"
                                min="1"
                                max="<?php

                                    echo $item["stock"];

                                ?>"
                                onchange="
                                    this.form.submit();
                                "
                            >

                        </form>


                        <!-- SUBTOTAL -->

                        <div class="carrito-subtotal">


                            <span>
                                Subtotal
                            </span>


                            <strong>

                                $

                                <?php

                                echo number_format(
                                    $subtotal,
                                    2
                                );

                                ?>

                            </strong>


                        </div>


                        <!-- ELIMINAR -->

                        <form method="POST">

                            <input
                                type="hidden"
                                name="accion"
                                value="eliminar"
                            >


                            <input
                                type="hidden"
                                name="producto_id"
                                value="<?php

                                    echo $item["id"];

                                ?>"
                            >


                            <button
                                type="submit"
                                class="btn-eliminar-carrito"
                                title="Eliminar producto"
                            >

                                ×

                            </button>

                        </form>


                    </div>


                <?php endforeach; ?>


            </div>


            <!-- =====================================
                 RESUMEN
                 ===================================== -->

            <aside class="carrito-resumen">


                <h2>
                    Resumen de compra
                </h2>


                <div class="resumen-linea">

                    <span>
                        Productos
                    </span>

                    <strong>

                        <?php

                        echo $cantidad_productos;

                        ?>

                    </strong>

                </div>


                <div class="resumen-linea">

                    <span>
                        Subtotal
                    </span>

                    <strong>

                        $

                        <?php

                        echo number_format(
                            $total,
                            2
                        );

                        ?>

                    </strong>

                </div>


                <div
                    class="resumen-linea total"
                >

                    <span>
                        Total
                    </span>

                    <strong>

                        $

                        <?php

                        echo number_format(
                            $total,
                            2
                        );

                        ?>

                    </strong>

                </div>


                <!-- =================================
                     SIGUIENTE PASO
                     ================================= -->

                <a
                    href="confirmar_pedido_clientes.php"
                    class="btn-finalizar"
                >

                    Continuar con el pedido

                </a>


                <a
                    href="productos_clientes.php"
                    class="btn-seguir-comprando"
                >

                    ← Seguir comprando

                </a>


            </aside>


        <?php else: ?>


            <!-- =====================================
                 CARRITO VACÍO
                 ===================================== -->

            <div class="carrito-vacio">


                <div class="carrito-vacio-icono">

                    🛒

                </div>


                <h2>

                    Tu carrito está vacío

                </h2>


                <p>

                    Agrega algunos productos
                    para comenzar tu compra.

                </p>


                <a
                    href="productos_clientes.php"
                    class="btn-comprar"
                >

                    Ver productos

                </a>


            </div>


        <?php endif; ?>


        </section>


    </main>


</div>


</body>

</html>