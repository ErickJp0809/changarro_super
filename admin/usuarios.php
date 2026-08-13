<?php

require_once "verificar_acceso.php";
require_once "../config/conexion.php";


/* =========================================
   VERIFICAR QUE SEA ADMINISTRADOR
   ========================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {

    header("Location: dashboard.php");
    exit();

}


/* =========================================
   OBTENER USUARIOS ADMINISTRATIVOS
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
    WHERE rol IN ('Administrador', 'Empleado')
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


    <style>

        /* =========================================
           TABLA
           ========================================= */

        .tabla-usuarios {

            width: 100%;

            border-collapse: collapse;

        }


        .tabla-usuarios th {

            text-align: left;

            padding: 12px 14px;

            background: #f5f5f5;

            color: #555;

            font-size: 13px;

        }


        .tabla-usuarios td {

            padding: 13px 14px;

            border-bottom: 1px solid #eeeeee;

            font-size: 13px;

        }


        .tabla-usuarios tr:last-child td {

            border-bottom: none;

        }


        /* =========================================
           ACCIONES
           ========================================= */

        .acciones-usuario {

            display: flex;

            gap: 7px;

            align-items: center;

        }


        .btn-editar {

            display: inline-block;

            padding: 7px 11px;

            background: #eeeeee;

            color: #222;

            border-radius: 7px;

            text-decoration: none;

            font-size: 12px;

        }


        .btn-desactivar {

            display: inline-block;

            padding: 7px 11px;

            background: #ffe9e9;

            color: #d64545;

            border-radius: 7px;

            text-decoration: none;

            font-size: 12px;

        }


        .btn-reactivar {

            display: inline-block;

            padding: 7px 11px;

            background: #e8f8ef;

            color: #16834a;

            border-radius: 7px;

            text-decoration: none;

            font-size: 12px;

        }


        .btn-editar:hover,
        .btn-desactivar:hover,
        .btn-reactivar:hover {

            opacity: 0.8;

        }


        /* =========================================
           ESTADOS
           ========================================= */

        .estado-activo {

            color: #16834a;

            font-weight: 600;

            font-size: 12px;

        }


        .estado-inactivo {

            color: #d64545;

            font-weight: 600;

            font-size: 12px;

        }


        /* =========================================
           MENSAJES
           ========================================= */

        .mensaje-exito {

            background: #e8f8ef;

            color: #16834a;

            border: 1px solid #b9e8cc;

            padding: 12px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 13px;

            font-weight: 600;

        }


        .mensaje-error {

            background: #ffe9e9;

            color: #b42318;

            border: 1px solid #f4b8b8;

            padding: 12px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 13px;

            font-weight: 600;

        }


        /* =========================================
           SIN USUARIOS
           ========================================= */

        .sin-datos {

            text-align: center;

            color: #888;

            padding: 35px !important;

        }


        /* =========================================
           RESPONSIVE
           ========================================= */

        @media (max-width: 900px) {

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
                    Usuarios
                </h1>


                <p>
                    Administra los usuarios
                    con acceso al panel.
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

                        Administrador

                    </span>

                </div>


            </div>


        </header>


        <!-- =====================================
             MENSAJES
             ===================================== -->

        <?php if (
            isset($_GET["creado"]) &&
            $_GET["creado"] === "ok"
        ): ?>


            <div class="mensaje-exito">

                ✓ Usuario creado correctamente.

            </div>


        <?php endif; ?>


        <?php if (
            isset($_GET["editado"]) &&
            $_GET["editado"] === "ok"
        ): ?>


            <div class="mensaje-exito">

                ✓ Usuario actualizado correctamente.

            </div>


        <?php endif; ?>


        <?php if (
            isset($_GET["desactivado"]) &&
            $_GET["desactivado"] === "ok"
        ): ?>


            <div class="mensaje-exito">

                ✓ Usuario desactivado correctamente.

            </div>


        <?php endif; ?>


        <?php if (
            isset($_GET["reactivado"]) &&
            $_GET["reactivado"] === "ok"
        ): ?>


            <div class="mensaje-exito">

                ✓ Usuario reactivado correctamente.

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
             PANEL
             ===================================== -->

        <section class="panel-productos">


            <!-- CABECERA -->

            <div class="cabecera-productos">


                <div>

                    <h2>
                        Usuarios administrativos
                    </h2>


                    <p>
                        Administradores y empleados
                        con acceso al sistema.
                    </p>

                </div>


                <a
                    href="agregar_usuario.php"
                    class="btn-agregar"
                >

                    + Agregar usuario

                </a>


            </div>


            <!-- =================================
                 TABLA
                 ================================= -->

            <div class="tabla-contenedor">


                <table
                    class="tabla-usuarios"
                >


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

                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $usuario["nombre"]
                                        );

                                        ?>

                                    </strong>

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

                                    echo date(
                                        "d/m/Y H:i",
                                        strtotime(
                                            $usuario[
                                                "fecha_registro"
                                            ]
                                        )
                                    );

                                    ?>

                                </td>


                                <!-- ACCIONES -->

                                <td>


                                    <div
                                        class="acciones-usuario"
                                    >


                                        <!-- EDITAR -->

                                        <a
                                            href="editar_usuario.php?id=<?php

                                                echo intval(
                                                    $usuario["id"]
                                                );

                                            ?>"
                                            class="btn-editar"
                                        >

                                            Editar

                                        </a>


                                        <?php

                                        /*
                                         * No permitimos
                                         * desactivar la
                                         * cuenta del
                                         * administrador
                                         * que está usando
                                         * actualmente
                                         */

                                        $es_usuario_actual =
                                            (
                                                intval(
                                                    $usuario["id"]
                                                )
                                                ===
                                                intval(
                                                    $_SESSION["id"]
                                                )
                                            );

                                        ?>


                                        <?php if (
                                            !$es_usuario_actual
                                        ): ?>


                                            <?php if (
                                                $usuario["estado"]
                                                === "Activo"
                                            ): ?>


                                                <!-- DESACTIVAR -->

                                                <a
                                                    href="cambiar_estado_usuario.php?id=<?php

                                                        echo intval(
                                                            $usuario["id"]
                                                        );

                                                    ?>&estado=Inactivo"
                                                    class="btn-desactivar"
                                                    onclick="
                                                        return confirm(
                                                            '¿Deseas desactivar este usuario?'
                                                        );
                                                    "
                                                >

                                                    Desactivar

                                                </a>


                                            <?php else: ?>


                                                <!-- REACTIVAR -->

                                                <a
                                                    href="cambiar_estado_usuario.php?id=<?php

                                                        echo intval(
                                                            $usuario["id"]
                                                        );

                                                    ?>&estado=Activo"
                                                    class="btn-reactivar"
                                                    onclick="
                                                        return confirm(
                                                            '¿Deseas reactivar este usuario?'
                                                        );
                                                    "
                                                >

                                                    Reactivar

                                                </a>


                                            <?php endif; ?>


                                        <?php endif; ?>


                                    </div>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="6"
                                class="sin-datos"
                            >

                                No hay usuarios administrativos
                                registrados.

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