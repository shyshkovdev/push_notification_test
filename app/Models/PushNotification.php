<?php


namespace App\Models;


use Exception;

class PushNotification
{
    /**
     * @param string $title
     * @param string $message
     * @param string $token
     * @return bool
     * @throws Exception
     */
    public static function send(string $title, string $message, string $token): bool
    {
        // Message sending emulation
        // usleep(30000); //30 ms
        return random_int(1, 10) > 1;
    }
}