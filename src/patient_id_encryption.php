<?php

declare(strict_types=1);

//Load and validate the encryption and HMAC keys
function loadPatientIdKeys(): array
{
    static $keys = null;

    if ($keys !== null) {
        return $keys;
    }

    $config = require __DIR__ . '/patient_id_keys.php';

    $encryptionKey = base64_decode(
        $config['encryption_key'] ?? '',
        true
    );

    $hmacKey = base64_decode(
        $config['hmac_key'] ?? '',
        true
    );

    if (
        $encryptionKey === false ||
        strlen($encryptionKey) !== 32 ||
        $hmacKey === false ||
        strlen($hmacKey) !== 32
    ) {
        throw new RuntimeException('Invalid patient ID key configuration.');
    }

    $keys = [
        'encryption_key' => $encryptionKey,
        'hmac_key' => $hmacKey,
    ];

    return $keys;
}

//Create an HMAC for matching a patient ID
function patientIdHmac($hospitalPatientId)
{
    $keys = loadPatientIdKeys();

    return hash_hmac(
        'sha256',
        trim($hospitalPatientId),
        $keys['hmac_key']
    );
}

//Encrypt patient ID using AES-256-GCM
function encryptPatientId($hospitalPatientId)
{
    $keys = loadPatientIdKeys();

    $nonce = random_bytes(12);
    $authTag = '';

    $ciphertext = openssl_encrypt(
        trim($hospitalPatientId),
        'aes-256-gcm',
        $keys['encryption_key'],
        OPENSSL_RAW_DATA,
        $nonce,
        $authTag,
        '',
        16
    );

    if ($ciphertext === false) {
        throw new RuntimeException('Patient ID encryption failed.');
    }

    return [
        'ciphertext' => $ciphertext,
        'nonce' => $nonce,
        'auth_tag' => $authTag,
    ];
}


//Decrypt patient ID
function decryptPatientId($ciphertext, $nonce, $authTag)
{
    $keys = loadPatientIdKeys();

    $patientId = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        $keys['encryption_key'],
        OPENSSL_RAW_DATA,
        $nonce,
        $authTag,
        ''
    );

    if ($patientId === false) {
        throw new RuntimeException('Patient ID decryption failed.');
    }

    return $patientId;
}