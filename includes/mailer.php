<?php
	//@TODO: Move this into functions.php and call it using the form action!

    // Only process POST reqeusts.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // Get the form fields and remove whitespace.
        $name = strip_tags(trim($_POST['data'][0]['name']));
		$name = str_replace(array("\r","\n"),array(" "," "),$name);
        
        $email = filter_var(trim($_POST['data'][0]['email']), FILTER_SANITIZE_EMAIL);
        $message = trim($_POST['data'][0]['message']);

        //Verify required fields.
        if ( empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<i class='fa fa-times error'></i> Please verify all required fields are filled out and try again.";
            exit;
        }

        // Set the recipient email address.
        $recipient = "nobody@nobody.co";

        // Set the email subject.
        $subject = "Contact form from $name via Nebula.";

        // Build the email content.
        $email_content = "Name: $name\n";
        $email_content .= "Email: $email\n\n";
        $email_content .= "Message:\n$message\n";

        // Build the email headers.
        $email_headers = "From: $name <$email>";

        // Send the email.
        if ( mail($recipient, $subject, $email_content, $email_headers) ) {
			echo "<i class='fa fa-check success'></i> Thank you! Your message has been sent."; //Add a font-awesome icon here
        } else {
			echo "<i class='fa fa-times error'></i> Your contact form could not be sent. Please try again later. (maybe put a mailto link here?)";
        }

    } else {
        echo "No data received.";
    }
?>