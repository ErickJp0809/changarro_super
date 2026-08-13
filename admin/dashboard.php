<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/conexion.php";


/* =========================================
   PRODUCTOS ACTIVOS
   ========================================= */

$sql_productos = "
    SELECT COUNT(*) AS total
    FROM productos
    WHERE activo = 1
";

$resultado_productos = $conexion->query($sql_productos);

$total_productos =
    $resultado_productos->fetch_assoc()["total"];


/* =========================================
   STOCK TOTAL
   ========================================= */

$sql_stock = "
    SELECT COALESCE(SUM(stock), 0) AS total
    FROM productos
    WHERE activo = 1
";

$resultado_stock = $conexion->query($sql_stock);

$total_stock =
    $resultado_stock->fetch_assoc()["total"];


/* =========================================
   STOCK BAJO
   ========================================= */

$sql_stock_bajo = "
    SELECT COUNT(*) AS total
    FROM productos
    WHERE activo = 1
    AND stock <= 5
";

$resultado_stock_bajo =
    $conexion->query($sql_stock_bajo);

$total_stock_bajo =
    $resultado_stock_bajo->fetch_assoc()["total"];


/* =========================================
   VENTAS DE HOY
   ========================================= */

$sql_ventas_hoy = "
    SELECT
        COUNT(*) AS cantidad,
        COALESCE(SUM(total), 0) AS total
    FROM ventas
    WHERE DATE(fecha) = CURDATE()
    AND estado = 'Completada'
";

$resultado_ventas_hoy =
    $conexion->query($sql_ventas_hoy);

$ventas_hoy =
    $resultado_ventas_hoy->fetch_assoc();

$dinero_ventas_hoy =
    $ventas_hoy["total"];

$total_ventas_hoy =
    $ventas_hoy["cantidad"];


/* =========================================
   USUARIOS ACTIVOS
   ========================================= */

$sql_usuarios = "
    SELECT COUNT(*) AS total
    FROM usuarios
    WHERE estado = 'Activo'
";

$resultado_usuarios =
    $conexion->query($sql_usuarios);

$total_usuarios =
    $resultado_usuarios->fetch_assoc()["total"];

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
        Panel | Changarro Súper y Más
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

</head>


<body>


<div class="contenedor">


    <!-- =========================================
         MENÚ LATERAL
         ========================================= -->

    <aside class="sidebar">


        <!-- LOGO -->

        <div class="marca">

            <img
                src="../img/logo_changarro_transparente.png"
                alt="El Changarro"
            >

        </div>


        <!-- MENÚ -->

        <nav class="menu">


            <!-- INICIO ACTIVO -->

            <a
                href="dashboard.php"
                class="activo"
            >

                <span>⌂</span>

                Inicio

            </a>


            <!-- PRODUCTOS -->

            <a href="productos.php">

                <span>▣</span>

                Productos

            </a>


            <!-- INVENTARIO -->

            <a href="inventario.php">

                <span>▤</span>

                Inventario

            </a>


            <!-- VENTAS -->

            <a href="ventas.php">

                <span>$</span>

                Ventas

            </a>


            <!-- USUARIOS -->

            <?php if (
                isset($_SESSION["rol"]) &&
                $_SESSION["rol"] === "Administrador"
            ): ?>

                <a href="usuarios.php">

                    <span>♟</span>

                    Usuarios

                </a>

            <?php endif; ?>


        </nav>


        <!-- CERRAR SESIÓN -->

        <div class="salir">

            <a href="../logout.php">

                Cerrar sesión

            </a>

        </div>


    </aside>


    <!-- =========================================
         CONTENIDO
         ========================================= -->

    <main class="contenido">


        <!-- ENCABEZADO -->

        <header class="encabezado">


            <div>

                <h1>
                    Panel de administración
                </h1>

                <p>

                    Bienvenido,

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["nombre"]
                    );

                    ?>

                </p>

            </div>


            <!-- PERFIL -->

            <div class="perfil">


                <div class="avatar">

                    <?php

                    echo strtoupper(
                        substr(
                            $_SESSION["nombre"],
                            0,
                            1
                        )
                    );

                    ?>

                </div>


                <div>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $_SESSION["nombre"]
                        );

                        ?>

                    </strong>


                    <span>

                        <?php

                        echo htmlspecialchars(
                            $_SESSION["rol"]
                        );

                        ?>

                    </span>

                </div>


            </div>


        </header>


        <!-- =========================================
             TARJETAS
             ========================================= -->

        <section class="tarjetas">


            <!-- PRODUCTOS -->

            <div class="tarjeta">

                <div>

                    <span>
                        Productos
                    </span>

                    <h2>

                        <?php

                        echo $total_productos;

                        ?>

                    </h2>

                    <p>
                        Productos activos
                    </p>

                </div>


                <div class="icono">
                    ▣
                </div>

            </div>


            <!-- INVENTARIO -->

            <div class="tarjeta">

                <div>

                    <span>
                        Inventario
                    </span>

                    <h2>

                        <?php

                        echo $total_stock;

                        ?>

                    </h2>

                    <p>
                        Artículos disponibles
                    </p>

                </div>


                <div class="icono">
                    ▤
                </div>

            </div>


            <!-- VENTAS DE HOY -->

            <div class="tarjeta">

                <div>

                    <span>
                        Ventas de hoy
                    </span>

                    <h2>

                        $

                        <?php

                        echo number_format(
                            $dinero_ventas_hoy,
                            2
                        );

                        ?>

                    </h2>

                    <p>

                        <?php

                        echo $total_ventas_hoy;

                        ?>

                        venta(s) realizadas

                    </p>

                </div>


                <div class="icono">
                    $
                </div>

            </div>


            <!-- STOCK BAJO -->

            <div class="tarjeta">

                <div>

                    <span>
                        Stock bajo
                    </span>

                    <h2>

                        <?php

                        echo $total_stock_bajo;

                        ?>

                    </h2>

                    <p>
                        Productos con 5 o menos unidades
                    </p>

                </div>


                <div class="icono">
                    ⚠
                </div>

            </div>


            <!-- USUARIOS -->

            <div class="tarjeta">

                <div>

                    <span>
                        Usuarios
                    </span>

                    <h2>

                        <?php

                        echo $total_usuarios;

                        ?>

                    </h2>

                    <p>
                        Usuarios activos
                    </p>

                </div>


                <div class="icono">
                    ♙
                </div>

            </div>


        </section>


        <!-- =========================================
             ESTADÍSTICAS
             ========================================= -->

        <a
            href="estadisticas.php"
            class="boton-estadisticas"
        >

            <div>

                <h2>
                    📊 Ver estadísticas
                </h2>

                <p>
                    Consulta ventas, ganancias,
                    productos más vendidos y
                    rendimiento del personal.
                </p>

            </div>


            <div class="flecha-estadisticas">

                →

            </div>

        </a>


        <!-- =========================================
             ALERTA DE STOCK
             ========================================= -->

        <?php if (
            $total_stock_bajo > 0
        ): ?>

            <section class="stock-alerta">

                <h3>
                    ⚠️ Atención: stock bajo
                </h3>

                <p>

                    Hay

                    <strong>

                        <?php

                        echo $total_stock_bajo;

                        ?>

                    </strong>

                    producto(s) que necesitan reposición.

                </p>

            </section>

        <?php endif; ?>


        <!-- =========================================
             RESUMEN
             ========================================= -->

        <section
            class="panel-inferior"
            style="margin-top:25px;"
        >

            <div>

                <h2>
                    Resumen general
                </h2>

                <p>

                    Consulta rápidamente la información
                    principal de Changarro Súper y Más.

                </p>

            </div>


            <div class="estado">

                <span></span>

                Sistema activo

            </div>


        </section>


    </main>


</div>


</body>

</html>