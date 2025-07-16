<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\PhpseclibX509Service;

// Create an instance of the PhpseclibX509Service
$service = new PhpseclibX509Service();

try {
    echo "Generating Root CA...\n";

    // Test generateRootCa with a passphrase
    $rootCa = $service->generateRootCa(
        'Test Root CA',
        'example.com',
        'test-passphrase'
    );

    echo "Root CA generated successfully!\n";

    echo "Generating Certificate...\n";

    // Test generateCertificate with the generated root CA
    $certificate = $service->generateCertificate(
        'Test Certificate',
        'example.com',
        $rootCa['private_key'],
        $rootCa['certificate'],
        'test-passphrase',
        'cert-passphrase'
    );

    echo "Certificate generated successfully!\n";
    echo "All tests passed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
