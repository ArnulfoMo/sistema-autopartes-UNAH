
<?php

function subirFotografia(array $archivo, bool $obligatoria = true): ?string
{
    $directorio = dirname(__DIR__) . '/uploads/';
    $limite = 4 * 1024 * 1024; // 4 MB

    // seleccion
    if (
        !isset($archivo['error']) ||
        $archivo['error'] === UPLOAD_ERR_NO_FILE
    ) {
        if ($obligatoria) {
            throw new RuntimeException("Debes seleccionar una fotografía.");
        }

        return null;
    }

    // 1: upload error?
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Error al subir la fotografía.");
    }

    // 2, tamanio max
    if (
        !isset($archivo['size']) ||
        $archivo['size'] <= 0 ||
        $archivo['size'] > $limite
    ) {
        throw new RuntimeException("La fotografía no puede superar los 4 MB.");
    }

    if (!is_uploaded_file($archivo['tmp_name'])) {
        throw new RuntimeException("Archivo de subida no válido.");
    }

    // extensiones permitidas
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

    $extension = strtolower(
        pathinfo($archivo['name'], PATHINFO_EXTENSION)
    );

    if (!in_array($extension, $extensionesPermitidas, true)) {
        throw new RuntimeException("Extensión de archivo no permitida.");
    }

    // MIME
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);

    $tiposPermitidos = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp'
    ];

    if (
        !isset($tiposPermitidos[$extension]) ||
        $mime !== $tiposPermitidos[$extension]
    ) {
        throw new RuntimeException("El contenido del archivo no es una imagen válida.");
    }
    if (getimagesize($archivo['tmp_name']) === false) {
        throw new RuntimeException("El archivo no contiene una imagen válida.");
    }

    // directorio de destino
    if (!is_dir($directorio)) {
        throw new RuntimeException("No existe la carpeta uploads.");
    }

    if (!is_writable($directorio)) {
        throw new RuntimeException("No hay permisos para guardar fotografías.");
    }

    // 4: nombre criptográfico único
    $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extension;

    $destino = $directorio . $nombreArchivo;

    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        throw new RuntimeException("No se pudo guardar la fotografía.");
    }

    //retornando para DB
    return $nombreArchivo;
}
