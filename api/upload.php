<?php
/**
 * api/upload.php — Sikker billedupload
 * Kræver aktiv server-side session.
 */
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Kun POST tilladt']);
    exit;
}

// Tilladte filtyper
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'];
$allowed_ext   = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'];
$max_size      = 10 * 1024 * 1024; // 10MB

$file = $_FILES['image'] ?? $_FILES['file'] ?? null;

if (!$file) {
    echo json_encode(['ok' => false, 'error' => 'Ingen fil modtaget']);
    exit;
}

// Tjek for upload-fejl
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE   => 'Filen er for stor (server-grænse)',
        UPLOAD_ERR_FORM_SIZE  => 'Filen er for stor',
        UPLOAD_ERR_PARTIAL    => 'Filen blev kun delvist uploadet',
        UPLOAD_ERR_NO_FILE    => 'Ingen fil valgt',
        UPLOAD_ERR_NO_TMP_DIR => 'Mangler midlertidig mappe',
        UPLOAD_ERR_CANT_WRITE => 'Kunne ikke skrive filen',
    ];
    echo json_encode(['ok' => false, 'error' => $errors[$file['error']] ?? 'Upload-fejl']);
    exit;
}

// Tjek filstørrelse
if ($file['size'] > $max_size) {
    echo json_encode(['ok' => false, 'error' => 'Filen er for stor. Maks 10MB.']);
    exit;
}

// Tjek MIME-type (brug finfo for sikkerhed — ikke bare $_FILES['type'])
$finfo     = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($file['tmp_name']);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode(['ok' => false, 'error' => 'Filtypen er ikke tilladt. Brug billeder eller PDF.']);
    exit;
}

// Tjek filendelse
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed_ext)) {
    echo json_encode(['ok' => false, 'error' => 'Ugyldig filendelse.']);
    exit;
}

// Bestem destination-mappe fra POST-parameter
$folder = $_POST['folder'] ?? 'general';

// Whitelist af tilladte mapper — ingen directory traversal mulig
$allowed_folders = ['projects', 'general', 'cover', 'photo', 'docs'];
if (!in_array($folder, $allowed_folders)) {
    $folder = 'general';
}

$upload_dir = __DIR__ . "/../assets/{$folder}/";

// Opret mappe hvis den ikke findes
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generer sikkert filnavn — brug original navn men sanitér det
$original_name = pathinfo($file['name'], PATHINFO_FILENAME);
$safe_name     = preg_replace('/[^a-zA-Z0-9_-]/', '-', $original_name);
$safe_name     = strtolower(trim($safe_name, '-'));
$safe_name     = preg_replace('/-+/', '-', $safe_name); // ingen dobbelt-bindestreger
$filename      = $safe_name . '.' . $ext;
$filepath      = $upload_dir . $filename;

// Hvis filen allerede eksisterer, tilføj timestamp
if (file_exists($filepath)) {
    $filename = $safe_name . '-' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;
}

// Flyt filen fra temp til destination
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['ok' => false, 'error' => 'Kunne ikke gemme filen. Tjek mapperettigheder.']);
    exit;
}

// Returnér den relative sti som bruges i content.json
$relative_path = "assets/{$folder}/{$filename}";

echo json_encode([
    'ok'   => true,
    'path' => $relative_path,
    'name' => $filename,
    'size' => $file['size'],
    'type' => $mime_type,
]);
