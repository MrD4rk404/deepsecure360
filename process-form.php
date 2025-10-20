<?php
// process-form.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['name']) || !isset($input['email']) || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: name, email, message']);
    exit;
}

// Sanitize inputs
$name = filter_var(trim($input['name']), FILTER_SANITIZE_STRING);
$email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
$company = isset($input['company']) ? filter_var(trim($input['company']), FILTER_SANITIZE_STRING) : '';
$message = filter_var(trim($input['message']), FILTER_SANITIZE_STRING);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Validate other fields
if (empty($name) || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Name and message are required']);
    exit;
}

// Prepare email content
$to = "admin@deepsecure360.com"; // Change this to your actual email
$subject = "New Demo Request from DeepSecure360 Website";
$headers = "From: noreply@deepsecure360.com\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

$email_body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0a192f; color: #64ffda; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .field { margin-bottom: 15px; padding: 10px; background: white; border-radius: 3px; }
        .label { font-weight: bold; color: #0a192f; display: block; margin-bottom: 5px; }
        .footer { background: #112240; color: #8892b0; padding: 15px; text-align: center; border-radius: 0 0 5px 5px; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🚀 New Demo Request</h2>
        </div>
        <div class='content'>
            <div class='field'><span class='label'>Name:</span> $name</div>
            <div class='field'><span class='label'>Email:</span> $email</div>
            <div class='field'><span class='label'>Company:</span> " . ($company ?: 'Not provided') . "</div>
            <div class='field'><span class='label'>Message:</span><br>$message</div>
        </div>
        <div class='footer'>
            <p>This message was sent from the DeepSecure360 website contact form.</p>
        </div>
    </div>
</body>
</html>
";

// Send email
if (mail($to, $subject, $email_body, $headers)) {
    // Also save to a text file as backup
    $log_entry = date('Y-m-d H:i:s') . " - $name ($email) - $company\n";
    file_put_contents('form-submissions.log', $log_entry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for your request! We will contact you shortly.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to send message. Please try again or contact us directly at admin@deepsecure360.com'
    ]);
}
?>