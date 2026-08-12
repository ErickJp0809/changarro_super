<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">

    <div class="marca">
        <h2>Changarro</h2>
        <span>Súper y Más</span>
    </div>

    <nav class="menu">

        <a href="dashboard.php"
           class="<?php echo ($pagina_actual == 'dashboard.php') ? 'active' : ''; ?>">
            ⌂ Inicio
        </a>

        <a href="productos.php"
           class="<?php echo ($pagina_actual == 'productos.php') ? 'active' : ''; ?>">
            ▣ Productos
        </a>

        <a href="inventario.php"
           class="<?php echo ($pagina_actual == 'inventario.php') ? 'active' : ''; ?>">
            ▤ Inventario
        </a>

        <a href="ventas.php"
           class="<?php echo ($pagina_actual == 'ventas.php') ? 'active' : ''; ?>">
            $ Ventas
        </a>

        <a href="usuarios.php"
           class="<?php echo ($pagina_actual == 'usuarios.php') ? 'active' : ''; ?>">
            ♙ Usuarios
        </a>

    </nav>

    <div class="salir">
        <a href="../logout.php">
            Cerrar sesión
        </a>
    </div>

</aside>