<?php

session_start();


/* =========================================
   VERIFICAR SESIÓN
   ========================================= */

if (!isset($_SESSION["id"])) {

    header("Location: ../index.php");

    exit();

}


require_once "../config/conexion.php";


/* =========================================
   VERIFICAR ADMINISTRADOR
   ========================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {

    header("Location: ../index.php");

    exit();

}


/* =========================================
   CAMBIAR ESTADO DEL PEDIDO
   ========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $pedido_id =
        intval($_POST["pedido_id"] ?? 0);

    $nuevo_estado =
        trim($_POST["estado"] ?? "");


    /* =====================================
       ESTADOS PERMITIDOS
       ===================================== */

    $estados_permitidos = [
        "Pendiente",
        "En preparación",
        "En camino",
        "Completada",
        "Cancelada"
    ];


    /* =====================================
       VALIDAR
       ===================================== */

    if (
        $pedido_id > 0 &&
        in_array(
            $nuevo_estado,
            $estados_permitidos,
            true
        )
    ) {


        /* ================================
           ACTUALIZAR
           ================================ */

        $sql_update = "
            UPDATE ventas
            SET estado = ?
            WHERE id = ?
        ";


        $stmt_update =
            $conexion->prepare(
                $sql_update
            );


        if ($stmt_update) {

            $stmt_update->bind_param(
                "si",
                $nuevo_estado,
                $pedido_id
            );


            $stmt_update->execute();


            $stmt_update->close();

        }

    }


    /* =====================================
       REGRESAR A PEDIDOS
       ===================================== */

    header(
        "Location: pedidos.php"
    );

    exit();

}


/* =========================================
   OBTENER PEDIDOS
   ========================================= */

$sql = "
    SELECT
        ventas.id,
        ventas.total,
        ventas.fecha,
        ventas.estado,
        usuarios.nombre AS cliente
    FROM ventas
    INNER JOIN usuarios
        ON ventas.usuario_id = usuarios.id
    WHERE usuarios.rol = 'Cliente'
    ORDER BY ventas.id DESC
";


$resultado =
    $conexion->query($sql);


/* =========================================
   INICIAL DEL ADMINISTRADOR
   ========================================= */

$inicial_admin = strtoupper(
    substr(
        $_SESSION["nombre"],
        0,
        1
    )
);

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
        Pedidos | Changarro Súper y Más
    </title>


    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >


    <style>

        /* =========================================
           ESTADOS
           ========================================= */

        .estado {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

        }


        .estado-pendiente {

            background: #fff4df;

            color: #c87500;

        }


        .estado-preparacion {

            background: #eaf4ff;

            color: #2475b8;

        }


        .estado-camino {

            background: #f0eaff;

            color: #7048b8;

        }


        .estado-completada {

            background: #e8f7ee;

            color: #16834a;

        }


        .estado-cancelada {

            background: #ffe9e9;

            color: #d52f2f;

        }


        /* =========================================
           SELECT DE ESTADO
           ========================================= */

        .form-estado {

            display: inline-flex;

            align-items: center;

        }


        .select-estado {

            min-width: 145px;

            padding: 8px 10px;

            border: 1px solid #ddd;

            border-radius: 8px;

            background: white;

            color: #333;

            font-size: 12px;

            cursor: pointer;

            outline: none;

        }


        .select-estado:focus {

            border-color: #f7941d;

        }


        /* =========================================
           BOTÓN VER
           ========================================= */

        .btn-ver-pedido {

            display: inline-block;

            padding: 7px 13px;

            border-radius: 7px;

            background: #f7941d;

            color: white;

            text-decoration: none;

            font-size: 12px;

            font-weight: bold;

        }


        .btn-ver-pedido:hover {

            background: #e98212;

        }


        /* =========================================
           SIN PEDIDOS
           ========================================= */

        .sin-pedidos {

            padding: 45px 20px;

            text-align: center;

            color: #777;

        }


        .sin-pedidos h3 {

            margin-bottom: 8px;

            color: #222;

        }


        /* =========================================
           TABLA
           ========================================= */

        .tabla-contenedor {

            overflow-x: auto;

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


        <!-- =========================================
             ENCABEZADO
             ========================================= -->

        <header class="encabezado">


            <div>

                <h1>
                    Pedidos
                </h1>

                <p>
                    Consulta y administra los pedidos de los clientes.
                </p>

            </div>


            <!-- PERFIL -->

            <div class="perfil">


                <div class="avatar">

                    <?php

                    echo htmlspecialchars(
                        $inicial_admin
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
             PANEL
             ========================================= -->

        <section class="panel-productos">


            <!-- CABECERA -->

            <div class="cabecera-productos">


                <div>

                    <h2>
                        Pedidos registrados
                    </h2>

                    <p>
                        Pedidos realizados por los clientes.
                    </p>

                </div>


            </div>


            <!-- =========================================
                 TABLA
                 ========================================= -->

            <div class="tabla-contenedor">


                <table>


                    <thead>

                        <tr>

                            <th>
                                Pedido
                            </th>


                            <th>
                                Cliente
                            </th>


                            <th>
                                Fecha
                            </th>


                            <th>
                                Total
                            </th>


                            <th>
                                Estado
                            </th>


                            <th>
                                Cambiar estado
                            </th>


                            <th>
                                Acción
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $resultado &&
                        $resultado->num_rows > 0
                    ): ?>


                        <?php while (
                            $pedido =
                            $resultado->fetch_assoc()
                        ): ?>


                            <?php

                            $estado =
                                $pedido["estado"];


                            /* =========================
                               CLASE DEL ESTADO
                               ========================= */

                            switch ($estado) {


                                case "Pendiente":

                                    $clase_estado =
                                        "estado-pendiente";

                                    break;


                                case "En preparación":

                                    $clase_estado =
                                        "estado-preparacion";

                                    break;


                                case "En camino":

                                    $clase_estado =
                                        "estado-camino";

                                    break;


                                case "Completada":

                                    $clase_estado =
                                        "estado-completada";

                                    break;


                                case "Cancelada":

                                    $clase_estado =
                                        "estado-cancelada";

                                    break;


                                default:

                                    $clase_estado =
                                        "estado-pendiente";

                                    break;

                            }

                            ?>


                            <tr>


                                <!-- =================================
                                     PEDIDO
                                     ================================= -->

                                <td>

                                    <strong>

                                        #

                                        <?php

                                        echo intval(
                                            $pedido["id"]
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <!-- =================================
                                     CLIENTE
                                     ================================= -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $pedido["cliente"]
                                    );

                                    ?>

                                </td>


                                <!-- =================================
                                     FECHA
                                     ================================= -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $pedido["fecha"]
                                    );

                                    ?>

                                </td>


                                <!-- =================================
                                     TOTAL
                                     ================================= -->

                                <td>

                                    <strong>

                                        $

                                        <?php

                                        echo number_format(
                                            $pedido["total"],
                                            2
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <!-- =================================
                                     ESTADO ACTUAL
                                     ================================= -->

                                <td>

                                    <span
                                        class="estado <?php

                                            echo $clase_estado;

                                        ?>"
                                    >

                                        ●

                                        <?php

                                        echo htmlspecialchars(
                                            $estado
                                        );

                                        ?>

                                    </span>

                                </td>


                                <!-- =================================
                                     CAMBIAR ESTADO
                                     ================================= -->

                                <td>


                                    <form
                                        method="POST"
                                        action="pedidos.php"
                                        class="form-estado"
                                    >


                                        <input
                                            type="hidden"
                                            name="pedido_id"
                                            value="<?php

                                                echo intval(
                                                    $pedido["id"]
                                                );

                                            ?>"
                                        >


                                        <select
                                            name="estado"
                                            class="select-estado"
                                            onchange="this.form.submit()"
                                        >


                                            <option
                                                value="Pendiente"
                                                <?php

                                                echo (
                                                    $estado ===
                                                    "Pendiente"
                                                )
                                                    ? "selected"
                                                    : "";

                                                ?>
                                            >

                                                🟡 Pendiente

                                            </option>


                                            <option
                                                value="En preparación"
                                                <?php

                                                echo (
                                                    $estado ===
                                                    "En preparación"
                                                )
                                                    ? "selected"
                                                    : "";

                                                ?>
                                            >

                                                🔵 En preparación

                                            </option>


                                            <option
                                                value="En camino"
                                                <?php

                                                echo (
                                                    $estado ===
                                                    "En camino"
                                                )
                                                    ? "selected"
                                                    : "";

                                                ?>
                                            >

                                                🟣 En camino

                                            </option>


                                            <option
                                                value="Completada"
                                                <?php

                                                echo (
                                                    $estado ===
                                                    "Completada"
                                                )
                                                    ? "selected"
                                                    : "";

                                                ?>
                                            >

                                                🟢 Completada

                                            </option>


                                            <option
                                                value="Cancelada"
                                                <?php

                                                echo (
                                                    $estado ===
                                                    "Cancelada"
                                                )
                                                    ? "selected"
                                                    : "";

                                                ?>
                                            >

                                                🔴 Cancelada

                                            </option>


                                        </select>


                                    </form>


                                </td>


                                <!-- =================================
                                     ACCIÓN
                                     ================================= -->

                                <td>

                                    <a
                                        href="detalle_pedido.php?id=<?php echo intval($pedido["id"]); ?>"
                                        class="btn-ver-pedido"
                                    >

                                        Ver

                                    </a>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="7"
                                class="sin-pedidos"
                            >

                                <h3>
                                    No hay pedidos registrados
                                </h3>

                                <p>
                                    Los pedidos realizados por los clientes aparecerán aquí.
                                </p>

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