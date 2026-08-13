<?php

$mensaje = "";

if (isset($_GET["error"])) {

    if ($_GET["error"] === "desactivado") {

        $mensaje = "⚠️ Este usuario está desactivado. Contacta al administrador.";

    } elseif ($_GET["error"] === "incorrecta") {

        $mensaje = "❌ Usuario o contraseña incorrectos.";

    } elseif ($_GET["error"] === "1") {

        $mensaje = "❌ Usuario o contraseña incorrectos.";

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

    <title>
        Iniciar sesión | Changarro Súper y Más
    </title>

    <link
        rel="stylesheet"
        href="css/estilos.css"
    >

</head>

<body>

<div class="login-container">

    <div class="login-box">


        <!-- LOGO -->

        <div class="login-logo">

            <img
                src="img/logo_changarro_transparente.png"
                alt="El Changarro"
            >

        </div>


        <!-- NOMBRE DE LA TIENDA -->

        <h1>
            Changarro Súper y Más
        </h1>


        <!-- FORMULARIO -->

        <form
            action="login.php"
            method="POST"
        >


            <?php if ($mensaje !== ""): ?>

                <div class="mensaje-login">

                    <?php

                    echo htmlspecialchars($mensaje);

                    ?>

                </div>

            <?php endif; ?>


            <!-- USUARIO -->

            <div class="campo">

                <label for="usuario">
                    Usuario
                </label>

                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    placeholder="Ingresa tu usuario"
                    autocomplete="username"
                    required
                >

            </div>


            <!-- CONTRASEÑA -->

            <div class="campo">

                <label for="contrasena">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    placeholder="Ingresa tu contraseña"
                    autocomplete="current-password"
                    required
                >

            </div>


            <!-- BOTÓN -->

            <button type="submit">
                Iniciar sesión
            </button>


        </form>


        <!-- COLORES DE LA TIENDA -->

        <div class="colores-login">

            <span class="color-naranja"></span>

            <span class="color-azul"></span>

            <span class="color-rojo"></span>

            <span class="color-amarillo"></span>

        </div>


    </div>

</div>

</body>

</html>