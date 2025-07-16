<?php

namespace App\Services;

use phpseclib3\Crypt\RSA;
use phpseclib3\File\X509;
use Random\RandomException;
use RuntimeException;

class PhpseclibX509Service
{
    /**
     * Generate a Root CA certificate with PSS padding and SHA256 hash.
     *
     * @throws RandomException
     * @throws RuntimeException
     */
    public function generateRootCa(
        string $commonName,
        string $organizationName,
        string $passphrase = '',
        int $days = 3650
    ): array
    {
        try {
            // 1. Key Generation with explicit PKCS1 padding and SHA256 hash
            $privateKey = RSA::createKey(4096)
                ->withPadding(RSA::SIGNATURE_PKCS1)
                ->withHash('sha256');
            
            $publicKey = $privateKey->getPublicKey();

            // 2. Create and configure the certificate object
            $x509 = new X509();
            $x509->setPrivateKey($privateKey);
            $x509->setPublicKey($publicKey);
            $x509->setDN([
                'countryName'         => 'BD',
                'stateOrProvinceName' => 'Dhaka',
                'localityName'        => 'Dhaka',
                'organizationName'    => $organizationName,
                'commonName'          => $commonName,
            ]);
            $x509->setStartDate('-1 day');
            $x509->setEndDate(" + $days days");
            $x509->setSerialNumber((string)base_convert(bin2hex(random_bytes(10)), 16, 10));

            // 3. Set CA extensions
            $x509->makeCA();
            $x509->setExtension('id-ce-subjectKeyIdentifier', 'hash');

            // 4. Self-sign the certificate
            $result = $x509->sign($x509, $x509);

            if ($result === false) {
                throw new RuntimeException('Failed to sign the certificate.');
            }

            // 5. Save and Validate
            $certData = $x509->saveX509($result);
            if ($certData === false) {
                throw new RuntimeException('Failed to save X509 certificate');
            }

            $validator = new X509();
            if (!$validator->loadX509($certData) || !$validator->loadCA($certData) || !$validator->validateSignature()) {
                throw new RuntimeException('Generated certificate failed validation.');
            }

            return [
                'private_key' => $passphrase
                    ? $privateKey->toString('PKCS8', ['password' => $passphrase])
                    : $privateKey->toString('PKCS8'),
                'public_key'  => $publicKey->toString('PKCS8'),
                'certificate' => $certData,
            ];

        } catch (\Exception $e) {
            throw new RuntimeException('Certificate generation failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate a leaf certificate signed by the given Root CA.
     *
     * @throws RandomException
     */
    public function generateCertificate(
        string $domain,
        array $altNames,
        string $rootCaCertificatePem,
        string $rootCaPrivateKeyPem,
        string $rootCaPassphrase = '',
        int $days = 398
    ): array
    {
        // 1. Load CA private key with explicit PKCS1 padding and SHA256 hash
        $caPrivateKey = RSA::loadPrivateKey($rootCaPrivateKeyPem, $rootCaPassphrase)
            ->withPadding(RSA::SIGNATURE_PKCS1)
            ->withHash('sha256');

        $issuer = new X509();
        $issuer->loadX509($rootCaCertificatePem);
        $issuer->setPrivateKey($caPrivateKey);

        // 2. Create leaf key with explicit PKCS1 padding and SHA256 hash
        $leafPrivateKey = RSA::createKey(2048)
            ->withPadding(RSA::SIGNATURE_PKCS1)
            ->withHash('sha256');
        $leafPublicKey  = $leafPrivateKey->getPublicKey();

        // 3. Configure subject
        $subject = new X509();
        $subject->setPublicKey($leafPublicKey);
        $subject->setDNProp('id-at-countryName', 'BD');
        $subject->setDNProp('id-at-stateOrProvinceName', 'Dhaka');
        $subject->setDNProp('id-at-localityName', 'Dhaka');
        $subject->setDNProp('id-at-organizationName', $issuer->getDNProp('id-at-organizationName')[0]);
        $subject->setDNProp('id-at-commonName', $domain);

        $subject->setStartDate('-1 day');
        $subject->setEndDate(" + $days days");
        $subject->setSerialNumber(base_convert(bin2hex(random_bytes(10)), 16, 10));

        // 4. Set leaf certificate extensions
        $subject->setExtension('id-ce-basicConstraints', ['cA' => false], true);
        $subject->setExtension('id-ce-keyUsage', ['digitalSignature', 'keyEncipherment'], true);
        $subject->setExtension('id-ce-extKeyUsage', ['serverAuth', 'clientAuth'], false);
        $subject->setExtension('id-ce-subjectKeyIdentifier', 'hash');

        $allNames = array_unique(array_merge([$domain], $altNames));

        $sans = [];
        foreach ($allNames as $name) {
            if (filter_var($name, FILTER_VALIDATE_IP)) {
                $sans[] = ['iPAddress' => $name];
            } else {
                $sans[] = ['dNSName' => $name];
            }
        }


        $subject->setExtension(
            'id-ce-subjectAltName',
            $sans
        );

        $subject->setExtension('id-ce-authorityKeyIdentifier', [
            'keyIdentifier' => $issuer->getExtension('id-ce-subjectKeyIdentifier')
        ]);

        // 5. Sign the certificate
        $x509 = new X509();
        $cert = $x509->sign($issuer, $subject);

        return [
            'private_key' => $leafPrivateKey->toString('PKCS8'),
            'public_key'  => $leafPublicKey->toString('PKCS8'),
            'certificate' => $x509->saveX509($cert),
        ];
    }
}

