<?php

namespace App\Services;

use phpseclib3\Crypt\RSA;
use phpseclib3\File\X509;
use phpseclib3\Math\BigInteger;
use Random\RandomException;

class PhpseclibX509Service
{
    /**
     * Generates a Root CA certificate and its corresponding private key.
     *
     * @param string $commonName The Common Name for the CA (e.g., "My Local CA").
     * @param string $organizationName The Organization Name (e.g., "My Company").
     * @param string $passphrase A strong passphrase to encrypt the private key.
     * @param int $days Validity period in days (e.g., 7300 for 20 years).
     * @return array Contains 'private_key', 'public_key', and 'certificate'.
     * @throws RandomException
     */
    public function generateRootCa(string $commonName, string $organizationName, string $passphrase, int $days = 7300): array
    {
        // 1. Create a new private key for the root CA
        $caPrivateKey = RSA::createKey(4096);
        $caPublicKey = $caPrivateKey->getPublicKey();

        // 2. Define the Distinguished Name (DN) for the root CA
        $subjectDN = [
            'id-at-countryName' => 'US',
            'id-at-stateOrProvinceName' => 'California',
            'id-at-localityName' => 'San Francisco',
            'id-at-organizationName' => $organizationName,
            'id-at-commonName' => $commonName,
        ];

        // 3. Create the subject certificate object
        $subject = new X509();
        $subject->setPublicKey($caPublicKey);
        $subject->setDN($subjectDN);
        $subject->setStartDate('-1 day');
        $subject->setEndDate("-$days days");
        $subject->setSerialNumber(new BigInteger(random_bytes(20)));

        // 4. Set extensions for a root CA certificate
        $subject->setExtension('id-ce-basicConstraints', ['cA' => true], true);
        $subject->setExtension('id-ce-keyUsage', ['keyCertSign', 'cRLSign'], true);
        $subject->setExtension('id-ce-subjectKeyIdentifier', [], false);

        // 5. Create the issuer certificate object (self-signed)
        $issuer = new X509();
        $issuer->setPrivateKey($caPrivateKey);
        $issuer->setDN($subjectDN); // Issuer DN is the same as subject DN

        // 6. Sign the certificate
        $x509 = new X509();
        $result = $x509->sign($issuer, $subject);

        // 7. Return the keys and the certificate
        return [
            'private_key' => $caPrivateKey->toString('PKCS8', ['password' => $passphrase]),
            'public_key' => $caPublicKey->toString('PKCS8'),
            'certificate' => $x509->saveX509($result),
        ];
    }

    /**
     * Generates a leaf certificate signed by a given Root CA.
     *
     * @param string $domain The primary domain for the certificate (e.g., "myapp.local").
     * @param array $altNames A list of alternative domains (SANs).
     * @param string $rootCaCertificatePem The Root CA certificate (PEM format).
     * @param string $rootCaPrivateKeyPem The Root CA's private key (PEM format).
     * @param string $rootCaPassphrase The passphrase for the Root CA's private key.
     * @param int $days Validity period in days (e.g., 398).
     * @return array Contains 'private_key', 'public_key', and 'certificate'.
     * @throws RandomException
     */
    public function generateCertificate(string $domain, array $altNames, string $rootCaCertificatePem, string $rootCaPrivateKeyPem, string $rootCaPassphrase, int $days = 398): array
    {
        // 1. Load the CA's private key and certificate
        $caPrivateKey = RSA::loadPrivateKey($rootCaPrivateKeyPem, $rootCaPassphrase);
        $issuer = new X509();
        $issuer->loadX509($rootCaCertificatePem);
        $issuer->setPrivateKey($caPrivateKey);

        // 2. Create a new private key for the leaf certificate
        $leafPrivateKey = RSA::createKey(2048);
        $leafPublicKey = $leafPrivateKey->getPublicKey();

        // 3. Define the DN for the leaf certificate
        $subjectDN = [
            // Inherit organization from CA for consistency
            'id-at-organizationName' => $issuer->getDNProp('id-at-organizationName')[0],
            'id-at-commonName' => $domain,
        ];

        // 4. Create the subject certificate object
        $subject = new X509();
        $subject->setPublicKey($leafPublicKey);
        $subject->setDN($subjectDN);
        $subject->setStartDate('-1 day');
        $subject->setEndDate("-$days days");
        $subject->setSerialNumber(new BigInteger(random_bytes(20)));

        // 5. Set extensions for a leaf (server/client) certificate
        $subject->setExtension('id-ce-basicConstraints', ['cA' => false], true);
        $subject->setExtension('id-ce-keyUsage', ['digitalSignature', 'keyEncipherment'], true);
        $subject->setExtension('id-ce-extKeyUsage', ['serverAuth', 'clientAuth'], false);
        $subject->setExtension('id-ce-subjectKeyIdentifier', [], false);

        // Add Subject Alternative Names (SANs)
        $sans = array_unique(array_merge([$domain], $altNames));
        $subject->setExtension('id-ce-subjectAltName', array_map(fn($san) => ['dNSName' => $san], $sans));

        // Link to the CA via Authority Key Identifier
        $subject->setExtension('id-ce-authorityKeyIdentifier', [
            'keyIdentifier' => $issuer->getExtension('id-ce-subjectKeyIdentifier')
        ]);

        // 6. Sign the certificate
        $x509 = new X509();
        $result = $x509->sign($issuer, $subject);

        // 7. Return the new keys and certificate
        return [
            'private_key' => $leafPrivateKey->toString('PKCS8'),
            'public_key' => $leafPublicKey->toString('PKCS8'),
            'certificate' => $x509->saveX509($result),
        ];
    }
}
