<?php
/**
 * Encryption utilities using AES-256-GCM
 */

require_once __DIR__ . '/config.php';

class Crypto {
    const ALGORITHM = 'aes-256-gcm';
    const KEY_BYTES = 32;
    
    /**
     * Encrypt text using AES-256-GCM
     */
    public static function encrypt(string $text): array {
        $key = hex2bin(ENCRYPTION_KEY);
        if ($key === false) {
            throw new Exception('Invalid encryption key');
        }
        
        $iv = random_bytes(16);
        $tag = null;
        
        $encrypted = openssl_encrypt(
            $text,
            self::ALGORITHM,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($encrypted === false) {
            throw new Exception('Encryption failed');
        }
        
        return [
            'encrypted' => bin2hex($encrypted),
            'iv' => bin2hex($iv),
            'authTag' => bin2hex($tag),
        ];
    }
    
    /**
     * Decrypt text using AES-256-GCM
     */
    public static function decrypt(string $encrypted, string $iv, string $authTag): string {
        $key = hex2bin(ENCRYPTION_KEY);
        if ($key === false) {
            throw new Exception('Invalid encryption key');
        }
        
        $decrypted = openssl_decrypt(
            hex2bin($encrypted),
            self::ALGORITHM,
            $key,
            OPENSSL_RAW_DATA,
            hex2bin($iv),
            hex2bin($authTag)
        );
        
        if ($decrypted === false) {
            throw new Exception('Decryption failed');
        }
        
        return $decrypted;
    }
    
    /**
     * Mask API key for display
     */
    public static function maskKey(string $key): string {
        if (strlen($key) < 8) {
            return str_repeat('•', strlen($key));
        }
        return substr($key, 0, 4) . str_repeat('•', 8) . substr($key, -4);
    }
}
