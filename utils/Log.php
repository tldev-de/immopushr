<?php

namespace Utils;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log
{
    protected static $instance;

    public static function debug($message, array $context = [])
    {
        self::getLogger()->debug($message, $context);
    }

    /**
     * @return \Monolog\Logger
     */
    public static function getLogger()
    {
        if (!self::$instance) {
            self::createInstance();
        }

        return self::$instance;
    }

    public static function info($message, array $context = [])
    {
        self::getLogger()->info($message, $context);
    }

    public static function notice($message, array $context = [])
    {
        self::getLogger()->notice($message, $context);
    }

    public static function warning($message, array $context = [])
    {
        self::getLogger()->warning($message, $context);
    }

    public static function error($message, array $context = [])
    {
        self::getLogger()->error($message, $context);
    }

    public static function critical($message, array $context = [])
    {
        self::getLogger()->critical($message, $context);
    }

    public static function alert($message, array $context = [])
    {
        self::getLogger()->alert($message, $context);
    }

    public static function emergency($message, array $context = [])
    {
        self::getLogger()->emergency($message, $context);
    }

    protected static function createInstance()
    {
        $logDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';

        $logger = new Logger('ImmoPushr');
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        $handler = new RotatingFileHandler($logDirectory . DIRECTORY_SEPARATOR . 'ImmoPushr.log', 7);
        $handler->setFilenameFormat('{date}_{filename}', 'Y-m-d');
        $logger->pushHandler($handler);

        self::$instance = $logger;
    }
}
