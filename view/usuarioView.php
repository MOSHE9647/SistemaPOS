<?php

    require_once __DIR__ . '/../auth/auth.php'; // Verifica la autenticación y los roles

    // Solo los usuarios con rol de administrador (por ejemplo, RolID = 1) pueden acceder
    if (!verificarRol([ROL_ADMIN])) { // 1 = Rol de Administrador
        // Si el usuario no tiene el rol adecuado, cierra la sesión y redirige a la página de inicio de sesión
        $_SESSION[SESSION_ACCESS_DENIED] = true;
        $INDEX_PAGE = "./../index.php";
        header("Location: $INDEX_PAGE");
        exit();
    }

?>

<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de Usuarios | POSFusion</title>
        <link rel="stylesheet" href="./css/styles.css">
    </head>
    <body>
        <h2>Lista de Usuarios</h2>

        <div id="message"></div>

        <div class="table-container">
            <div class="table-header">
                <div id="paginationSort">
                    Ordenar por:
                    <select id="sortSelector">
                        <option value="nombre">Nombre</option>
                        <option value="apellido1">Prim. Apellido</option>
                        <option value="apellido2">Seg. Apellido</option>
                        <option value="email">Correo</option>
                        <option value="fechacreacion">Fecha Registro</option>
                    </select>
                </div>

                <!-- Botón para crear nuevo Usuario -->
                <button id="createButton" onclick="showCreateRow()">Crear</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th data-field="nombre">Nombre</th>
                        <th data-field="apellido1">Prim. Apellido</th>
                        <th data-field="apellido2">Seg. Apellido</th>
                        <th data-field="correo">Correo</th>
                        <th data-field="password">Contraseña</th>
                        <th data-field="rol">Rol</th>
                        <th data-field="creacion">Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan = "8" style = "text-align: center; height: 50px;">
                            No hay registros disponibles
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="pagination-container">
                <!-- Selector de tamaño de página -->
                <div id="paginationSize">
                    Mostrando:
                    <select id="pageSizeSelector">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    de <span id="totalRecords"></span> registros
                </div>

                <!-- Controles de paginación -->
                <div id="paginationControls">
                    <button id="prevPage" onclick="changePage(currentPage - 1)">Anterior</button>
                    <span id="pageInfo">Página <span id="currentPage">1</span> de <span id="totalPages">1</span></span>
                    <button id="nextPage" onclick="changePage(currentPage + 1)">Siguiente</button>
                </div>
            </div>
        </div>

        <a href="../index.php" class="menu-button">Regresar al Menú</a>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        
        <!-- Scripts propios -->
        <script src="./js/utils.js"></script>
        <script src="./js/usuario/gui.js"></script>
        <script src="./js/usuario/pagination.js"></script>
        <script src="./js/usuario/selects.js"></script>
        <script src="./js/usuario/crud.js"></script>
    </body>
</html>
