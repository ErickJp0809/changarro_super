<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión | Changarro Súper y Más</title>

    <link rel="stylesheet" href="css/estilos.css">
</head>

<body>

    <div class="login-container">

        <div class="login-box">

            <h1>Changarro Súper y Más</h1>
            <p>Panel de Administración</p>

            <form action="login.php" method="POST">

                <div class="campo">
                    <label for="usuario">Usuario</label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        placeholder="Ingresa tu usuario"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="contrasena">Contraseña</label>

                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        placeholder="Ingresa tu contraseña"
                        required
                    >
                </div>

                <button type="submit">
                    Iniciar sesión
                </button>

            </form>

        </div>

    </div>

</body>

</html>