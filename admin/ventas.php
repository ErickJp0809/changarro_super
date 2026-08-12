<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/conexion.php";

/* Obtener ventas */
$sql = "SELECT
            ventas.id,
            ventas.total,
            ventas.fecha,
            ventas.estado,
            usuarios.nombre AS usuario_nombre
        FROM ventas
        LEFT JOIN usuarios
            ON ventas.usuario_id = usuarios.id
        ORDER BY ventas.id DESC";

$resultado = $conexion->query($sql);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ventas | Changarro Súper y Más</title>

    <link rel="stylesheet" href="../css/dashboard.css">

</head>

<body>

<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="contenido">

        <div class="encabezado">

            <div>
                <h1>Ventas</h1>
                <p>Consulta y administra las ventas realizadas.</p>
            </div>

            <div class="usuario">

                <div class="avatar">
                    A
                </div>

                <div>
                    <strong>Administrador</strong>
                    <span>Administrador</span>
                </div>

            </div>

        </div>


        <section class="panel">

            <div class="panel-header">

                <div>
                    <h2>Historial de ventas</h2>
                    <p>Ventas registradas en el sistema.</p>
                </div>

                <a href="crear_venta.php" class="btn-principal">
                    + Nueva venta
                </a>

            </div>


            <table class="tabla">

                <thead>

                    <tr>
                        <th>Folio</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Realizada por</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if ($resultado->num_rows > 0): ?>

                        <?php while ($venta = $resultado->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    #<?php echo $venta["id"]; ?>
                                </td>

                                <td>
                                    $<?php echo number_format($venta["total"], 2); ?>
                                </td>

                                <td>
                                    <?php echo $venta["fecha"]; ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $venta["usuario_nombre"] ?? "No disponible"
                                    );
                                    ?>
                                </td>

                                <td>

                                    <?php if ($venta["estado"] === "Cancelada"): ?>

                                        <span class="estado-inactivo">
                                            ● Cancelada
                                        </span>

                                    <?php else: ?>

                                        <span class="estado-activo">
                                            ● Completada
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <a
                                        href="detalle_venta.php?id=<?php echo $venta["id"]; ?>"
                                        class="btn-ver">
                                        Ver
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="6" class="sin-datos">
                                No hay ventas registradas todavía.
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