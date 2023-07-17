<?php

namespace RPMS\App\Security;

use RPMS\App\Log\LogHandler;

class Encryption
{
    private string $logName;
    private string $ivLength;
    private string $extraKey;
    private string $tagLength;
    private string $getVarSalt;
    private string $encryptMethod;

    public function __construct(?string $varSalt = null)
    {
        $this->logName       = 'encryption';
        $this->ivLength      = ImmutableVariable::getValue('IVLength');
        $this->tagLength     = ImmutableVariable::getValue('tagLength');
        $this->extraKey      = ImmutableVariable::getValue('staticSalt');
        $this->encryptMethod = ImmutableVariable::getValue('encryptMethod');
        $this->getVarSalt    = $varSalt === null ? $this->encryptIP() : $varSalt;
    }

    private function encryptIP(): string
    {
        return $this->encryptString($_SERVER['REMOTE_ADDR']);
    }

    public static function salt(?string $varSalt = null): self
    {
        return new self($varSalt);
    }

    public function encrypt(string $data): string
    {
        try {
            return $this->encryptString($data);
        } catch (\Exception $e) {
            LogHandler::handle($this->logName, 'Encryption failed: ' . $e->getMessage());
            throw new \Exception('Encryption failed: ' . $e->getMessage());
        }
    }

    public function decrypt(string $encryptedData): string
    {
        try {
            return $this->decryptString($encryptedData);
        } catch (\Exception $e) {
            LogHandler::handle($this->logName, 'Decryption failed: ' . $e->getMessage());
            throw new \Exception('Decryption failed: ' . $e->getMessage());
        }
    }

    private function getKeyAndIV(): array
    {
        $key = hash('sha256', $this->extraKey . '-' . $this->getVarSalt);
        $iv = random_bytes(12);
        return [$key, $iv];
    }

    private function encryptString(string $rawData): string
    {
        [$key, $iv] = $this->getKeyAndIV();

        $ciphertext = openssl_encrypt($rawData, $this->encryptMethod, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false) {
            LogHandler::handle($this->logName, 'Encryption failed');
            throw new \Exception('Encryption failed');
        }

        $encodedData = $this->base64UrlEncode($iv . $ciphertext . $tag);

        return $encodedData;
    }

    private function decryptString(string $encryptedData): string
    {
        [$key, $iv] = $this->getKeyAndIV();

        $decodedData = $this->base64UrlDecode($encryptedData);

        if ($decodedData === false) {
            LogHandler::handle($this->logName, 'Invalid base64-encoded data');
            throw new \Exception('Invalid base64-encoded data');
        }

        $iv = substr($decodedData, 0, $this->ivLength);
        $ciphertext = substr($decodedData, $this->ivLength, -$this->tagLength);
        $tag = substr($decodedData, -$this->tagLength);

        $plaintext = openssl_decrypt($ciphertext, $this->encryptMethod, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($plaintext === false) {
            LogHandler::handle($this->logName, 'Decryption failed');
            throw new \Exception('Decryption failed');
        }

        return $plaintext;
    }

    private function base64UrlEncode(string $data): string
    {
        $base64Url = strtr(base64_encode($data), '+/', '-_');
        return rtrim($base64Url, '=');
    }

    private function base64UrlDecode(string $base64Url): string
    {
        $paddedBase64 = str_pad(strtr($base64Url, '-_', '+/'), strlen($base64Url) % 4, '=', STR_PAD_RIGHT);
        return base64_decode($paddedBase64);
    }
}