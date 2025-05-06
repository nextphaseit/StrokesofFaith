<?php
if ($_POST) {
    $to_Email = "alexis.archer@strokesoffaith.art";
    $subject = 'Strokes Of Faith - New Contact Inquiry';

    // Check for AJAX request
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        $output = json_encode(array('type' => 'error', 'text' => 'Request must come from Ajax'));
        die($output);
    }

    // Check for required fields
    if (!isset($_POST["userName"]) || !isset($_POST["userEmail"]) || !isset($_POST["userMessage"]) || !isset($_POST["g-recaptcha-response"])) {
        $output = json_encode(array('type' => 'error', 'text' => 'Input fields are empty or reCAPTCHA not completed!'));
        die($output);
    }

    // Sanitize input
    $user_Name = filter_var($_POST["userName"], FILTER_SANITIZE_STRING);
    $user_Email = filter_var($_POST["userEmail"], FILTER_SANITIZE_EMAIL);
    $user_Phone = filter_var($_POST["userPhone"], FILTER_SANITIZE_STRING);
    $user_Message = filter_var($_POST["userMessage"], FILTER_SANITIZE_STRING);
    $recaptcha_response = $_POST["g-recaptcha-response"];

    // reCAPTCHA validation
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = array(
        'secret' => '6LcQizArAAAAADR7B-P32_6zw1soiMuZ9JsVY6Ac',
        'response' => $recaptcha_response
    );

    $options = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => "Content-type: application/x-www-form-urlencoded
",
            'content' => http_build_query($recaptcha_data)
        )
    );

    $context  = stream_context_create($options);
    $verify = file_get_contents($recaptcha_url, false, $context);
    $captcha_success = json_decode($verify);

    if (!$captcha_success->success) {
        $output = json_encode(array('type' => 'error', 'text' => 'reCAPTCHA verification failed!'));
        die($output);
    }

    // Additional validation
    if (strlen($user_Name) < 3) {
        $output = json_encode(array('type' => 'error', 'text' => 'Name is too short or empty!'));
        die($output);
    }
    if (!filter_var($user_Email, FILTER_VALIDATE_EMAIL) || preg_match("/[\n]/", $user_Email)) {
        $output = json_encode(array('type' => 'error', 'text' => 'Invalid email!'));
        die($output);
    }
    if (strlen($user_Message) < 5) {
        $output = json_encode(array('type' => 'error', 'text' => 'Message is too short!'));
        die($output);
    }

    // Email headers and message
    $headers = 'From: '.$user_Email."\r\n".
               'Reply-To: '.$user_Email."\r\n" .
               'X-Mailer: PHP/' . phpversion();

    $message_body = "Name: $user_Name\nEmail: $user_Email\nPhone: $user_Phone\nMessage:\n$user_Message";

    // Sending email
    $sentMail = mail($to_Email, $subject, $message_body, $headers);

    if (!$sentMail) {
        $output = json_encode(array('type' => 'error', 'text' => 'Could not send mail! Please check your PHP mail configuration.'));
        die($output);
    } else {
        $output = json_encode(array('type' => 'message', 'text' => 'Hi '.$user_Name .' Thank you for your email.'));
        die($output);
    }
}
?>
