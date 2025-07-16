<?php
// Tinker away!
use App\Models\Certificate;
use App\Models\RootCa;
use App\Services\PhpseclibX509Service;

\Illuminate\Support\Facades\Artisan::call("migrate:fresh", [
    "--force" => true,
    "--seed"  => true
]);

$keys = (new PhpseclibX509Service())
    ->generateRootCa(
        commonName      : "Root CA",
        organizationName: "localhost",
        passphrase      : "localhost"
    );

$rootCa = RootCa::query()->create([
    'name'        => "Root CA",
    'domain'      => "localhost",
    'description' => "Root Certificate",
    'private_key' => $keys["private_key"],
    'public_key'  => $keys["public_key"],
    'certificate' => $keys["certificate"],
    'passphrase'  => "localhost"
]);

file_put_contents(base_path("ssls/root-ca-certificate.pem"), $rootCa->certificate);


$cert = (new PhpseclibX509Service())
    ->generateCertificate(
        domain              : "localhost",
        altNames            : ["localhost", "127.0.0.1"],
        rootCaCertificatePem: $rootCa->certificate,
        rootCaPrivateKeyPem : $rootCa->private_key,
        rootCaPassphrase    : $rootCa->passphrase,      // rootCaPassphrase
        days                : 365
    );

$certificate = Certificate::query()->create([
    "name"        => "Client Certificate Test",
    "root_ca_id"  => $rootCa->getKey(),
    "domain"      => "localhost",
    'private_key' => $cert['private_key'],
    'public_key'  => $cert['public_key'],
    'certificate' => $cert['certificate'],
    'valid_from'  => now(),
    "expires_at"  => now()->addDays(365),
]);


file_put_contents(base_path("ssls/localhost_cert.pem"), $certificate->certificate);
file_put_contents(base_path("ssls/localhost_key.pem"), $certificate->private_key);
