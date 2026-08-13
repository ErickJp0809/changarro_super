<?php

$pagina_actual = basename($_SERVER["PHP_SELF"]);

?>

<aside class="sidebar">


    <!-- =========================================
         LOGO
         ========================================= -->

    <div class="marca">

        <img
            src="../img/logo_changarro_transparente.png"
            alt="El Changarro"
        >

    </div>


    <!-- =========================================
         MENÚ
         ========================================= -->

    <nav class="menu">


        <!-- =====================================
             INICIO
             ===================================== -->

        <a
            href="dashboard.php"
            class="<?php echo ($pagina_actual == "dashboard.php") ? "activo" : ""; ?>"
        >

            <span>⌂</span>

            Inicio

        </a>


        <!-- =====================================
             PRODUCTOS
             ===================================== -->

        <a
            href="productos.php"
            class="<?php echo ($pagina_actual == "productos.php") ? "activo" : ""; ?>"
        >

            <span>▣</span>

            Productos

        </a>


        <!-- =====================================
             INVENTARIO
             ===================================== -->

        <a
            href="inventario.php"
            class="<?php echo ($pagina_actual == "inventario.php") ? "activo" : ""; ?>"
        >

            <span>▤</span>

            Inventario

        </a>


        <!-- =====================================
             VENTAS
             ===================================== -->

        <a
            href="ventas.php"
            class="<?php echo ($pagina_actual == "ventas.php") ? "activo" : ""; ?>"
        >

            <span>$</span>

            Ventas

        </a>


        <!-- =====================================
             PEDIDOS
             ===================================== -->

        <a
            href="pedidos.php"
            class="<?php echo ($pagina_actual == "pedidos.php") ? "activo" : ""; ?>"
        >

            <span>📦</span>

            Pedidos

        </a>


        <!-- =====================================
             ESTADÍSTICAS
             ===================================== -->

        <a
            href="estadisticas.php"
            class="<?php echo ($pagina_actual == "estadisticas.php") ? "activo" : ""; ?>"
        >

            <span>📊</span>

            Estadísticas

        </a>


        <!-- =====================================
             USUARIOS
             ===================================== -->

        <?php if (
            isset($_SESSION["rol"]) &&
            $_SESSION["rol"] === "Administrador"
        ): ?>

            <a
                href="usuarios.php"
                class="<?php echo ($pagina_actual == "usuarios.php") ? "activo" : ""; ?>"
            >

                <span>♟</span>

                Usuarios

            </a>

        <?php endif; ?>


    </nav>


    <!-- =========================================
         CERRAR SESIÓN
         ========================================= -->

    <div class="salir">

        <a href="../logout.php">

            Cerrar sesión

        </a>

    </div>


</aside>