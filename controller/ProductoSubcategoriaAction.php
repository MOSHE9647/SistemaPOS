<?php
 include __DIR__ . '/../service/ProductoSubcategoriasBusiness.php';
 require_once __DIR__ . '/../utils/Utils.php';

 // Función para validar los datos del impuesto
 function validarDatos($id_producto, $id_subcategoria) {
    $errors = [];
    if(empty($id_producto) || !is_numeric($id_producto)){
         $errors[] = "¡El id producto no debe estar vacio!";
    }  
    if(empty($id_subcategoria) || !is_numeric($id_subcategoria)){
        $errors[] = "¡El id subcategoria no debe estar vacio!";
    }    
     return $errors;
 }
 
 $response = [];
 if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $accion = $_POST['accion'];

     $id_producto_subcategoria = isset($_POST['id']) ? $_POST['id'] : null;
     $id_producto = $_POST['producto'];
     $id_subcategoria = $_POST['subcategoria'];
        Utils::writeLog("Action : ".$id_producto."   ".$id_subcategoria );
     $ProductoSubcategoriaBusiness = new ProductoSubcategoriaBusines();

     if ($accion == 'eliminar') {
         if (empty($id_producto_subcategoria) || !is_numeric($id_producto_subcategoria)) {
             $response['success'] = false;
             $response['message'] = "El ID no puede estar vacío.";
         } else {
             $result = $ProductoSubcategoriaBusiness->deleteProductoSubcategoria($id_producto_subcategoria);
             $response['success'] = $result["success"];
             $response['message'] = $result["message"];
         }
     } else {
         $validationErrors = validarDatos($id_producto,$id_subcategoria);
         if (empty($validationErrors)) {
            $ProductoSub = new ProductoSubcategoria($id_producto,$id_subcategoria,$id_producto_subcategoria);
             //$subcategoria = new Subcategoria($nombre_subcategoria,$id_subcategoria);
             if ($accion == 'insertar') {
                 // Utils::writeLog("Action insert: ".$subcategoria->getSubcategoriaNombre()."  ".$subcategoria->getSubcategoriaId());
                 $result = $ProductoSubcategoriaBusiness->insertProductoSubcategoria($ProductoSub);
                 $response['success'] = $result["success"];
                 $response['message'] = $result["message"];
             } elseif ($accion == 'actualizar') {
                 // Utils::writeLog("Action update: ".$subcategoria->getSubcategoriaNombre()."  ".$subcategoria->getSubcategoriaId());
                 $result =  $ProductoSubcategoriaBusiness->updateProductoSubcategoria($ProductoSub);
                 $response['success'] = $result["success"];
                 $response['message'] = $result["message"];
             } else {
                 $response['success'] = false;
                 $response['message'] = "Acción no válida.";
             }
         } else {
             $response['success'] = false;
             $response['message'] = implode(' ', $validationErrors);
         }
     }

     header('Content-Type: application/json');
     echo json_encode($response);
     exit();
 }

 if ($_SERVER["REQUEST_METHOD"] == "GET") {
     // Obtener parámetros de la solicitud GET
     $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
     $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
     $sort = isset($_GET['sort']) ? $_GET['sort'] : null;

     // Validar los parámetros
     if ($page < 1) $page = 1;
     if ($size < 1) $size = 5;

     $ProductoSubcategoriaBusiness= new ProductoSubcategoriaBusines();
     $result =  $ProductoSubcategoriaBusiness->getAllProductoSubcategoria($page, $size, $sort);
     header('Content-Type: application/json');
     echo json_encode($result);
     exit();
 }


?>