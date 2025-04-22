<?php
// thumbnail.php https://pqina.nl/blog/creating-thumbnails-with-php/
function createThumbnail($sourcePath, $destPath, $width = 200, $height = 150) {
    // Obtener información de la imagen
    $info = getimagesize($sourcePath);
    if (!$info) {
        return false;
    }

    // Crear imagen según el tipo
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    // Calcular proporciones
    $sourceWidth = imagesx($source);
    $sourceHeight = imagesy($source);
    
    // Calcular nuevas dimensiones manteniendo aspecto
    $ratio = $sourceWidth / $sourceHeight;
    if ($width / $height > $ratio) {
        $width = $height * $ratio;
    } else {
        $height = $width / $ratio;
    }

    // Crear thumbnail
    $thumbnail = imagecreatetruecolor($width, $height);
    
    // Preservar transparencia para PNG y GIF
    if ($info[2] == IMAGETYPE_PNG || $info[2] == IMAGETYPE_GIF) {
        imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
    }

    // Redimensionar imagen
    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

    // Guardar thumbnail
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumbnail, $destPath, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumbnail, $destPath, 8);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumbnail, $destPath);
            break;
    }

    // Liberar memoria
    imagedestroy($source);
    imagedestroy($thumbnail);

    return true;
}

// Función para obtener o crear thumbnail
function getThumbnail($imagePath, $thumbDir = 'thumbs') {
    $sourcePath = __DIR__ . '/' . $imagePath;
    
    // Verificar si existe la imagen original
    if (!file_exists($sourcePath)) {
        return false;
    }
    
    // Crear directorio de thumbnails si no existe
    $thumbPath = __DIR__ . '/' . $thumbDir . '/' . basename($imagePath);
    if (!file_exists(dirname($thumbPath))) {
        mkdir(dirname($thumbPath), 0755, true);
    }
    
    // Crear thumbnail si no existe o si la original es más reciente
    if (!file_exists($thumbPath) || filemtime($sourcePath) > filemtime($thumbPath)) {
        if (!createThumbnail($sourcePath, $thumbPath)) {
            return $imagePath; // Si falla, devolver la original
        }
    }
    
    return $thumbDir . '/' . basename($imagePath);
}