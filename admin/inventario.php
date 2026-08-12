<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/conexion.php";

/* PRODUCTOS ACTIVOS */
$sql_productos = "SELECT id, nombre, stock
                  FROM productos
                  WHERE activo = 1
                  ORDER BY nombre ASC";

$resultado_productos = $conexion->query($sql_productos);


/* MOVIMIENTOS */
$sql_movimientos = "SELECT 
                        movimientos_inventario.id,
                        productos.nombre,
                        movimientos_inventario.tipo,
                        movimientos_inventario.cantidad,
                        movimientos_inventario.motivo,
                        movimientos_inventario.fecha,
                        usuarios.nombre AS usuario_nombre
                    FROM movimientos_inventario
                    INNER JOIN productos
                    ON movimientos_inventario.producto_id = productos.id
                    LEFT JOIN usuarios
                    ON movimientos_inventario.usuario_id = usuarios.id
                    ORDER BY movimientos_inventario.fecha DESC";

$resultado_movimientos = $conexion->query($sql_movimientos);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Inventario | Changarro Súper y Más</title>

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

            <a href="dashboard.php">
                ⌂ Inicio
            </a>

            <a href="productos.php">
                ▣ Productos
            </a>

            <a href="inventario.php" class="activo">
                ▤ Inventario
            </a>

            <a href="ventas.php">
                $ Ventas
            </a>

            <?php if (
            isset($_SESSION["rol"]) &&
            $_SESSION["rol"] === "Administrador"
        ): ?>

            <a href="usuarios.php">
                ♟ Usuarios
            </a>

        <?php endif; ?>

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

                <h1>Inventario</h1>

                <p>
                    Controla las entradas y salidas de productos.
                </p>

            </div>


            <div class="perfil">

                <div class="avatar">
                    <?php echo strtoupper(substr($_SESSION["nombre"], 0, 1)); ?>
                </div>

                <div>

                    <strong>
                        <?php echo $_SESSION["nombre"]; ?>
                    </strong>

                    <br>

                    <span>
                        <?php echo $_SESSION["rol"]; ?>
                    </span>

                </div>

            </div>

        </header>


        <!-- RESUMEN -->

        <section class="inventario-resumen">

            <?php

            $total_productos = 0;
            $total_stock = 0;

            while ($producto = $resultado_productos->fetch_assoc()) {

                $total_productos++;
                $total_stock += $producto["stock"];

            }

            ?>

            <div class="inventario-card">

                <span>Productos activos</span>

                <strong>
                    <?php echo $total_productos; ?>
                </strong>

                <small>
                    Productos disponibles
                </small>

            </div>


            <div class="inventario-card">

                <span>Stock total</span>

                <strong>
                    <?php echo $total_stock; ?>
                </strong>

                <small>
                    Artículos disponibles
                </small>

            </div>

        </section>


        <!-- MOVIMIENTOS -->

        <section class="inventario-panel">

            <div class="inventario-panel-header">

                <div>

                    <h2>Movimientos de inventario</h2>

                    <p>
                        Historial de entradas y salidas.
                    </p>

                </div>

                <button class="btn-movimiento" onclick="document.getElementById('formMovimiento').style.display='block'">

                    + Registrar movimiento

                </button>

            </div>


            <!-- FORMULARIO -->

            <div id="formMovimiento" style="display:none;">

                <form class="form-movimiento" method="POST" action="movimiento_inventario.php">

                    <label>
                        Producto
                    </label>

                    <select name="producto_id" required>

                        <option value="">
                            Selecciona un producto
                        </option>

                        <?php

                        $productos_form = $conexion->query(
                            "SELECT id, nombre, stock
                             FROM productos
                             WHERE activo = 1
                             ORDER BY nombre"
                        );

                        while ($p = $productos_form->fetch_assoc()):

                        ?>

                            <option value="<?php echo $p["id"]; ?>">

                                <?php echo htmlspecialchars($p["nombre"]); ?>

                                - Stock:
                                <?php echo $p["stock"]; ?>

                            </option>

                        <?php endwhile; ?>

                    </select>


                    <label>
                        Tipo de movimiento
                    </label>

                    <select name="tipo" required>

                        <option value="">
                            Selecciona
                        </option>

                        <option value="entrada">
                            Entrada
                        </option>

                        <option value="salida">
                            Salida
                        </option>

                    </select>


                    <label>
                        Cantidad
                    </label>

                    <input
                        type="number"
                        name="cantidad"
                        min="1"
                        required
                    >


                    <label>
                        Motivo
                    </label>

                    <input
                        type="text"
                        name="motivo"
                        placeholder="Ej. Compra de mercancía"
                    >


                    <button type="submit">
                        Guardar movimiento
                    </button>

                </form>

            </div>


            <!-- TABLA -->

            <table class="inventario-tabla">

                <thead>

                    <tr>

                        <th>Producto</th>

                        <th>Tipo</th>

                        <th>Cantidad</th>

                        <th>Motivo</th>

                        <th>Usuario</th>

                        <th>Fecha</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($resultado_movimientos->num_rows > 0): ?>

                    <?php while ($movimiento = $resultado_movimientos->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($movimiento["nombre"]); ?>
                            </td>

                            <td>

                                <?php if ($movimiento["tipo"] == "entrada"): ?>

                                    <strong>
                                        Entrada
                                    </strong>

                                <?php else: ?>

                                    <strong>
                                        Salida
                                    </strong>

                                <?php endif; ?>

                            </td>

                            <td>

                                <?php

                                if ($movimiento["tipo"] == "entrada") {

                                    echo "+";

                                } else {

                                    echo "-";

                                }

                                ?>

                                <?php echo $movimiento["cantidad"]; ?>

                            </td>

                            <td>
                                <?php echo htmlspecialchars($movimiento["motivo"]); ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $movimiento["usuario_nombre"]
                                    ?? "No disponible"
                                );
                                ?>
                            </td>

                            <td>
                                <?php echo $movimiento["fecha"]; ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="6">

                            No hay movimientos registrados.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </section>

    </main>

</div>

</body>

</html>