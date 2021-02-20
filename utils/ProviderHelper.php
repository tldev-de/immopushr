<?php

namespace Utils;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Provider\Provider;

class ProviderHelper
{
    public static array $providers = [];

    public static function initialize()
    {
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, Provider::class)) {
                foreach ($class::getDomains() as $domain) {
                    self::$providers[$domain] = $class;
                }
            }
        }
    }

    public static function count()
    {
        return count(self::$providers);
    }

    /**
     * @param  string          $url
     * @param  RemoteWebDriver $browser
     * @return Provider|null
     */
    public static function getInstanceFor(string $url, RemoteWebDriver $browser)
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!isset(self::$providers[$host])) {
            return null;
        }
        return new self::$providers[$host]($browser);
    }
}
