<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/conexion.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: ventas.php");
    exit();
}

$venta_id = intval($_GET["id"]);


/* Información de la venta */

$sql_venta = "
    SELECT
        ventas.id,
        ventas.total,
        ventas.metodo_pago,
        ventas.efectivo_recibido,
        ventas.fecha,
        ventas.estado,
        usuarios.nombre AS usuario_nombre
    FROM ventas
    LEFT JOIN usuarios
        ON ventas.usuario_id = usuarios.id
    WHERE ventas.id = $venta_id
";

$resultado_venta = $conexion->query($sql_venta);

if ($resultado_venta->num_rows === 0) {
    die("La venta no existe.");
}

$venta = $resultado_venta->fetch_assoc();


/* Productos vendidos */

$sql_detalle = "
    SELECT
        detalle_venta.cantidad,
        detalle_venta.precio,
        detalle_venta.subtotal,
        productos.nombre
    FROM detalle_venta
    INNER JOIN productos
        ON detalle_venta.producto_id = productos.id
    WHERE detalle_venta.venta_id = $venta_id
";

$resultado_detalle = $conexion->query($sql_detalle);


/* Calcular cambio */

$cambio = 0;

if ($venta["metodo_pago"] === "Efectivo") {

    $cambio =
        $venta["efectivo_recibido"] -
        $venta["total"];
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
        Venta #<?php echo $venta["id"]; ?>
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

    <style>

        .ticket-contenedor {
            max-width: 500px;
            margin: 0 auto;
        }

        .ticket {
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 3px 12px rgba(0,0,0,.05);
        }

        .ticket-encabezado {
            text-align: center;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .ticket-encabezado h2 {
            margin: 0;
            font-size: 24px;
        }

        .ticket-encabezado p {
            margin: 5px 0;
            color: #777;
            font-size: 13px;
        }

        .ticket-info {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .ticket-tabla {
            width: 100%;
            border-collapse: collapse;
        }

        .ticket-tabla th {
            text-align: left;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        .ticket-tabla td {
            padding: 9px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .ticket-tabla .derecha {
            text-align: right;
        }

        .ticket-total {
            border-top: 2px solid #222;
            margin-top: 15px;
            padding-top: 15px;
            text-align: right;
            font-size: 22px;
            font-weight: 700;
        }

        .ticket-pago {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #ccc;
            font-size: 14px;
        }

        .ticket-pago div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 7px;
        }

        .ticket-final {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px dashed #ccc;
            color: #777;
            font-size: 13px;
        }

        .ticket-acciones {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-imprimir,
        .btn-regresar {
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-imprimir {
            background: #222;
            color: white;
        }

        .btn-regresar {
            background: #eee;
            color: #222;
        }


        /* =========================
           IMPRESIÓN
           ========================= */

        @media print {

            body {
                background: white;
            }

            .sidebar,
            .encabezado,
            .ticket-acciones {
                display: none !important;
            }

            .contenido {
                margin: 0;
                padding: 0;
            }

            .ticket-contenedor {
                max-width: 100%;
            }

            .ticket {
                border: none;
                box-shadow: none;
                padding: 0;
            }

        }
        .btn-cancelar-venta {
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
            background: #ffe8e8;
            color: #c62828;
            font-weight: 600;
        }

        .btn-cancelar-venta:hover {
            background: #ffd6d6;
        }

        .venta-cancelada {
            padding: 11px 18px;
            border-radius: 8px;
            background: #ffe8e8;
            color: #c62828;
            font-weight: 700;
        }
    </style>

</head>


<body>

<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>


    <main class="contenido">

        <div class="encabezado">

            <div>

                <h1>Comprobante de venta</h1>

                
                <p>
                    Venta #<?php echo $venta["id"]; ?>
                </p>

                <p>
                    Realizada por:
                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $venta["usuario_nombre"] ?? "Usuario no disponible"
                        );
                        ?>
                    </strong>
                </p>

                <p>
                    Estado:

                    <strong>
                        <?php echo htmlspecialchars($venta["estado"]); ?>
                    </strong>
                </p>
            </div>

        </div>


        <div class="ticket-contenedor">

            <div class="ticket">


                <!-- ENCABEZADO DEL TICKET -->

                <div class="ticket-encabezado">

                    <h2>
                        CHANGARRO
                    </h2>

                    <p>
                        SÚPER Y MÁS
                    </p>

                    <p>
                        Venta #<?php echo $venta["id"]; ?>
                    </p>

                    <p>
                        <?php echo $venta["fecha"]; ?>
                    </p>

                </div>


                <!-- PRODUCTOS -->

                <table class="ticket-tabla">

                    <thead>

                        <tr>

                            <th>
                                Producto
                            </th>

                            <th>
                                Cant.
                            </th>

                            <th class="derecha">
                                Subtotal
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while (
                        $detalle =
                        $resultado_detalle->fetch_assoc()
                    ): ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $detalle["nombre"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo $detalle["cantidad"];
                                ?>
                            </td>

                            <td class="derecha">

                                $
                                <?php
                                echo number_format(
                                    $detalle["subtotal"],
                                    2
                                );
                                ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>


                <!-- TOTAL -->

                <div class="ticket-total">

                    Total:
                    $
                    <?php
                    echo number_format(
                        $venta["total"],
                        2
                    );
                    ?>

                </div>


                <!-- PAGO -->

                <div class="ticket-pago">

                    <div>

                        <span>
                            Método de pago
                        </span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $venta["metodo_pago"]
                            );
                            ?>
                        </strong>

                    </div>


                    <?php if (
                        $venta["metodo_pago"] === "Efectivo"
                    ): ?>

                        <div>

                            <span>
                                Recibido
                            </span>

                            <strong>
                                $
                                <?php
                                echo number_format(
                                    $venta["efectivo_recibido"],
                                    2
                                );
                                ?>
                            </strong>

                        </div>


                        <div>

                            <span>
                                Cambio
                            </span>

                            <strong>
                                $
                                <?php
                                echo number_format(
                                    $cambio,
                                    2
                                );
                                ?>
                            </strong>

                        </div>

                    <?php endif; ?>

                </div>


                <!-- MENSAJE FINAL -->

                <div class="ticket-final">

                    ¡Gracias por su compra!

                </div>


            </div>


            <!-- BOTONES -->

            <div class="ticket-acciones">

                <button
                    class="btn-imprimir"
                    onclick="window.print()"
                >
                    🖨 Imprimir ticket
                </button>

                <?php if ($venta["estado"] === "Completada"): ?>

                    <a
                        href="cancelar_venta.php?id=<?php echo $venta["id"]; ?>"
                        class="btn-cancelar-venta"
                        onclick="return confirm('¿Seguro que deseas cancelar esta venta? El stock será devuelto al inventario.');"
                    >
                        ✕ Cancelar venta
                    </a>

                <?php else: ?>

                    <span class="venta-cancelada">
                        Venta cancelada
                    </span>

                <?php endif; ?>


                <a
                    href="ventas.php"
                    class="btn-regresar"
                >
                    ← Volver a ventas
                </a>

            </div>

        </div>

    </main>

</div>

</body>

</html>