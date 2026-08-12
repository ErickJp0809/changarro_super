<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit();
}

require_once "../config/conexion.php";

if (!isset($_GET["id"])) {
    header("Location: productos.php");
    exit();
}

$id = $_GET["id"];

// ACTUALIZAR PRODUCTO
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"]);
    $categoria = trim($_POST["categoria"]);
    $precio_compra = $_POST["precio_compra"];
    $precio_venta = $_POST["precio_venta"];
    $stock = $_POST["stock"];

    $sql = "UPDATE productos
            SET nombre = ?, categoria = ?, precio_compra = ?, precio_venta = ?, stock = ?
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

// OBTENER DATOS DEL PRODUCTO

$sql = "SELECT * FROM productos WHERE id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Editar producto</title>

<link rel="stylesheet" href="../css/dashboard.css">

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
Inicio
</a>

<a href="productos.php" class="activo">
Productos
</a>

<a href="#">
Inventario
</a>

<a href="#">
Ventas
</a>

<a href="#">
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
<h1>Editar producto</h1>
<p>Modifica la información del producto.</p>
</div>

<div class="perfil">

<div class="avatar">
<?php echo strtoupper(substr($_SESSION["nombre"],0,1)); ?>
</div>

<div>
<strong><?php echo $_SESSION["nombre"]; ?></strong><br>
<span><?php echo $_SESSION["rol"]; ?></span>
</div>

</div>

</header>

<section class="formulario-producto">

<div class="titulo-formulario">
<h2>Información del producto</h2>
<p>Edita los datos y guarda los cambios.</p>
</div>

<form method="POST">

<div class="campo-formulario">

<label>Nombre del producto</label>

<input
type="text"
name="nombre"
value="<?php echo htmlspecialchars($producto["nombre"]); ?>"
required>

</div>

<div class="campo-formulario">

<label>Categoría</label>

<input
type="text"
name="categoria"
value="<?php echo htmlspecialchars($producto["categoria"]); ?>"
required>

</div>

<div class="fila-formulario">

<div class="campo-formulario">

<label>Precio de compra</label>

<input
type="number"
step="0.01"
name="precio_compra"
value="<?php echo $producto["precio_compra"]; ?>"
required>

</div>

<div class="campo-formulario">

<label>Precio de venta</label>

<input
type="number"
step="0.01"
name="precio_venta"
value="<?php echo $producto["precio_venta"]; ?>"
required>

</div>

</div>

<div class="campo-formulario">

<label>Stock</label>

<input
type="number"
name="stock"
value="<?php echo $producto["stock"]; ?>"
required>

</div>

<div class="acciones-formulario">

<a href="productos.php" class="btn-cancelar">
Cancelar
</a>

<button type="submit" class="btn-guardar">
Guardar cambios
</button>

</div>

</form>

</section>

</main>

</div>

</body>
</html>