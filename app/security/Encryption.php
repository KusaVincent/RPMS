<?php

namespace RPMS\APP\Security;

use RPMS\APP\Log\SystemLog;

class Encryption
{
    private $extraKey;
    private $systemLog;
    private $getVarSalt;

    public function __construct(?string $varSalt = null)
    {
        $this->extraKey     = $_ENV['STATIC_SALT'];
        $this->systemLog    = new SystemLog('encryption');
        $this->getVarSalt   = $varSalt === null ? $this->encryptIP() : $varSalt;
    }

    private function encryptIP(): string
    {
        return $this->encryptString($_SERVER['REMOTE_ADDR']);
    }

    public static function make(?string $varSalt = null): self
    {
        return new self($varSalt);
    }

    public function encrypt(string $data): string
    {
        try {
            return $this->encryptString($data);
        } catch (\Exception $e) {
            try {
                $this->systemLog->error($e->getMessage());
            } catch (\Exception $e) {
                SystemLog::log($e->getMessage());
            }

            throw new \Exception('Encryption failed: ' . $e->getMessage());
        }
    }

    public function decrypt(string $encryptedData): string
    {
        try {
            return $this->decryptString($encryptedData);
        } catch (\Exception $e) {
            try {
                $this->systemLog->error($e->getMessage());
            } catch (\Exception $e) {
                SystemLog::log($e->getMessage());
            }

            throw new \Exception('Decryption failed: ' . $e->getMessage());
        }
    }

    private function getKeyAndIV(): array
    {
        $key    = hash('sha256', $this->extraKey . '-' . $this->getVarSalt);
        $iv     = substr(hash('sha256', $this->getVarSalt . '+-_-+' . $this->extraKey), 0, intval($_ENV['IV_LENGTH']));
        return [$key, $iv];
    }

    private function encryptString(string $rawData): string
    {
        [$key, $iv]     = $this->getKeyAndIV();
        $opensslOutput  = openssl_encrypt($rawData, $_ENV['ENCRYPT_METHOD'], $key, 0, $iv);

        if ($opensslOutput === false) {
            try {
                $this->systemLog->info('Encryption failed');
            } catch (\Exception $e) {
                SystemLog::log($e->getMessage());
            }

            throw new \Exception('Encryption failed');
        }

        $encodedOutput      = $this->base64UrlEncode($opensslOutput);
        $encryptedOutput    = str_replace('=', '[equal]', $encodedOutput);

        return $encryptedOutput;
    }

    private function decryptString(string $encryptedData): string
    {
        [$key, $iv]         = $this->getKeyAndIV();
        $setEncryptData     = str_replace('[equal]', '=', $encryptedData);
        $decryptedOutput    = openssl_decrypt($this->base64UrlDecode($setEncryptData), $_ENV['ENCRYPT_METHOD'], $key, 0, $iv);
        
        if ($decryptedOutput === false) {
            try {
                $this->systemLog->info('Decryption failed');
            } catch (\Exception $e) {
                SystemLog::log($e->getMessage());
            }
            
            throw new \Exception('Decryption failed');
        }

        return $decryptedOutput;
    }

    private function base64UrlEncode(string $data): string
    {
        $base64Url = strtr(base64_encode($data), '+/', '-_');
        return rtrim($base64Url, '=');
    }

    private function base64UrlDecode(string $base64Url): string
    {
        return base64_decode(strtr($base64Url, '-_', '+/'));
    }
}