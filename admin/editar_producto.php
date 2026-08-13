<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/conexion.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: productos.php");
    exit();
}

$id = intval($_GET["id"]);


/* =========================
   ACTUALIZAR PRODUCTO
   ========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"]);
    $categoria = trim($_POST["categoria"]);
    $precio_compra = $_POST["precio_compra"];
    $precio_venta = $_POST["precio_venta"];
    $stock = $_POST["stock"];

    if (
        $nombre != "" &&
        $categoria != "" &&
        $precio_compra >= 0 &&
        $precio_venta >= 0 &&
        $stock >= 0
    ) {

        $sql = "UPDATE productos
                SET nombre = ?,
                    categoria = ?,
                    precio_compra = ?,
                    precio_venta = ?,
                    stock = ?
                WHERE id = ?";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ssddii",
            $nombre,
            $categoria,
            $precio_compra,
            $precio_venta,
            $stock,
            $id
        );

        if ($stmt->execute()) {

            header("Location: productos.php");
            exit();

        }

    }

}


/* =========================
   OBTENER PRODUCTO
   ========================= */

$sql = "SELECT *
        FROM productos
        WHERE id = ?";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows == 0) {

    header("Location: productos.php");
    exit();

}


$producto = $resultado->fetch_assoc();

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
        Editar producto | Changarro Súper y Más
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

</head>


<body>


<div class="contenedor">


    <!-- =========================
         MENÚ LATERAL
         ========================= -->

    <aside class="sidebar">


        <div class="marca">

            <h2>
                Changarro
            </h2>

            <span>
                Súper y Más
            </span>

        </div>


        <nav class="menu">


            <a href="dashboard.php">

                <span>⌂</span>
                Inicio

            </a>


            <a
                href="productos.php"
                class="activo"
            >

                <span>▣</span>
                Productos

            </a>


            <a href="inventario.php">

                <span>▤</span>
                Inventario

            </a>


            <a href="ventas.php">

                <span>$</span>
                Ventas

            </a>


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


        <div class="salir">

            <a href="../logout.php">

                Cerrar sesión

            </a>

        </div>


    </aside>


    <!-- =========================
         CONTENIDO
         ========================= -->

    <main class="contenido">


        <!-- ENCABEZADO -->

        <header class="encabezado">


            <div>

                <h1>
                    Editar producto
                </h1>

                <p>
                    Modifica la información del producto.
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


        <!-- =========================
             FORMULARIO
             ========================= -->

        <section class="formulario-producto">


            <div class="titulo-formulario">

                <h2>
                    Información del producto
                </h2>

                <p>
                    Edita los datos y guarda los cambios.
                </p>

            </div>


            <form method="POST">


                <!-- CÓDIGO -->

                <div class="campo-formulario">

                    <label for="codigo">

                        Código del producto

                    </label>


                    <input
                        type="text"
                        id="codigo"
                        value="<?php
                        echo htmlspecialchars(
                            $producto["codigo"]
                        );
                        ?>"
                        readonly
                        style="
                            background:#f1f1f1;
                            cursor:not-allowed;
                            font-weight:600;
                        "
                    >

                </div>


                <!-- NOMBRE -->

                <div class="campo-formulario">

                    <label for="nombre">

                        Nombre del producto

                    </label>


                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value="<?php
                        echo htmlspecialchars(
                            $producto["nombre"]
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- CATEGORÍA -->

                <div class="campo-formulario">

                    <label for="categoria">

                        Categoría

                    </label>


                    <input
                        type="text"
                        id="categoria"
                        name="categoria"
                        value="<?php
                        echo htmlspecialchars(
                            $producto["categoria"]
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- PRECIOS -->

                <div class="fila-formulario">


                    <div class="campo-formulario">

                        <label for="precio_compra">

                            Precio de compra

                        </label>


                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            id="precio_compra"
                            name="precio_compra"
                            value="<?php
                            echo $producto["precio_compra"];
                            ?>"
                            required
                        >

                    </div>


                    <div class="campo-formulario">

                        <label for="precio_venta">

                            Precio de venta

                        </label>


                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            id="precio_venta"
                            name="precio_venta"
                            value="<?php
                            echo $producto["precio_venta"];
                            ?>"
                            required
                        >

                    </div>


                </div>


                <!-- STOCK -->

                <div class="campo-formulario">

                    <label for="stock">

                        Stock

                    </label>


                    <input
                        type="number"
                        min="0"
                        id="stock"
                        name="stock"
                        value="<?php
                        echo $producto["stock"];
                        ?>"
                        required
                    >

                </div>


                <!-- BOTONES -->

                <div class="acciones-formulario">


                    <a
                        href="productos.php"
                        class="btn-cancelar"
                    >

                        Cancelar

                    </a>


                    <button
                        type="submit"
                        class="btn-guardar"
                    >

                        Guardar cambios

                    </button>


                </div>


            </form>


        </section>


    </main>


</div>


</body>

</html>