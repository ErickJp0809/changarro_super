<?php

session_start();

require_once "../config/conexion.php";


/* =========================================
   VERIFICAR SESIÓN
   ========================================= */

/*
   La página puede visitarse sin cuenta.

   Si existe una sesión de Cliente:
   - puede agregar productos al carrito
   - puede ver su nombre
   - puede usar las funciones de cliente

   Si NO existe sesión:
   - puede navegar
   - puede buscar
   - puede ver categorías
   - al intentar agregar un producto
     se mostrará el modal de inicio de sesión.
*/

$cliente_logueado = (

    isset($_SESSION["id"]) &&

    isset($_SESSION["rol"]) &&

    $_SESSION["rol"] === "Cliente"

);


/* =========================================
   BUSCADOR
   ========================================= */

$busqueda = "";


if (isset($_GET["buscar"])) {

    $busqueda =
        trim(
            $_GET["buscar"]
        );

}


/* =========================================
   OBTENER PRODUCTOS
   ========================================= */

if ($busqueda !== "") {


    $busqueda_sql =
        "%" . $busqueda . "%";


    $sql = "

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


    $stmt =
        $conexion->prepare(
            $sql
        );


    $stmt->bind_param(
        "sss",
        $busqueda_sql,
        $busqueda_sql,
        $busqueda_sql
    );


    $stmt->execute();


    $resultado =
        $stmt->get_result();


} else {


    $sql = "

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


    $resultado =
        $conexion->query(
            $sql
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


$resultado_categorias =
    $conexion->query(
        $sql_categorias
    );


/* =========================================
   CANTIDAD DEL CARRITO
   ========================================= */

$cantidad_carrito = 0;


if (
    isset($_SESSION["carrito"])
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


if ($cliente_logueado) {

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

        Productos | Changarro Súper y Más

    </title>


    <link
        rel="stylesheet"
        href="../css/clientes.css"
    >


    <style>

        /* =====================================
           PRODUCTOS
           ===================================== */

        .catalogo-producto-footer form {

            margin: 0;

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
           CONTADOR
           ===================================== */

        .contador-boton {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 20px;

            height: 20px;

            margin-left: 5px;

            padding: 0 5px;

            border-radius: 10px;

            background: white;

            color: #f7941d;

            font-size: 11px;

            font-weight: bold;

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

            height: 42px;

            padding: 0 14px;

            border: 1px solid #ddd;

            border-radius: 8px;

            outline: none;

        }


        .buscador-productos button {

            padding: 0 18px;

            border: none;

            border-radius: 8px;

            background: #f7941d;

            color: white;

            font-weight: bold;

            cursor: pointer;

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

            background: #f4f4f4;

            color: #555;

            text-decoration: none;

            font-size: 13px;

        }


        .categorias a:hover,
        .categorias .categoria-activa {

            background: #f7941d;

            color: white;

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
                    .55
                );

            align-items: center;

            justify-content: center;

        }


        .modal-login.mostrar {

            display: flex;

        }


        .modal-contenido {

            position: relative;

            width: 90%;

            max-width: 420px;

            background: white;

            border-radius: 16px;

            padding: 30px;

            text-align: center;

            box-sizing: border-box;

        }


        .modal-cerrar {

            position: absolute;

            top: 10px;

            right: 15px;

            border: none;

            background: transparent;

            font-size: 25px;

            cursor: pointer;

            color: #555;

        }


        .modal-contenido h2 {

            margin-bottom: 10px;

        }


        .modal-contenido p {

            color: #777;

            line-height: 1.5;

            margin-bottom: 20px;

        }


        .modal-botones {

            display: flex;

            flex-direction: column;

            gap: 10px;

        }


        .modal-botones a {

            display: flex;

            align-items: center;

            justify-content: center;

            height: 45px;

            border-radius: 8px;

            text-decoration: none;

            font-weight: bold;

            box-sizing: border-box;

        }


        .modal-btn-login {

            background: #f7941d;

            color: white;

        }


        .modal-btn-registro {

            border: 1px solid #249db5;

            color: #249db5;

        }


        .modal-btn-registro:hover {

            background: #249db5;

            color: white;

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
            >

                <span>⌂</span>

                Inicio

            </a>


            <!-- PRODUCTOS -->

            <a
                href="./productos_clientes.php"
                class="activo"
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


            <?php if (
                $cliente_logueado
            ): ?>

                <a
                    href="../logout.php"
                >

                    Cerrar sesión

                </a>


            <?php else: ?>

                <a
                    href="../index.php"
                >

                    Iniciar sesión

                </a>

            <?php endif; ?>


        </div>


    </aside>


    <!-- =========================================
         CONTENIDO
         ========================================= -->

    <main class="cliente-contenido">


        <!-- ENCABEZADO -->

        <header class="cliente-encabezado">


            <div>

                <h1>

                    Productos

                </h1>


                <p>

                    Explora los productos disponibles
                    en Changarro Súper y Más.

                </p>

            </div>


            <!-- AVATAR -->

            <div class="cliente-avatar">


                <?php if (
                    $cliente_logueado
                ): ?>


                    <?php

                    echo htmlspecialchars(
                        $inicial_cliente
                    );

                    ?>


                <?php else: ?>

                    👤

                <?php endif; ?>


            </div>


        </header>


        <!-- =========================================
             CATÁLOGO
             ========================================= -->

        <section class="catalogo">


            <!-- CABECERA -->

            <div class="catalogo-header">


                <div>

                    <h2>

                        Catálogo de productos

                    </h2>


                    <p>

                        Selecciona los productos
                        que deseas comprar.

                    </p>

                </div>


                <!-- CARRITO -->

                <a
                    href="./carrito_clientes.php"
                    class="btn-carrito"
                >

                    🛒 Mi carrito


                    <?php if (
                        $cantidad_carrito > 0
                    ): ?>

                        <span
                            class="contador-boton"
                        >

                            <?php

                            echo $cantidad_carrito;

                            ?>

                        </span>

                    <?php endif; ?>


                </a>


            </div>


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
                        href="./productos_clientes.php"
                        class="limpiar-busqueda"
                    >

                        Limpiar

                    </a>

                <?php endif; ?>


            </form>


            <!-- =====================================
                 CATEGORÍAS
                 ===================================== -->

            <div class="categorias">


                <a
                    href="./productos_clientes.php"
                    class="<?php

                        echo (
                            $busqueda === ""
                        )
                        ? "categoria-activa"
                        : "";

                    ?>"
                >

                    Todos

                </a>


                <?php if (
                    $resultado_categorias &&
                    $resultado_categorias->num_rows > 0
                ): ?>


                    <?php while (
                        $categoria =
                        $resultado_categorias->fetch_assoc()
                    ): ?>


                        <a
                            href="./productos_clientes.php?buscar=<?php

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

            <div class="catalogo-grid">


                <?php if (
                    $resultado &&
                    $resultado->num_rows > 0
                ): ?>


                    <?php while (
                        $producto =
                        $resultado->fetch_assoc()
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


                            <!-- =================================
                                 PRECIO Y AGREGAR
                                 ================================= -->

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


                                    <!-- =================================
                                         CLIENTE CON SESIÓN
                                         ================================= -->

                                    <form
                                        method="POST"
                                        action="./carrito_clientes.php"
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


                                    <!-- =================================
                                         VISITANTE
                                         ================================= -->

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
                        class="catalogo-vacio"
                    >

                        <h3>

                            No encontramos productos

                        </h3>


                        <p>

                            Intenta buscar otro producto
                            o categoría.

                        </p>

                    </div>


                <?php endif; ?>


            </div>


        </section>


    </main>


</div>


<!-- =========================================
     MODAL DE INICIO DE SESIÓN
     ========================================= -->

<div
    id="modalLogin"
    class="modal-login"
>


    <div class="modal-contenido">


        <!-- CERRAR -->

        <button
            type="button"
            class="modal-cerrar"
            onclick="cerrarModalLogin()"
        >

            ×

        </button>


        <h2>

            🔐 Inicia sesión

        </h2>


        <p>

            Para agregar productos al carrito
            necesitas iniciar sesión o crear
            una cuenta.

        </p>


        <div class="modal-botones">


            <!-- INICIAR SESIÓN -->

            <a
                href="../index.php"
                class="modal-btn-login"
            >

                Iniciar sesión

            </a>


            <!-- CREAR CUENTA -->

            <a
                href="../registro.php"
                class="modal-btn-registro"
            >

                Crear cuenta

            </a>


        </div>


    </div>


</div>


<script>


/* =========================================
   MOSTRAR MODAL
   ========================================= */

function mostrarModalLogin() {

    const modal =
        document.getElementById(
            "modalLogin"
        );


    modal.classList.add(
        "mostrar"
    );

}


/* =========================================
   CERRAR MODAL
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