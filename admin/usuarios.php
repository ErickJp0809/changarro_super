<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/conexion.php";


/* =========================================
   SOLO ADMINISTRADORES
   ========================================= */

if ($_SESSION["rol"] !== "Administrador") {
    header("Location: dashboard.php");
    exit();
}


/* =========================================
   OBTENER USUARIOS
   ========================================= */

$sql = "
    SELECT
        id,
        nombre,
        usuario,
        rol,
        estado,
        fecha_registro
    FROM usuarios
    ORDER BY id DESC
";

$resultado = $conexion->query($sql);

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
        Usuarios | Changarro Súper y Más
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
                    Usuarios
                </h1>

                <p>
                    Administra los usuarios del sistema.
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
             PANEL DE USUARIOS
             ========================================= -->

        <section class="panel-productos">


            <!-- CABECERA -->

            <div class="cabecera-productos">


                <div>

                    <h2>
                        Lista de usuarios
                    </h2>

                    <p>
                        Usuarios registrados en el sistema.
                    </p>

                </div>


                <a
                    href="agregar_usuario.php"
                    class="btn-agregar"
                >

                    + Agregar usuario

                </a>


            </div>


            <!-- =========================================
                 TABLA
                 ========================================= -->

            <div class="tabla-contenedor">


                <table>


                    <thead>

                        <tr>

                            <th>
                                Nombre
                            </th>

                            <th>
                                Usuario
                            </th>

                            <th>
                                Rol
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Fecha de registro
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
                            $usuario =
                            $resultado->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- NOMBRE -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $usuario["nombre"]
                                    );

                                    ?>

                                </td>


                                <!-- USUARIO -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $usuario["usuario"]
                                    );

                                    ?>

                                </td>


                                <!-- ROL -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $usuario["rol"]
                                    );

                                    ?>

                                </td>


                                <!-- ESTADO -->

                                <td>


                                    <?php if (
                                        $usuario["estado"]
                                        === "Activo"
                                    ): ?>


                                        <span
                                            class="estado-activo"
                                        >

                                            ● Activo

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="estado-inactivo"
                                        >

                                            ● Inactivo

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- FECHA -->

                                <td>

                                    <?php

                                    echo $usuario[
                                        "fecha_registro"
                                    ];

                                    ?>

                                </td>


                                <!-- ACCIONES -->

                                <td>


                                    <a
                                        href="editar_usuario.php?id=<?php echo $usuario["id"]; ?>"
                                        class="btn-editar"
                                    >

                                        Editar

                                    </a>


                                    <?php if (
                                        $usuario["estado"]
                                        === "Activo"
                                    ): ?>


                                        <a
                                            href="cambiar_estado_usuario.php?id=<?php echo $usuario["id"]; ?>&estado=Inactivo"
                                            class="btn-eliminar"
                                            onclick="return confirm('¿Seguro que deseas desactivar este usuario?');"
                                        >

                                            Desactivar

                                        </a>


                                    <?php else: ?>


                                        <a
                                            href="cambiar_estado_usuario.php?id=<?php echo $usuario["id"]; ?>&estado=Activo"
                                            class="btn-activar"
                                            onclick="return confirm('¿Deseas activar este usuario?');"
                                        >

                                            Activar

                                        </a>


                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="6"
                                class="sin-datos"
                            >

                                No hay usuarios registrados todavía.

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