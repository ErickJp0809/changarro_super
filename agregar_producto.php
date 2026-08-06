<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit();
}

require_once "config/conexion.php";

$mensaje = "";

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

        $sql = "INSERT INTO productos 
                (nombre, categoria, precio_compra, precio_venta, stock)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ssddi",
            $nombre,
            $categoria,
            $precio_compra,
            $precio_venta,
            $stock
        );

        if ($stmt->execute()) {
            header("Location: productos.php");
            exit();
        } else {
            $mensaje = "Ocurrió un error al guardar el producto.";
        }

    } else {
        $mensaje = "Completa correctamente todos los campos.";
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Agregar producto | Changarro Súper y Más</title>

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
                <h1>Agregar producto</h1>
                <p>Registra un nuevo producto en el sistema</p>
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


        <section class="formulario-producto">

            <div class="titulo-formulario">
                <h2>Información del producto</h2>
                <p>Ingresa los datos del producto que deseas registrar.</p>
            </div>


            <?php if ($mensaje != ""): ?>

                <div class="mensaje-error">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <div class="campo-formulario">

                    <label for="nombre">Nombre del producto</label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        placeholder="Ej. Coca-Cola 600 ml"
                        required
                    >

                </div>


                <div class="campo-formulario">

                    <label for="categoria">Categoría</label>

                    <input
                        type="text"
                        id="categoria"
                        name="categoria"
                        placeholder="Ej. Bebidas"
                        required
                    >

                </div>


                <div class="fila-formulario">

                    <div class="campo-formulario">

                        <label for="precio_compra">
                            Precio de compra
                        </label>

                        <input
                            type="number"
                            id="precio_compra"
                            name="precio_compra"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            required
                        >

                    </div>


                    <div class="campo-formulario">

                        <label for="precio_venta">
                            Precio de venta
                        </label>

                        <input
                            type="number"
                            id="precio_venta"
                            name="precio_venta"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            required
                        >

                    </div>

                </div>


                <div class="campo-formulario">

                    <label for="stock">Stock inicial</label>

                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        min="0"
                        placeholder="0"
                        required
                    >

                </div>


                <div class="acciones-formulario">

                    <a href="productos.php" class="btn-cancelar">
                        Cancelar
                    </a>

                    <button type="submit" class="btn-guardar">
                        Guardar producto
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>