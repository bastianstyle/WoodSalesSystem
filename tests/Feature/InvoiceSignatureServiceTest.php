<?php

use App\Services\Cryptography\InvoiceSignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('signs and verifies RSA-PSS SHA-256 payloads', function () {
    $service = app(InvoiceSignatureService::class);
    $key = $service->generateKeyPair('test-v1');
    $payload = '{"amount":"100.00","invoice":"INV-001"}';

    $signature = $service->sign($payload, $key);

    expect($service->verify($payload, $signature, $key->public_key))->toBeTrue()
        ->and($service->verify('{"amount":"101.00","invoice":"INV-001"}', $signature, $key->public_key))->toBeFalse();
});

it('verifies OpenSSL-generated RSA-PSS SHA-256 signatures', function () {
    $publicKey = <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqIGitwBg5IDNVnYUlN2o
n5VT0ylBMpoBPNVYQob+GN99iPqN+UyX5zApC5QfwAFb2BV/rraleXdhPSi8oN4h
A4uxmy90/XHvSRMmyhp3mVNU8c8eYPtumwBlE/BXqifgeMFqzt898YiyZfA6YwGb
VU7bH0AADcKb8qoMR+PkHDQ5djHG4OfeuByfsYZfhNQWWfOHLzpmUULbqk1tSKcs
VOhnhqs4IQh5U06bwayhIX1f8c7UMzM6kkX6P2Bn5QRWdf3e2kkX9cig4nGCcnlc
jsibMZmIaoQUb5vYfEYIVG7O2GEHCuSc2KgRZueOQrlfNfiZ0mQISFPC/jpc7mHP
IwIDAQAB
-----END PUBLIC KEY-----
PEM;
    $signature = 'od6weZnb/4ToWFm4sgI3w6Y0JRlJUbl8pBx66pOnj2WIKgVjkEv9m5Bcp83KqzHmOxQ3yYyYKXE40ERUMM81jZ/FZUiyRcIOrts1LXqniGzzespHKE1ybFAxccLJSHGf+MP98NUP3N6YR0remnw8xV2Sy7aND6S40en/lKbQCqej2i/NbjW4qJmVsFDLXKIDqvw6eyCtNBVLVdyCVZaK5XpVfKzUctej9Hbu+jO9pSdxprl8MkQFZs4AHKXHWfRetUq2VXvBsXq+078ZYturvNm3MpO1mTMu868zfnSEUpe0VwxrcPpDwsVRZqHQ7uikUYFd9kRKV1PQq6+Wc4suHA==';

    expect(app(InvoiceSignatureService::class)->verify('{"amount":"100.00","invoice":"INV-001"}', $signature, $publicKey))->toBeTrue();
});
