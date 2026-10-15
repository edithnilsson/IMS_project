<?php

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';

$captcha = '';

for ($i = 0; $i < 5; $i++) {
    $captcha .= $characters[random_int(0, strlen($characters) - 1)];
}

$_SESSION['captcha_answer'] = $captcha;

header('Content-Type: image/png');

$image = imagecreatetruecolor(150, 50);

imagealphablending($image, false);
imagesavealpha($image, true);
$background = imagecolorallocatealpha($image, 255, 255, 255, 127);
imagefill($image, 0, 0, $background);
$text_color = imagecolorallocate($image, 0, 74, 173);


imagestring($image, 5, 35, 15, $captcha, $text_color);

imagepng($image);

imagedestroy($image);

?>