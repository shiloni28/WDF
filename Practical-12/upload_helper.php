
<?php

const MAX_POSTER_SIZE = 2 * 1024 * 1024;

function uploadPoster($file, $oldPoster = null) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [
            'ok' => true,
            'poster' => $oldPoster,
            'new_file' => false
        ];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Poster upload failed. Try again.');
    }

    if ($file['size'] <= 0 || $file['size'] > MAX_POSTER_SIZE) {
        throw new RuntimeException('Poster must be smaller than or equal to 2 MB.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid uploaded file.');
    }

    $imageInfo = @getimagesize($file['tmp_name']);

    if ($imageInfo === false) {
        throw new RuntimeException('The uploaded file is not a valid image.');
    }

    $mimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (
        !isset($mimeTypes[$mime]) ||
        $imageInfo['mime'] !== $mime
    ) {
        throw new RuntimeException(
            'Only valid JPG, PNG and WebP images are allowed.'
        );
    }

    $directory = __DIR__ . '/uploads';

    if (!is_dir($directory) || !is_writable($directory)) {
        throw new RuntimeException(
            'Uploads folder is missing or not writable.'
        );
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $mimeTypes[$mime];
    $destination = $directory . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Unable to save the poster.');
    }

    return [
        'ok' => true,
        'poster' => $filename,
        'new_file' => true
    ];
}

function removePoster($filename) {
    if (!$filename) {
        return;
    }
    if (!preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $filename)) {
        return;
    }

    $path = __DIR__ . '/uploads/' . $filename;

    if (is_file($path)) {
        unlink($path);
    }
}
?>
