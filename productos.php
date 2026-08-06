<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit();
}

require_once "config/conexion.php";

$sql = "SELECT * FROM productos ORDER BY id DESC";
$resultado = $conexion->query($sql);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Productos | Changarro Súper y Más</title>

    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

<div class="contenedor">

    <aside class="sidebar">

        <div class="marca">
            <h2>Changarro</h2>
            <span>Súper y Más</span>
        </div>

        <nav class="menu">

            <a href="dashboard.php">
                <span>⌂</span>
                Inicio
            </a>

            <a href="productos.php" class="activo">
                <span>▣</span>
                Productos
            </a>

            <a href="#">
                <span>▤</span>
                Inventario
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
            <a href="logout.php">Cerrar sesión</a>
        </div>

    </aside>


    <main class="contenido">

        <header class="encabezado">

            <div>
                <h1>Productos</h1>
                <p>Administra los productos de Changarro Súper y Más</p>
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


        <section class="panel-productos">

            <div class="cabecera-productos">

                <div>
                    <h2>Lista de productos</h2>
                    <p>Productos registrados en el sistema</p>
                </div>

                <a href="agregar_producto.php" class="btn-agregar">
                    + Agregar producto
                </a>

            </div>


            <div class="tabla-contenedor">

                <table>

                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Compra</th>
                            <th>Venta</th>
                            <th>Stock</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($resultado->num_rows > 0): ?>

                        <?php while ($producto = $resultado->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($producto["nombre"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($producto["categoria"]); ?>
                                </td>

                                <td>
                                    $<?php echo number_format($producto["precio_compra"], 2); ?>
                                </td>

                                <td>
                                    $<?php echo number_format($producto["precio_venta"], 2); ?>
                                </td>

                                <td>
                                    <?php echo $producto["stock"]; ?>
                                </td>

                                <td>
                                    <?php echo date("d/m/Y", strtotime($producto["fecha_registro"])); ?>
                                </td>

                                <td>
                                    <button class="btn-editar">Editar</button>
                                    <button class="btn-eliminar">Eliminar</button>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="sin-productos">
                                No hay productos registrados todavía.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

</body>
</html>