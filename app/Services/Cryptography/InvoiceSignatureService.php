<?php

namespace App\Services\Cryptography;

use App\Models\RsaKey;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class InvoiceSignatureService
{
    private const HASH_ALGORITHM = 'sha256';

    private const SALT_LENGTH = 32;

    public function generateKeyPair(string $version, int $keySize = 2048): RsaKey
    {
        if (RsaKey::where('version', $version)->exists()) {
            throw new RuntimeException("RSA key version [{$version}] already exists.");
        }

        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => $keySize,
            'digest_alg' => self::HASH_ALGORITHM,
            'config' => base_path('config/openssl.cnf'),
        ]);
        if ($privateKey === false) {
            throw $this->opensslFailure('RSA key generation failed');
        }

        if (! openssl_pkey_export($privateKey, $privatePem, null, ['config' => base_path('config/openssl.cnf')])) {
            throw $this->opensslFailure('RSA private key export failed');
        }

        $details = openssl_pkey_get_details($privateKey);
        if ($details === false || ! isset($details['key'])) {
            throw $this->opensslFailure('RSA public key extraction failed');
        }

        RsaKey::where('status', 'active')->update(['status' => 'retired', 'retired_at' => now()]);

        return RsaKey::create([
            'version' => $version,
            'public_key' => $details['key'],
            'encrypted_private_key' => Crypt::encryptString($privatePem),
            'key_algorithm' => 'RSA-PSS',
            'key_size' => $details['bits'],
            'status' => 'active',
            'generated_at' => now(),
        ]);
    }

    public function activeKey(): RsaKey
    {
        $key = RsaKey::where('status', 'active')->latest('generated_at')->first();

        return $key ?? $this->generateKeyPair('v1');
    }

    public function sign(string $payload, RsaKey $key): string
    {
        $privateKey = openssl_pkey_get_private(Crypt::decryptString($key->encrypted_private_key));
        if ($privateKey === false) {
            throw $this->opensslFailure('RSA private key could not be loaded');
        }

        $details = openssl_pkey_get_details($privateKey);
        if ($details === false || ! isset($details['bits'])) {
            throw $this->opensslFailure('RSA private key details could not be read');
        }

        $encodedMessage = $this->encodePss($payload, $details['bits']);
        $modulusLength = intdiv($details['bits'] + 7, 8);
        $encodedMessage = str_pad($encodedMessage, $modulusLength, "\0", STR_PAD_LEFT);
        if (! openssl_private_encrypt($encodedMessage, $signature, $privateKey, OPENSSL_NO_PADDING)) {
            throw $this->opensslFailure('RSA-PSS signing failed');
        }

        return base64_encode($signature);
    }

    public function verify(string $payload, string $signatureBase64, string $publicKey): bool
    {
        $signature = base64_decode($signatureBase64, true);
        if ($signature === false) {
            return false;
        }

        $key = openssl_pkey_get_public($publicKey);
        if ($key === false) {
            return false;
        }

        $details = openssl_pkey_get_details($key);
        if ($details === false || ! isset($details['bits'])) {
            return false;
        }

        $modulusLength = intdiv($details['bits'] + 7, 8);
        if (strlen($signature) !== $modulusLength) {
            return false;
        }

        if (! openssl_public_decrypt($signature, $encodedMessage, $key, OPENSSL_NO_PADDING)) {
            return false;
        }

        $encodedLength = intdiv($details['bits'] + 6, 8);
        $encodedMessage = substr($encodedMessage, $modulusLength - $encodedLength);

        return $this->verifyPss($payload, $encodedMessage, $details['bits']);
    }

    private function encodePss(string $payload, int $modulusBits): string
    {
        $hashLength = 32;
        $encodedBits = $modulusBits - 1;
        $encodedLength = intdiv($encodedBits + 7, 8);
        $dataBlockLength = $encodedLength - $hashLength - 1;
        if ($dataBlockLength <= self::SALT_LENGTH) {
            throw new RuntimeException('RSA key is too short for RSA-PSS SHA-256.');
        }

        $messageHash = hash(self::HASH_ALGORITHM, $payload, true);
        $salt = random_bytes(self::SALT_LENGTH);
        $hash = hash(self::HASH_ALGORITHM, str_repeat("\0", 8).$messageHash.$salt, true);
        $paddingLength = $dataBlockLength - self::SALT_LENGTH - 1;
        $dataBlock = str_repeat("\0", $paddingLength)."\x01".$salt;
        $maskedDataBlock = $dataBlock ^ $this->maskGenerationFunction($hash, strlen($dataBlock));

        $unusedBits = 8 * $encodedLength - $encodedBits;
        if ($unusedBits > 0) {
            $maskedDataBlock[0] = chr(ord($maskedDataBlock[0]) & (0xff >> $unusedBits));
        }

        return $maskedDataBlock.$hash."\xbc";
    }

    private function verifyPss(string $payload, string $encodedMessage, int $modulusBits): bool
    {
        $hashLength = 32;
        $encodedBits = $modulusBits - 1;
        $encodedLength = intdiv($encodedBits + 7, 8);
        $dataBlockLength = $encodedLength - $hashLength - 1;
        if ($dataBlockLength < 1 || strlen($encodedMessage) !== $encodedLength || substr($encodedMessage, -1) !== "\xbc") {
            return false;
        }

        $maskedDataBlock = substr($encodedMessage, 0, $dataBlockLength);
        $hash = substr($encodedMessage, $dataBlockLength, $hashLength);
        $unusedBits = 8 * $encodedLength - $encodedBits;
        if ($unusedBits > 0 && (ord($maskedDataBlock[0]) & (0xff << (8 - $unusedBits))) !== 0) {
            return false;
        }

        $dataBlock = $maskedDataBlock ^ $this->maskGenerationFunction($hash, $dataBlockLength);
        if ($unusedBits > 0) {
            $dataBlock[0] = chr(ord($dataBlock[0]) & (0xff >> $unusedBits));
        }

        $paddingLength = $dataBlockLength - self::SALT_LENGTH - 1;
        if ($paddingLength < 0) {
            return false;
        }
        if (! hash_equals(str_repeat("\0", $paddingLength), substr($dataBlock, 0, $paddingLength)) || $dataBlock[$paddingLength] !== "\x01") {
            return false;
        }

        $salt = substr($dataBlock, -self::SALT_LENGTH);
        $messageHash = hash(self::HASH_ALGORITHM, $payload, true);
        $expectedHash = hash(self::HASH_ALGORITHM, str_repeat("\0", 8).$messageHash.$salt, true);

        return hash_equals($expectedHash, $hash);
    }

    private function maskGenerationFunction(string $seed, int $length): string
    {
        $mask = '';
        for ($counter = 0; strlen($mask) < $length; $counter++) {
            $mask .= hash(self::HASH_ALGORITHM, $seed.pack('N', $counter), true);
        }

        return substr($mask, 0, $length);
    }

    private function opensslFailure(string $operation): RuntimeException
    {
        $errors = [];
        while (($error = openssl_error_string()) !== false) {
            $errors[] = $error;
        }

        return new RuntimeException($operation.($errors === [] ? '.' : ': '.implode('; ', $errors)));
    }
}
