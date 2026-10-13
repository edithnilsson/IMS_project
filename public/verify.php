<?php
require __DIR__ . '/../config/db.php';

// Guard against visiting this page with no token in the URL at all
// (e.g. someone just types /verify.php)
if (empty($_GET['token'])) {
    die('Invalid verification link.');
}

// Hash the token that came in through the URL (?token=...)
// We store only the hash in the database, so we hash the incoming
// token the same way before comparing
$token_hash = hash("sha256", $_GET["token"]);

// Look up a user whose stored token hash matches this one
$stmt = $conn->prepare("SELECT user_id FROM System_user WHERE user_verification_token_hash = ?");
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    // Match found - change the verification token to NULL and in the login check NULL = verified
    $update = $conn->prepare("UPDATE System_user SET user_verification_token_hash = NULL WHERE user_id = ?");
    $update->bind_param("s", $user["user_id"]);
    $update->execute();
    echo "<script>alert('Account verified. You can now log in.'); window.location.href='/index.php';</script>";
} else {
    echo "<script>alert('Invalid or expired verification link.'); window.location.href='verify.php'</script>";
}