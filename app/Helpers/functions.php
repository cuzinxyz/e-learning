<?php

declare(strict_types=1);

if (!function_exists('generateAuthCode')) {
    function generateAuthCode($length)
    {
        // Define the character set (alphanumeric)
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $authCode = '';

        // Generate the random code
        for ($i = 0; $i < $length; $i++) {
            $randomIndex = random_int(0, $charactersLength - 1); // Secure random integer
            $authCode .= $characters[$randomIndex];
        }

        return $authCode;
    }
}

/**
 * @param string $string
 * @return string
 */
if (!function_exists('hash_bcrypt')) {
    function hash_bcrypt(string $string): string
    {
        return password_hash($string, PASSWORD_BCRYPT);
    }
}
