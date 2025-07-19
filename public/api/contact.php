<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Here you would typically send an email
    // For now, we'll just return a success message

    echo json_encode(['success' => true, 'message' => 'شكراً لك! تم إرسال رسالتك بنجاح.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
