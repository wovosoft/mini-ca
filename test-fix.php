<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\PhpseclibX509Service;

// Create an instance of the PhpseclibX509Service
$service = new PhpseclibX509Service();

try {
    // Test generateRootCa with a passphrase
    $result = $service->generateRootCa(
        'Test Root CA',
        'example.com',
        'test-passphrase'
    );

    echo "Root CA generated successfully with passphrase!\n";

    // Test generateRootCa without a passphrase
    $result = $service->generateRootCa(
        'Test Root CA 2',
        'example2.com'
    );

    echo "Root CA generated successfully without passphrase!\n";

    // Test generateCertificate with the generated root CA
    $certificate = $service->generateCertificate(
        'Test Certificate',
        'example.com',
        $result['private_key'],
        $result['certificate'],
        null,
        'cert-passphrase'
    );

    echo "Certificate generated successfully with passphrase!\n";

    // Test generateCertificate without a passphrase
    $certificate = $service->generateCertificate(
        'Test Certificate 2',
        'example2.com',
        $result['private_key'],
        $result['certificate']
    );

    echo "Certificate generated successfully without passphrase!\n";

    echo "All tests passed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
