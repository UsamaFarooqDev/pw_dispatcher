<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

// Security: Check if user is authenticated
if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
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

try {
    $userId = $_SESSION['user']['id'];
    $accessToken = $_SESSION['access_token'];
    
    // Get current profile image URL from session or user metadata
    $profileImage = $_SESSION['profile_image'] ?? null;
    
    // If not in session, try to get from user metadata
    if (!$profileImage) {
        try {
            $userUrl = SUPABASE_URL . '/auth/v1/user';
            $userCh = curl_init($userUrl);
            curl_setopt_array($userCh, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'apikey: ' . SUPABASE_ANON_KEY,
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ],
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $userResponse = curl_exec($userCh);
            $userHttpCode = curl_getinfo($userCh, CURLINFO_HTTP_CODE);
            curl_close($userCh);
            
            if ($userHttpCode === 200) {
                $userData = json_decode($userResponse, true);
                $profileImage = $userData['user_metadata']['avatar_url'] ?? null;
            }
        } catch (Exception $e) {
            // Continue even if we can't fetch user metadata
        }
    }
    
    // Track if deletion was successful
    $deleteSuccess = false;
    $deleteHttpCode = null;
    
    // Delete the file from Supabase Storage if it exists
    if ($profileImage) {
        // Check if it's a Supabase Storage URL
        if (strpos($profileImage, '/storage/v1/object/public/avatars/') !== false || 
            strpos($profileImage, '/storage/v1/object/avatars/') !== false) {
            // Extract filename from public URL
            // URL format: https://xxx.supabase.co/storage/v1/object/public/avatars/filename.jpg
            // or: https://xxx.supabase.co/storage/v1/object/avatars/filename.jpg
            $parts = explode('/avatars/', $profileImage);
            if (count($parts) === 2) {
                $filename = trim($parts[1]);
                // Remove any query parameters
                $filename = explode('?', $filename)[0];
                
                // Delete from Supabase Storage
                // Use the bucket/object path format: /storage/v1/object/{bucket}/{path}
                $deleteUrl = SUPABASE_URL . '/storage/v1/object/avatars/' . rawurlencode($filename);
                
                // Use service role key for storage operations (more reliable)
                $deleteHeaders = [
                    'apikey: ' . SUPABASE_SERVICE_ROLE_KEY,
                    'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY
                ];
                
                $ch = curl_init($deleteUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_HTTPHEADER, $deleteHeaders);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_VERBOSE, false);
                
                $deleteResponse = curl_exec($ch);
                $deleteHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $deleteError = curl_error($ch);
                
                
                // Check if deletion was successful
                if ($deleteHttpCode === 200 || $deleteHttpCode === 204) {
                    $deleteSuccess = true;
                }
                
                // Log response for debugging
                $deleteErrorMessage = null;
                if ($deleteError) {
                    $deleteErrorMessage = "cURL Error: " . $deleteError;
                    error_log("Error deleting avatar from Supabase Storage: " . $deleteError . " - File: " . $filename . " - URL: " . $deleteUrl);
                } elseif ($deleteHttpCode !== 200 && $deleteHttpCode !== 204) {
                    $errorData = json_decode($deleteResponse, true);
                    $deleteErrorMessage = $errorData['message'] ?? $errorData['error'] ?? "HTTP code: $deleteHttpCode";
                    if (!$deleteErrorMessage || $deleteErrorMessage === "HTTP code: $deleteHttpCode") {
                        $deleteErrorMessage = "Delete failed with HTTP code: $deleteHttpCode. Response: " . substr($deleteResponse, 0, 200);
                    }
                    error_log("Failed to delete avatar from Supabase Storage. HTTP code: $deleteHttpCode - Message: " . $deleteErrorMessage . " - File: " . $filename . " - URL: " . $deleteUrl);
                }
            }
        }
        // Also handle old local file paths for backward compatibility
        elseif (strpos($profileImage, 'uploads/') === 0 || strpos($profileImage, '/uploads/') !== false) {
            $localPath = strpos($profileImage, '/uploads/') !== false 
                ? __DIR__ . '/..' . strstr($profileImage, '/uploads/')
                : __DIR__ . '/../' . $profileImage;
            if (file_exists($localPath) && is_file($localPath)) {
                unlink($localPath);
            }
        }
    }
    
    // Clear profile image from session
    unset($_SESSION['profile_image']);
    
    // Update user metadata in Supabase Auth to remove avatar_url
    $updateUrl = SUPABASE_URL . '/auth/v1/user';
    $updatePayload = json_encode([
        'user_metadata' => [
            'avatar_url' => null
        ]
    ]);
    
    $updateHeaders = [
        'Content-Type: application/json',
        'apikey: ' . SUPABASE_ANON_KEY,
        'Authorization: Bearer ' . $accessToken
    ];
    
    $ch = curl_init($updateUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $updatePayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $updateHeaders);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Don't fail if metadata update fails - file is already deleted
    curl_exec($ch);
    
    $message = 'Avatar removed successfully.';
    $responseData = [
        'success' => true,
        'message' => $message,
        'deleted_from_storage' => $deleteSuccess
    ];
    
    // Include error details if deletion failed (for debugging)
    if (!$deleteSuccess && isset($deleteErrorMessage)) {
        $responseData['delete_error'] = $deleteErrorMessage;
        $responseData['delete_http_code'] = $deleteHttpCode;
    }
    
    echo json_encode($responseData);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
