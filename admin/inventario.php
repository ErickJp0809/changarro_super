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
    SELECT id, nombre, stock
    FROM productos
    WHERE activo = 1
    ORDER BY nombre ASC
";

$resultado_productos =
    $conexion->query($sql_productos);


/* =========================================
   MOVIMIENTOS DE INVENTARIO
   ========================================= */

$sql_movimientos = "
    SELECT
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

    ORDER BY movimientos_inventario.fecha DESC
";

$resultado_movimientos =
    $conexion->query($sql_movimientos);

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
        Inventario | Changarro Súper y Más
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

</head>


<body>


<div class="contenedor">


    <!-- =========================================
         SIDEBAR
         ========================================= -->

    <?php include "../includes/sidebar.php"; ?>


    <!-- =========================================
         CONTENIDO
         ========================================= -->

    <main class="contenido">


        <!-- ENCABEZADO -->

        <header class="encabezado">


            <div>

                <h1>
                    Inventario
                </h1>

                <p>
                    Controla las entradas y salidas de productos.
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
             RESUMEN DEL INVENTARIO
             ========================================= -->

        <?php

        $total_productos = 0;
        $total_stock = 0;


        while (
            $producto =
            $resultado_productos->fetch_assoc()
        ) {

            $total_productos++;

            $total_stock +=
                $producto["stock"];

        }

        ?>


        <section class="inventario-resumen">


            <!-- PRODUCTOS ACTIVOS -->

            <div class="inventario-card">

                <span class="titulo">
                    Productos activos
                </span>

                <strong class="numero">

                    <?php

                    echo $total_productos;

                    ?>

                </strong>

                <span class="descripcion">
                    Productos disponibles
                </span>

            </div>


            <!-- STOCK TOTAL -->

            <div class="inventario-card">

                <span class="titulo">
                    Stock total
                </span>

                <strong class="numero">

                    <?php

                    echo $total_stock;

                    ?>

                </strong>

                <span class="descripcion">
                    Artículos disponibles
                </span>

            </div>


        </section>


        <!-- =========================================
             PANEL DE MOVIMIENTOS
             ========================================= -->

        <section class="inventario-panel">


            <!-- CABECERA -->

            <div class="inventario-panel-header">


                <div>

                    <h2>
                        Movimientos de inventario
                    </h2>

                    <p>
                        Historial de entradas y salidas.
                    </p>

                </div>


                <button
                    type="button"
                    class="btn-movimiento"
                    onclick="
                        document.getElementById('formMovimiento').style.display =
                        document.getElementById('formMovimiento').style.display === 'none'
                        ? 'block'
                        : 'none';
                    "
                >

                    + Registrar movimiento

                </button>


            </div>


            <!-- =========================================
                 FORMULARIO
                 ========================================= -->

            <div
                id="formMovimiento"
                style="display:none;"
            >


                <form
                    class="form-movimiento"
                    method="POST"
                    action="movimiento_inventario.php"
                >


                    <!-- PRODUCTO -->

                    <label>
                        Producto
                    </label>


                    <select
                        name="producto_id"
                        required
                    >

                        <option value="">
                            Selecciona un producto
                        </option>


                        <?php

                        $productos_form =
                            $conexion->query(
                                "
                                SELECT id, nombre, stock
                                FROM productos
                                WHERE activo = 1
                                ORDER BY nombre
                                "
                            );


                        while (
                            $p =
                            $productos_form->fetch_assoc()
                        ):

                        ?>


                            <option
                                value="<?php echo $p["id"]; ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $p["nombre"]
                                );

                                ?>

                                - Stock:

                                <?php

                                echo $p["stock"];

                                ?>

                            </option>


                        <?php endwhile; ?>


                    </select>


                    <!-- TIPO -->

                    <label>
                        Tipo de movimiento
                    </label>


                    <select
                        name="tipo"
                        required
                    >

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


                    <!-- CANTIDAD -->

                    <label>
                        Cantidad
                    </label>


                    <input
                        type="number"
                        name="cantidad"
                        min="1"
                        required
                    >


                    <!-- MOTIVO -->

                    <label>
                        Motivo
                    </label>


                    <input
                        type="text"
                        name="motivo"
                        placeholder="Ej. Compra de mercancía"
                    >


                    <!-- GUARDAR -->

                    <button
                        type="submit"
                    >

                        Guardar movimiento

                    </button>


                </form>


            </div>


            <!-- =========================================
                 TABLA DE MOVIMIENTOS
                 ========================================= -->

            <div
                class="tabla-contenedor"
            >


                <table
                    class="inventario-tabla"
                >


                    <thead>

                        <tr>

                            <th>
                                Producto
                            </th>

                            <th>
                                Tipo
                            </th>

                            <th>
                                Cantidad
                            </th>

                            <th>
                                Motivo
                            </th>

                            <th>
                                Realizado por
                            </th>

                            <th>
                                Fecha
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $resultado_movimientos &&
                        $resultado_movimientos->num_rows > 0
                    ): ?>


                        <?php while (
                            $movimiento =
                            $resultado_movimientos->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- PRODUCTO -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $movimiento["nombre"]
                                    );

                                    ?>

                                </td>


                                <!-- TIPO -->

                                <td>


                                    <?php if (
                                        $movimiento["tipo"]
                                        === "entrada"
                                    ): ?>


                                        <span
                                            class="movimiento-entrada"
                                        >

                                            Entrada

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="movimiento-salida"
                                        >

                                            Salida

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- CANTIDAD -->

                                <td>


                                    <?php if (
                                        $movimiento["tipo"]
                                        === "entrada"
                                    ): ?>

                                        <span
                                            class="movimiento-entrada"
                                        >

                                            +

                                            <?php

                                            echo $movimiento[
                                                "cantidad"
                                            ];

                                            ?>

                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="movimiento-salida"
                                        >

                                            -

                                            <?php

                                            echo $movimiento[
                                                "cantidad"
                                            ];

                                            ?>

                                        </span>

                                    <?php endif; ?>


                                </td>


                                <!-- MOTIVO -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $movimiento["motivo"]
                                        ?? ""
                                    );

                                    ?>

                                </td>


                                <!-- USUARIO -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $movimiento[
                                            "usuario_nombre"
                                        ]
                                        ?? "No disponible"
                                    );

                                    ?>

                                </td>


                                <!-- FECHA -->

                                <td>

                                    <?php

                                    echo $movimiento[
                                        "fecha"
                                    ];

                                    ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="6"
                                class="sin-datos"
                            >

                                No hay movimientos registrados.

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