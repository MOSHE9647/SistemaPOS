<?php

function convertToWebP($sourcePath, $newWidth = null, $newHeight = null, $quality = 80, $removeOriginal = true) {
    // Obtiene la información de la imagen original
    list($originalWidth, $originalHeight, $imageType) = getimagesize($sourcePath);
    $dir = pathinfo($sourcePath, PATHINFO_DIRNAME);
    $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
    $destinationPath = $dir . DIRECTORY_SEPARATOR . $filename . '.webp';

    // Crea la imagen original según el tipo de imagen
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        default:
            return $sourcePath;
    }

    // Si no se especifican nuevas dimensiones, se mantienen las originales
    $newWidth = $newWidth ?? $originalWidth;
    $newHeight = $newHeight ?? $originalHeight;

    // Crea una nueva imagen redimensionada
    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

    // Mantiene la transparencia si es PNG
    if ($imageType == IMAGETYPE_PNG) {
        imagecolortransparent($resizedImage, imagecolorallocatealpha($resizedImage, 0, 0, 0, 127));
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
    }

    // Redimensiona la imagen original
    imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

    // Guarda la imagen en formato WebP
    imagewebp($resizedImage, $destinationPath, $quality);

    // Libera la memoria
    imagedestroy($sourceImage);
    imagedestroy($resizedImage);

    // Elimina la imagen original si se especifica
    if ($removeOriginal) { unlink($sourcePath); }

    // Devuelve la ruta de la imagen convertida eliminando la ruta base
    return $destinationPath;
}

$source = dirname(__DIR__) . '/view/static/img/user-copy.png';
$destination = convertToWebP($source, 512, 512, 100, false);
var_dump($destination);

?>