<?php

namespace Utils;

class Utils
{
    public static function sleepRandomSeconds(int $min, int $max)
    {
        $seconds = rand($min, $max);
        Log::info('sleep for ' . $seconds . ' seconds');
        sleep($seconds);
    }

    public static function sendTelegramMessages($msg)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatIds = explode('|', env('TELEGRAM_CHATS'));
        foreach ($chatIds as $chatId) {
            $data = [
                'text' => $msg,
                'chat_id' => $chatId
            ];
            file_get_contents('https://api.telegram.org/bot' . $token . '/sendMessage?' . http_build_query($data));
        }
    }
}
