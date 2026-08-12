<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/conexion.php";

/* TOTAL DE PRODUCTOS */
$sql_productos = "SELECT COUNT(*) AS total FROM productos WHERE activo = 1";
$resultado_productos = $conexion->query($sql_productos);
$total_productos = $resultado_productos->fetch_assoc()["total"];


/* TOTAL DE ARTÍCULOS EN INVENTARIO */
$sql_stock = "SELECT COALESCE(SUM(stock), 0) AS total FROM productos WHERE activo = 1";
$resultado_stock = $conexion->query($sql_stock);
$total_stock = $resultado_stock->fetch_assoc()["total"];


/* TOTAL DE USUARIOS */
$sql_usuarios = "SELECT COUNT(*) AS total FROM usuarios";
$resultado_usuarios = $conexion->query($sql_usuarios);
$total_usuarios = $resultado_usuarios->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel | Changarro Súper y Más</title>

    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>

<div class="contenedor">

    <!-- MENÚ LATERAL -->
    <aside class="sidebar">

        <div class="marca">
            <h2>Changarro</h2>
            <span>Súper y Más</span>
        </div>

        <nav class="menu">

            <a href="#" class="activo">
                <span>⌂</span>
                Inicio
            </a>

            <a href="productos.php">
                <span>▣</span>
                Productos
            </a>

            <a href="inventario.php">
                ▤ Inventario
            </a>

            <a href="#">
                <span>$</span>
                Ventas
            </a>

            <a href="#">
                <span>♙</span>
                Usuarios
            </a>

        </nav>

        <div class="salir">
            <a href="../logout.php">
                Cerrar sesión
            </a>
        </div>

    </aside>


    <!-- CONTENIDO -->
    <main class="contenido">

        <header class="encabezado">

            <div>
                <h1>Panel de administración</h1>
                <p>
                    Bienvenido,
                    <?php echo htmlspecialchars($_SESSION["nombre"]); ?>
                </p>
            </div>

            <div class="perfil">
                <div class="avatar">
                    <?php echo strtoupper(substr($_SESSION["nombre"], 0, 1)); ?>
                </div>

                <div>
                    <strong>
                        <?php echo htmlspecialchars($_SESSION["nombre"]); ?>
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($_SESSION["rol"]); ?>
                    </span>
                </div>
            </div>

        </header>


        <section class="tarjetas">

            <div class="tarjeta">
                <div>
                    <span>Productos</span>
                    <h2><?php echo $total_productos; ?></h2>
                    <p>Productos registrados</p>
                </div>

                <div class="icono">▣</div>
            </div>


            <div class="tarjeta">
                <div>
                    <span>Inventario</span>
                    <h2><?php echo $total_stock; ?></h2>
                    <p>Artículos disponibles</p>
                </div>

                <div class="icono">▤</div>
            </div>


            <div class="tarjeta">
                <div>
                    <span>Ventas</span>
                    <h2>$0</h2>
                    <p>Ventas registradas</p>
                </div>

                <div class="icono">$</div>
            </div>


            <div class="tarjeta">
                <div>
                    <span>Usuarios</span>
                    <h2><?php echo $total_usuarios; ?></h2>
                    <p>Usuarios del sistema</p>
                </div>

                <div class="icono">♙</div>
            </div>

        </section>


        <section class="panel-inferior">

            <div>
                <h2>Resumen general</h2>
                <p>
                    Consulta rápidamente la información principal
                    de Changarro Súper y Más.
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