<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/conexion.php";

$mensaje = "";

/* Obtener productos activos con stock */
$sql_productos = "
    SELECT id, nombre, precio_venta, stock
    FROM productos
    WHERE activo = 1
    AND stock > 0
    ORDER BY nombre ASC
";

$resultado_productos = $conexion->query($sql_productos);


/* =========================
   REGISTRAR VENTA
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $productos = $_POST["producto_id"] ?? [];
    $cantidades = $_POST["cantidad"] ?? [];
    $metodo_pago = $_POST["metodo_pago"] ?? "Efectivo";
    $efectivo_recibido = floatval($_POST["efectivo_recibido"] ?? 0);

    if (empty($productos)) {

        $mensaje = "Agrega al menos un producto.";

    } else {

        $conexion->begin_transaction();

        try {

            $total = 0;
            $detalles = [];

            /* =========================
               VALIDAR PRODUCTOS
               ========================= */

            for ($i = 0; $i < count($productos); $i++) {

                $producto_id = intval($productos[$i]);
                $cantidad = intval($cantidades[$i]);

                if ($producto_id <= 0 || $cantidad <= 0) {
                    throw new Exception("Hay un producto o cantidad inválida.");
                }

                /* Buscar producto */
                $sql_producto = "
                    SELECT id, nombre, precio_venta, stock
                    FROM productos
                    WHERE id = $producto_id
                    AND activo = 1
                ";

                $resultado_producto = $conexion->query($sql_producto);

                if ($resultado_producto->num_rows === 0) {
                    throw new Exception("Uno de los productos no existe.");
                }

                $producto = $resultado_producto->fetch_assoc();

                /* Comprobar stock */
                if ($cantidad > $producto["stock"]) {

                    throw new Exception(
                        "No hay suficiente stock de " .
                        $producto["nombre"] .
                        ". Disponible: " .
                        $producto["stock"]
                    );
                }

                $precio = floatval($producto["precio_venta"]);
                $subtotal = $precio * $cantidad;

                $total += $subtotal;

                $detalles[] = [
                    "id" => $producto_id,
                    "nombre" => $producto["nombre"],
                    "cantidad" => $cantidad,
                    "precio" => $precio,
                    "subtotal" => $subtotal,
                    "stock" => intval($producto["stock"])
                ];
            }


            /* =========================
               CREAR VENTA
               ========================= */

            $total = round($total, 2);
            /* Validar método de pago */

            if (!in_array($metodo_pago, ["Efectivo", "Tarjeta", "Transferencia"])) {
                throw new Exception("Método de pago no válido.");
            }


            /* Validar efectivo */

            if ($metodo_pago === "Efectivo") {

                if ($efectivo_recibido < $total) {

                    throw new Exception(
                        "El efectivo recibido es menor al total de la venta."
                    );
                }

            } else {

                $efectivo_recibido = 0;
            }

            $sql_venta = "
                INSERT INTO ventas
                (total, metodo_pago, efectivo_recibido)
                VALUES
                ($total, '$metodo_pago', $efectivo_recibido)"
            ;

            if (!$conexion->query($sql_venta)) {
                throw new Exception("Error al crear la venta.");
            }

            $venta_id = $conexion->insert_id;


            /* =========================
               GUARDAR DETALLES Y STOCK
               ========================= */

            foreach ($detalles as $detalle) {

                $producto_id = $detalle["id"];
                $cantidad = $detalle["cantidad"];
                $precio = $detalle["precio"];
                $subtotal = $detalle["subtotal"];
                $nuevo_stock = $detalle["stock"] - $cantidad;


                /* Guardar detalle de venta */

                $sql_detalle = "
                    INSERT INTO detalle_venta
                    (
                        venta_id,
                        producto_id,
                        cantidad,
                        precio,
                        subtotal
                    )
                    VALUES
                    (
                        $venta_id,
                        $producto_id,
                        $cantidad,
                        $precio,
                        $subtotal
                    )
                ";

                if (!$conexion->query($sql_detalle)) {
                    throw new Exception(
                        "Error al guardar el detalle de la venta."
                    );
                }


                /* Actualizar stock */

                $sql_stock = "
                    UPDATE productos
                    SET stock = $nuevo_stock
                    WHERE id = $producto_id
                ";

                if (!$conexion->query($sql_stock)) {
                    throw new Exception(
                        "Error al actualizar el stock."
                    );
                }


                /* Registrar salida */

                $motivo = "Venta #" . $venta_id;

                $sql_movimiento = "
                    INSERT INTO movimientos_inventario
                    (
                        producto_id,
                        tipo,
                        cantidad,
                        motivo
                    )
                    VALUES
                    (
                        $producto_id,
                        'Salida',
                        $cantidad,
                        '$motivo'
                    )
                ";

                if (!$conexion->query($sql_movimiento)) {
                    throw new Exception(
                        "Error al registrar el movimiento."
                    );
                }
            }


            /* =========================
               CONFIRMAR TODO
               ========================= */

            $conexion->commit();

            header("Location: detalle_venta.php?id=" . $venta_id);
            exit();

        } catch (Exception $e) {

            $conexion->rollback();

            $mensaje = $e->getMessage();
        }
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

    <title>Nueva venta | Changarro Súper y Más</title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

    <style>

        .venta-formulario {
            max-width: 900px;
        }

        .producto-row {
            display: grid;
            grid-template-columns: 1fr 120px 120px 45px;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }

        .producto-row select,
        .producto-row input {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
        }

        .btn-eliminar-producto {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 8px;
            background: #ffe8e8;
            color: #d33;
            cursor: pointer;
            font-size: 18px;
        }

        .btn-agregar-producto {
            margin-top: 10px;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            background: #eeeeee;
            cursor: pointer;
            font-weight: 600;
        }

        .venta-total {
            margin-top: 25px;
            padding: 20px;
            background: #f7f7f7;
            border-radius: 10px;
            text-align: right;
            font-size: 22px;
            font-weight: bold;
        }

        .acciones-venta {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .btn-registrar {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            background: #222;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancelar {
            padding: 12px 20px;
            border-radius: 8px;
            background: #eee;
            color: #222;
            text-decoration: none;
        }

        .mensaje-error {
            background: #ffe8e8;
            color: #b42318;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        @media (max-width: 700px) {

            .producto-row {
                grid-template-columns: 1fr;
            }

        }

        .pago-venta {
    margin-top: 25px;
    padding: 20px;
    background: #f7f7f7;
    border-radius: 10px;
    }

    .pago-venta label {
        display: block;
        margin-bottom: 7px;
        font-weight: 600;
    }

    .pago-venta select,
    .pago-venta input {
        width: 100%;
        box-sizing: border-box;
        padding: 11px;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .cambio-venta {
        font-size: 18px;
        font-weight: 700;
        text-align: right;
    }

    #cambioVenta {
        margin-left: 8px;
    }
    </style>

</head>

<body>

<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="contenido">

        <div class="encabezado">

            <div>

                <h1>Nueva venta</h1>

                <p>
                    Registra los productos de una nueva venta.
                </p>

            </div>

        </div>


        <section class="panel venta-formulario">

            <h2>Productos de la venta</h2>

            <?php if ($mensaje != ""): ?>

                <div class="mensaje-error">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>

            <?php endif; ?>


            <form method="POST" id="formVenta">

                <div id="productos-container">

                    <div class="producto-row">

                        <select
                            name="producto_id[]"
                            class="producto-select"
                            required
                        >

                            <option value="">
                                Selecciona un producto
                            </option>

                            <?php

                            $resultado_productos->data_seek(0);

                            while (
                                $producto =
                                $resultado_productos->fetch_assoc()
                            ):

                            ?>

                                <option
                                    value="<?php echo $producto["id"]; ?>"
                                    data-precio="<?php echo $producto["precio_venta"]; ?>"
                                    data-stock="<?php echo $producto["stock"]; ?>"
                                >

                                    <?php echo htmlspecialchars($producto["nombre"]); ?>

                                    -
                                    $<?php echo number_format(
                                        $producto["precio_venta"],
                                        2
                                    ); ?>

                                    -
                                    Stock:
                                    <?php echo $producto["stock"]; ?>

                                </option>

                            <?php endwhile; ?>

                        </select>


                        <input
                            type="number"
                            name="cantidad[]"
                            class="cantidad-input"
                            min="1"
                            value="1"
                            required
                        >


                        <input
                            type="text"
                            class="subtotal-input"
                            value="$0.00"
                            readonly
                        >


                        <button
                            type="button"
                            class="btn-eliminar-producto"
                            onclick="eliminarProducto(this)"
                        >
                            ×
                        </button>

                    </div>

                </div>


                <button
                    type="button"
                    class="btn-agregar-producto"
                    onclick="agregarProducto()"
                >
                    + Agregar producto
                </button>


                <div class="venta-total">

                    Total:
                    <span id="totalVenta">
                        $0.00
                    </span>

                </div>

                <div class="pago-venta">

    <label for="metodo_pago">
        Método de pago
    </label>

    <select name="metodo_pago" id="metodo_pago" required>

        <option value="Efectivo">
            Efectivo
        </option>

        <option value="Tarjeta">
            Tarjeta
        </option>

        <option value="Transferencia">
            Transferencia
        </option>

    </select>


    <div id="efectivo-container">

        <label for="efectivo_recibido">
            Efectivo recibido
        </label>

        <input
            type="number"
            name="efectivo_recibido"
            id="efectivo_recibido"
            min="0"
            step="0.01"
            placeholder="0.00"
        >

        <div class="cambio-venta">

            Cambio:
            <span id="cambioVenta">
                $0.00
            </span>

                </div>

            </div>

        </div>

                <div class="acciones-venta">

                    <button
                        type="submit"
                        class="btn-registrar"
                    >
                        Registrar venta
                    </button>

                    <a
                        href="ventas.php"
                        class="btn-cancelar"
                    >
                        Cancelar
                    </a>

                </div>

            </form>

        </section>

    </main>

</div>


<script>

function actualizarTotales() {

    let total = 0;

    const filas =
        document.querySelectorAll(".producto-row");

    filas.forEach(function(fila) {

        const select =
            fila.querySelector(".producto-select");

        const cantidad =
            fila.querySelector(".cantidad-input");

        const subtotal =
            fila.querySelector(".subtotal-input");

        const opcion =
            select.options[select.selectedIndex];

        if (
            opcion &&
            opcion.dataset.precio &&
            cantidad.value
        ) {

            const precio =
                parseFloat(opcion.dataset.precio);

            const cantidadValor =
                parseInt(cantidad.value);

            const subtotalValor =
                precio * cantidadValor;

            subtotal.value =
                "$" + subtotalValor.toFixed(2);

            total += subtotalValor;

        } else {

            subtotal.value = "$0.00";

        }

    });

    document.getElementById("totalVenta").textContent =
        "$" + total.toFixed(2);
}


function agregarProducto() {

    const container =
        document.getElementById("productos-container");

    const primeraFila =
        document.querySelector(".producto-row");

    const nuevaFila =
        primeraFila.cloneNode(true);

    nuevaFila
        .querySelector(".producto-select")
        .value = "";

    nuevaFila
        .querySelector(".cantidad-input")
        .value = 1;

    nuevaFila
        .querySelector(".subtotal-input")
        .value = "$0.00";

    container.appendChild(nuevaFila);

    actualizarTotales();
}


function eliminarProducto(boton) {

    const filas =
        document.querySelectorAll(".producto-row");

    if (filas.length === 1) {

        alert("Debe existir al menos un producto.");

        return;
    }

    boton
        .closest(".producto-row")
        .remove();

    actualizarTotales();
}


document.addEventListener(
    "change",
    function(evento) {

        if (
            evento.target.classList.contains(
                "producto-select"
            ) ||
            evento.target.classList.contains(
                "cantidad-input"
            )
        ) {

            actualizarTotales();

        }

    }
    );


    document.addEventListener(
        "input",
        function(evento) {

            if (
                evento.target.classList.contains(
                    "cantidad-input"
                )
            ) {

                actualizarTotales();

            }

        }
    );


    document.getElementById("formVenta")
    .addEventListener("submit", function(evento) {

        const filas =
            document.querySelectorAll(".producto-row");

        let productosSeleccionados = [];

        let error = false;

        filas.forEach(function(fila) {

            const select =
                fila.querySelector(".producto-select");

            const cantidad =
                fila.querySelector(".cantidad-input");

            const opcion =
                select.options[select.selectedIndex];

            if (!select.value) {

                error = true;

                return;

            }

            const stock =
                parseInt(opcion.dataset.stock);

            const cantidadValor =
                parseInt(cantidad.value);

            if (cantidadValor > stock) {

                alert(
                    "No hay suficiente stock de " +
                    opcion.text
                );

                error = true;

                return;

            }

            if (
                productosSeleccionados
                    .includes(select.value)
            ) {

                alert(
                    "No puedes agregar el mismo producto dos veces."
                );

                error = true;

                return;

            }

            productosSeleccionados.push(
                select.value
            );

        });

        if (error) {

            evento.preventDefault();

        }

    });


        actualizarTotales();

        const metodoPago =
        document.getElementById("metodo_pago");

    const efectivoRecibido =
        document.getElementById("efectivo_recibido");

    const efectivoContainer =
        document.getElementById("efectivo-container");

    const cambioVenta =
        document.getElementById("cambioVenta");


    function actualizarCambio() {

        const totalTexto =
            document.getElementById("totalVenta").textContent;

        const total =
            parseFloat(
                totalTexto.replace("$", "")
            ) || 0;

        const efectivo =
            parseFloat(
                efectivoRecibido.value
            ) || 0;

        const cambio =
            efectivo - total;

        if (cambio >= 0) {

            cambioVenta.textContent =
                "$" + cambio.toFixed(2);

        } else {

            cambioVenta.textContent =
                "$0.00";
        }
    }


    metodoPago.addEventListener(
        "change",
        function() {

            if (metodoPago.value === "Efectivo") {

                efectivoContainer.style.display = "block";

            } else {

                efectivoContainer.style.display = "none";

                efectivoRecibido.value = "";

                cambioVenta.textContent = "$0.00";
            }

        }
    );


    efectivoRecibido.addEventListener(
        "input",
        actualizarCambio
    );
</script>

</body>

</html>