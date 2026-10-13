<?php

function handleDangerousCase(
    $conn,
    $pathogen_id,
    $diagnosis_date,
    $hospital_id
) {
    // Get the pathogen name
    $sql = "SELECT pathogen_name
            FROM Pathogen
            WHERE pathogen_id = ?
            AND pathogen_dangerous = TRUE";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pathogen_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $pathogen = $result->fetch_assoc();

    $stmt->close();

    // Stop if the pathogen is not marked dangerous
    if (!$pathogen) {
        return true;
    }

    // Get the hospital name
    $sql = "SELECT hospital_name
            FROM Hospital
            WHERE hospital_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $hospital_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $hospital = $result->fetch_assoc();

    $stmt->close();

    if (!$hospital) {
        error_log("Could not find hospital for dangerous case.");
        return false;
    }

    // Get active users in the same hospital and also epidemiologists/chief epidemiologists from other hospitals
    $sql = "SELECT user_email
            FROM System_user
            WHERE user_status = 'active'
            AND user_email IS NOT NULL
            AND user_email <> ''
            AND (
                hospital_id = ?
                OR (
                    hospital_id <> ?
                    AND role_id IN (3, 4)
                )
            )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hospital_id, $hospital_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $recipients = [];

    while ($row = $result->fetch_assoc()) {
        $recipients[] = $row['user_email'];
    }

    $stmt->close();

    if (empty($recipients)) {
    error_log("No recipients found for dangerous-case notification.");
    return false;
    }

    // Load the mail stuff
    try {
        $mail = require __DIR__ . "/../src/mailer.php";
    } catch (\Throwable $e) {
        error_log("Could not configure dangerous-case email: "
            . $e->getMessage());
        return false;
    }

    $mail_failed = false;

    // Prepare the email content
    $subject = "Dangerous pathogen notification";

    $body = "
        <h2>Dangerous pathogen notification</h2>
        <p>A dangerous pathogen has been reported in InfTraction.</p>
        <p><strong>Pathogen:</strong> "
            . htmlspecialchars($pathogen['pathogen_name'], ENT_QUOTES, 'UTF-8')
            . "</p>
        <p><strong>Hospital:</strong> "
            . htmlspecialchars($hospital['hospital_name'], ENT_QUOTES, 'UTF-8')
            . "</p>
        <p><strong>Diagnosis date:</strong> "
            . htmlspecialchars($diagnosis_date, ENT_QUOTES, 'UTF-8')
            . "</p>
        <p>Please review the case in InfTraction.</p>
    ";

    // Send a separate email to each recipient
    foreach ($recipients as $email) {
        try {
            $mail->clearAddresses();
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody =
                "Dangerous pathogen reported: "
                . $pathogen['pathogen_name']
                . " at "
                . $hospital['hospital_name']
                . ". Diagnosis date: "
                . $diagnosis_date
                . ".";

            $mail->addAddress($email);
            $mail->send();

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $mail_failed = true;
            error_log("Dangerous-case email failed: " . $e->getMessage());
        }
    }

    if ($mail_failed) {
        return false;
    }

    return true;
}