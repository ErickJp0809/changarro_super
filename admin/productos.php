<?php

require_once "verificar_acceso.php";
require_once "../config/conexion.php";


/* =========================================
   PRODUCTOS ACTIVOS
   ========================================= */

$sql = "
    SELECT
        id,
        codigo,
        nombre,
        categoria,
        precio_compra,
        precio_venta,
        stock,
        fecha_registro
    FROM productos
    WHERE activo = 1
    ORDER BY id DESC
";

$resultado = $conexion->query($sql);


/* =========================================
   PRODUCTOS DESACTIVADOS
   ========================================= */

$sql_desactivados = "
    SELECT
        id,
        codigo,
        nombre,
        categoria
    FROM productos
    WHERE activo = 0
    ORDER BY nombre ASC
";

$resultado_desactivados =
    $conexion->query($sql_desactivados);

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
        href="../css/dashboard.css"
    >


    <style>

        /* =========================================
           ACCIONES
           ========================================= */

        .acciones-producto {

            display: flex;

            align-items: center;

            gap: 7px;

            flex-wrap: wrap;

        }


        .btn-accion {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 7px 10px;

            border-radius: 7px;

            text-decoration: none;

            font-size: 12px;

            font-weight: 600;

            white-space: nowrap;

            border: none;

            cursor: pointer;

        }


        .btn-editar-producto {

            background: #eeeeee;

            color: #222;

        }


        .btn-stock {

            background: #e8f8ef;

            color: #16834a;

        }


        .btn-desactivar {

            background: #ffe8e8;

            color: #c62828;

        }


        .btn-reactivar {

            background: #e8f8ef;

            color: #16834a;

        }


        .btn-accion:hover {

            opacity: 0.85;

        }


        /* =========================================
           STOCK
           ========================================= */

        .stock-indicador {

            display: flex;

            align-items: center;

            gap: 8px;

        }


        .stock-numero {

            font-weight: 700;

        }


        .stock-foco {

            width: 9px;

            height: 9px;

            border-radius: 50%;

            display: inline-block;

        }


        .stock-foco.rojo {

            background: #d64545;

        }


        .stock-foco.amarillo {

            background: #e5a900;

        }


        .stock-foco.verde {

            background: #22a35a;

        }


        /* =========================================
           MENSAJES
           ========================================= */

        .mensaje-exito {

            background: #e8f8ef;

            color: #16834a;

            padding: 12px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-weight: 600;

        }


        .mensaje-error {

            background: #ffe8e8;

            color: #b42318;

            padding: 12px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-weight: 600;

        }


        /* =========================================
           SIN PRODUCTOS
           ========================================= */

        .sin-productos {

            text-align: center;

            padding: 40px !important;

            color: #888;

        }


        /* =========================================
           SEPARACIÓN
           ========================================= */

        .separacion-productos {

            margin-top: 30px;

        }


        /* =========================================
           MODAL STOCK
           ========================================= */

        .modal-stock {

            display: none;

            position: fixed;

            inset: 0;

            z-index: 9999;

            background: rgba(0, 0, 0, 0.45);

            align-items: center;

            justify-content: center;

            padding: 20px;

        }


        .modal-stock.activo {

            display: flex;

        }


        .modal-contenido {

            width: 100%;

            max-width: 420px;

            background: white;

            border-radius: 14px;

            box-shadow:
                0 15px 45px
                rgba(0, 0, 0, 0.20);

            overflow: hidden;

        }


        /* =========================================
           CABECERA MODAL
           ========================================= */

        .modal-cabecera {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 18px 20px;

            border-bottom: 1px solid #eeeeee;

        }


        .modal-cabecera h2 {

            margin: 0;

            font-size: 20px;

        }


        .btn-cerrar-modal {

            border: none;

            background: transparent;

            font-size: 22px;

            cursor: pointer;

            color: #777;

            padding: 3px 7px;

            border-radius: 6px;

        }


        .btn-cerrar-modal:hover {

            background: #f1f1f1;

        }


        /* =========================================
           CUERPO MODAL
           ========================================= */

        .modal-cuerpo {

            padding: 20px;

        }


        .producto-modal {

            background: #f7f7f7;

            border: 1px solid #e5e5e5;

            border-radius: 10px;

            padding: 14px;

            margin-bottom: 20px;

        }


        .producto-modal strong {

            display: block;

            font-size: 17px;

            margin-bottom: 5px;

        }


        .producto-modal span {

            color: #777;

            font-size: 13px;

        }


        .campo-modal label {

            display: block;

            font-weight: 600;

            margin-bottom: 7px;

        }


        .campo-modal input {

            width: 100%;

            box-sizing: border-box;

            padding: 12px;

            border: 1px solid #d8d8d8;

            border-radius: 8px;

            font-size: 16px;

            outline: none;

        }


        .campo-modal input:focus {

            border-color: #16834a;

        }


        /* =========================================
           BOTONES MODAL
           ========================================= */

        .modal-acciones {

            display: flex;

            justify-content: flex-end;

            gap: 10px;

            margin-top: 22px;

        }


        .btn-modal-cancelar {

            padding: 11px 17px;

            border: none;

            border-radius: 8px;

            background: #eeeeee;

            color: #222;

            cursor: pointer;

            font-weight: 600;

        }


        .btn-modal-guardar {

            padding: 11px 17px;

            border: none;

            border-radius: 8px;

            background: #16834a;

            color: white;

            cursor: pointer;

            font-weight: 600;

        }


        .btn-modal-guardar:hover {

            background: #126d3d;

        }


        /* =========================================
           RESPONSIVE
           ========================================= */

        @media (max-width: 1100px) {

            .tabla-contenedor {

                overflow-x: auto;

            }

        }

    </style>

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


        <!-- =====================================
             ENCABEZADO
             ===================================== -->

        <header class="encabezado">


            <div>

                <h1>
                    Productos
                </h1>


                <p>
                    Administra los productos
                    de Changarro Súper y Más.
                </p>

            </div>


            <!-- PERFIL -->

            <div class="perfil">


                <div class="avatar">

                    <?php

                    echo htmlspecialchars(
                        strtoupper(
                            substr(
                                $_SESSION["nombre"],
                                0,
                                1
                            )
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


        <!-- =====================================
             MENSAJES
             ===================================== -->

        <?php if (
            isset($_GET["stock"]) &&
            $_GET["stock"] === "ok"
        ): ?>


            <div class="mensaje-exito">

                ✓ Stock agregado correctamente.

            </div>


        <?php endif; ?>


        <?php if (
            isset($_GET["desactivado"]) &&
            $_GET["desactivado"] === "ok"
        ): ?>


            <div class="mensaje-exito">

                ✓ Producto desactivado correctamente.

            </div>


        <?php endif; ?>


        <?php if (
            isset($_GET["reactivado"]) &&
            $_GET["reactivado"] === "ok"
        ): ?>


            <div class="mensaje-exito">

                ✓ Producto reactivado correctamente.

            </div>


        <?php endif; ?>


        <?php if (
            isset($_GET["error"])
        ): ?>


            <div class="mensaje-error">

                ✕

                <?php

                echo htmlspecialchars(
                    $_GET["error"]
                );

                ?>

            </div>


        <?php endif; ?>


        <!-- =====================================
             PRODUCTOS ACTIVOS
             ===================================== -->

        <section class="panel-productos">


            <!-- CABECERA -->

            <div class="cabecera-productos">


                <div>

                    <h2>
                        Lista de productos
                    </h2>


                    <p>
                        Productos disponibles
                        en la tienda.
                    </p>

                </div>


                <a
                    href="agregar_producto.php"
                    class="btn-agregar"
                >

                    + Nuevo producto

                </a>


            </div>


            <!-- TABLA -->

            <div class="tabla-contenedor">


                <table>


                    <thead>

                        <tr>

                            <th>
                                Código
                            </th>

                            <th>
                                Producto
                            </th>

                            <th>
                                Categoría
                            </th>

                            <th>
                                Compra
                            </th>

                            <th>
                                Venta
                            </th>

                            <th>
                                Stock
                            </th>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $resultado &&
                        $resultado->num_rows > 0
                    ): ?>


                        <?php while (
                            $producto =
                            $resultado->fetch_assoc()
                        ): ?>


                            <?php

                            $stock =
                                intval(
                                    $producto["stock"]
                                );


                            if (
                                $stock <= 0
                            ) {

                                $estado_stock =
                                    "rojo";

                            } elseif (
                                $stock <= 10
                            ) {

                                $estado_stock =
                                    "amarillo";

                            } else {

                                $estado_stock =
                                    "verde";

                            }

                            ?>


                            <tr>


                                <!-- CÓDIGO -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $producto["codigo"]
                                    );

                                    ?>

                                </td>


                                <!-- PRODUCTO -->

                                <td>

                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $producto["nombre"]
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <!-- CATEGORÍA -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $producto["categoria"]
                                    );

                                    ?>

                                </td>


                                <!-- COMPRA -->

                                <td>

                                    $

                                    <?php

                                    echo number_format(
                                        $producto[
                                            "precio_compra"
                                        ],
                                        2
                                    );

                                    ?>

                                </td>


                                <!-- VENTA -->

                                <td>

                                    $

                                    <?php

                                    echo number_format(
                                        $producto[
                                            "precio_venta"
                                        ],
                                        2
                                    );

                                    ?>

                                </td>


                                <!-- STOCK -->

                                <td>

                                    <div
                                        class="stock-indicador"
                                    >

                                        <span
                                            class="stock-numero"
                                        >

                                            <?php

                                            echo $stock;

                                            ?>

                                        </span>


                                        <span
                                            class="
                                                stock-foco
                                                <?php

                                                echo $estado_stock;

                                                ?>
                                            "
                                        >
                                        </span>

                                    </div>

                                </td>


                                <!-- FECHA -->

                                <td>

                                    <?php

                                    echo date(
                                        "d/m/Y",
                                        strtotime(
                                            $producto[
                                                "fecha_registro"
                                            ]
                                        )
                                    );

                                    ?>

                                </td>


                                <!-- ACCIONES -->

                                <td>


                                    <div
                                        class="acciones-producto"
                                    >


                                        <!-- EDITAR -->

                                        <a
                                            href="editar_producto.php?id=<?php

                                                echo intval(
                                                    $producto["id"]
                                                );

                                            ?>"
                                            class="
                                                btn-accion
                                                btn-editar-producto
                                            "
                                        >

                                            ✏️ Editar

                                        </a>


                                        <!-- STOCK -->

                                        <button
                                            type="button"
                                            class="
                                                btn-accion
                                                btn-stock
                                            "
                                            onclick="abrirModalStock(
                                                <?php
                                                echo intval(
                                                    $producto["id"]
                                                );
                                                ?>,
                                                <?php
                                                echo htmlspecialchars(
                                                    json_encode(
                                                        $producto["nombre"],
                                                        JSON_UNESCAPED_UNICODE
                                                    ),
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );
                                                ?>,
                                                <?php
                                                echo $stock;
                                                ?>
                                            )"
                                        >

                                            📦 Stock

                                        </button>


                                        <!-- DESACTIVAR -->

                                        <a
                                            href="desactivar_producto.php?id=<?php

                                                echo intval(
                                                    $producto["id"]
                                                );

                                            ?>"
                                            class="
                                                btn-accion
                                                btn-desactivar
                                            "
                                            onclick="
                                                return confirm(
                                                    '¿Deseas desactivar este producto? Su stock actual será retirado del inventario.'
                                                );
                                            "
                                        >

                                            🚫 Desactivar

                                        </a>


                                    </div>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="8"
                                class="sin-productos"
                            >

                                No hay productos
                                registrados todavía.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>


        <!-- =====================================
             PRODUCTOS DESACTIVADOS
             ===================================== -->

        <section
            class="
                panel-productos
                separacion-productos
            "
        >


            <div class="cabecera-productos">


                <div>

                    <h2>
                        Productos desactivados
                    </h2>


                    <p>
                        Productos que actualmente
                        no están disponibles en la tienda.
                    </p>

                </div>


            </div>


            <!-- TABLA -->

            <div class="tabla-contenedor">


                <table>


                    <thead>

                        <tr>

                            <th>
                                Código
                            </th>

                            <th>
                                Producto
                            </th>

                            <th>
                                Categoría
                            </th>

                            <th>
                                Acción
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $resultado_desactivados &&
                        $resultado_desactivados->num_rows > 0
                    ): ?>


                        <?php while (
                            $producto_desactivado =
                            $resultado_desactivados
                            ->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- CÓDIGO -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $producto_desactivado[
                                            "codigo"
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- PRODUCTO -->

                                <td>

                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $producto_desactivado[
                                                "nombre"
                                            ]
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <!-- CATEGORÍA -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $producto_desactivado[
                                            "categoria"
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- REACTIVAR -->

                                <td>


                                    <a
                                        href="reactivar_producto.php?id=<?php

                                            echo intval(
                                                $producto_desactivado[
                                                    "id"
                                                ]
                                            );

                                        ?>"
                                        class="
                                            btn-accion
                                            btn-reactivar
                                        "
                                        onclick="
                                            return confirm(
                                                '¿Deseas reactivar este producto? El producto volverá con stock 0.'
                                            );
                                        "
                                    >

                                        🔄 Reactivar

                                    </a>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="4"
                                class="sin-productos"
                            >

                                No hay productos
                                desactivados.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>


    </main>


</div>


<!-- =========================================
     MODAL AGREGAR STOCK
     ========================================= -->

<div
    id="modalStock"
    class="modal-stock"
    onclick="cerrarModalFondo(event)"
>


    <div
        class="modal-contenido"
        onclick="event.stopPropagation()"
    >


        <!-- CABECERA -->

        <div class="modal-cabecera">


            <h2>
                Agregar stock
            </h2>


            <button
                type="button"
                class="btn-cerrar-modal"
                onclick="cerrarModalStock()"
            >

                ✕

            </button>


        </div>


        <!-- CUERPO -->

        <div class="modal-cuerpo">


            <div class="producto-modal">


                <strong
                    id="modalProductoNombre"
                >

                    Producto

                </strong>


                <span>

                    Stock actual:

                    <b
                        id="modalProductoStock"
                    >

                        0

                    </b>

                </span>


            </div>


            <!-- FORMULARIO -->

            <form
                action="agregar_stock.php"
                method="POST"
            >


                <input
                    type="hidden"
                    name="producto_id"
                    id="modalProductoId"
                >


                <div
                    class="campo-modal"
                >


                    <label
                        for="modalCantidad"
                    >

                        Cantidad

                    </label>


                    <input
                        type="number"
                        name="cantidad"
                        id="modalCantidad"
                        min="1"
                        step="1"
                        placeholder="Ej. 20"
                        required
                    >


                </div>


                <div
                    class="modal-acciones"
                >


                    <button
                        type="button"
                        class="btn-modal-cancelar"
                        onclick="cerrarModalStock()"
                    >

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn-modal-guardar"
                    >

                        Agregar

                    </button>


                </div>


            </form>


        </div>


    </div>


</div>


<script>

/* =========================================
   ABRIR MODAL
   ========================================= */

function abrirModalStock(
    id,
    nombre,
    stock
) {

    document.getElementById(
        "modalProductoId"
    ).value = id;


    document.getElementById(
        "modalProductoNombre"
    ).textContent = nombre;


    document.getElementById(
        "modalProductoStock"
    ).textContent = stock;


    document.getElementById(
        "modalCantidad"
    ).value = "";


    document.getElementById(
        "modalStock"
    ).classList.add("activo");


    setTimeout(
        function() {

            document.getElementById(
                "modalCantidad"
            ).focus();

        },
        100
    );

}


/* =========================================
   CERRAR MODAL
   ========================================= */

function cerrarModalStock() {

    document.getElementById(
        "modalStock"
    ).classList.remove("activo");

}


/* =========================================
   CERRAR AL TOCAR FONDO
   ========================================= */

function cerrarModalFondo(event) {

    if (
        event.target.id === "modalStock"
    ) {

        cerrarModalStock();

    }

}


/* =========================================
   CERRAR CON ESC
   ========================================= */

document.addEventListener(
    "keydown",
    function(event) {

        if (
            event.key === "Escape"
        ) {

            cerrarModalStock();

        }

    }
);

</script>


</body>

</html>