<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

// Security: Check if user is authenticated
if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => 'No file uploaded or upload error occurred.'
    ]);
    exit;
}

$file = $_FILES['avatar'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.'
    ]);
    exit;
}

// Validate file size (max 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    echo json_encode([
        'success' => false,
        'message' => 'File size exceeds 5MB limit.'
    ]);
    exit;
}

try {
    $userId = $_SESSION['admin_id'];

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
    $storagePath = 'avatars/' . $filename;
    
    // Read file content
    $fileContent = file_get_contents($file['tmp_name']);
    
    // Upload to Supabase Storage
    $storageUrl = SUPABASE_URL . '/storage/v1/object/avatars/' . $filename;
    
    $headers = [
        'Content-Type: ' . $mimeType,
        'apikey: ' . SUPABASE_SERVICE_ROLE_KEY,
        'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
        'x-upsert: true',
    ];
    
    $ch = curl_init($storageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    if ($error) {
        throw new Exception("Failed to upload to Supabase Storage: " . $error);
    }
    
    if ($httpCode !== 200 && $httpCode !== 201) {
        $errorData = json_decode($response, true);
        $errorMsg = $errorData['message'] ?? $errorData['error'] ?? "Failed to upload. HTTP code: $httpCode";
        throw new Exception($errorMsg);
    }
    
    // Get public URL for the uploaded file
    $publicUrl = SUPABASE_URL . '/storage/v1/object/public/avatars/' . $filename;
    
    // Store public URL in session
    $_SESSION['profile_image'] = $publicUrl;

    echo json_encode([
        'success' => true,
        'message' => 'Avatar uploaded successfully.',
        'image_url' => $publicUrl
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
