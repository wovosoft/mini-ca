<?php

require __DIR__ . '/vendor/autoload.php';

use phpseclib3\Crypt\RSA;

// Create a private key
$privateKey = RSA::createKey(2048);

// Test toString with passphrase as array
try {
    $passphrase = 'test-passphrase';
    $privateKeyPem = $privateKey->toString('PKCS8', ['password' => $passphrase]);
    echo "Success: toString with passphrase as array works!\n";
} catch (Exception $e) {
    echo "Error with array parameter: " . $e->getMessage() . "\n";
}

// Test toString with passphrase as string (this should fail)
try {
    $passphrase = 'test-passphrase';
    $privateKeyPem = $privateKey->toString('PKCS8', $passphrase);
    echo "Warning: toString with passphrase as string works (unexpected)!\n";
} catch (Exception $e) {
    echo "Success: toString with passphrase as string fails as expected: " . $e->getMessage() . "\n";
}

// Test toString without passphrase
try {
    $privateKeyPem = $privateKey->toString('PKCS8');
    echo "Success: toString without passphrase works!\n";
} catch (Exception $e) {
    echo "Error without passphrase: " . $e->getMessage() . "\n";
}
