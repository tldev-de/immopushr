<?php

namespace Provider;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Models\OfferDTO;
use ReflectionClass;
use Utils\Utils;

/**
 * Interface Provider
 */
abstract class Provider
{
    protected RemoteWebDriver $browser;

    public function __construct(RemoteWebDriver $browser)
    {
        $this->browser = $browser;
    }

    /**
     * returns all domains of the provider.
     * @return string[]
     */
    abstract public static function getDomains(): array;

    /**
     * this function returns the parsed offers as array of type Offer.
     * @param  string     $url
     * @return OfferDTO[]
     */
    abstract public function parseOffers(string $url): array;

    /**
     * returns the start page url of the provider.
     * @return string
     */
    abstract public function getDefaultUrl(): string;

    /**
     * saves all websites cookies to a PROVIDERNAME.cookie file
     */
    protected function saveCookies(): void
    {
        $cookies = $this->browser->manage()->getCookies();
        file_put_contents($this->getCookieFile(), serialize($cookies));
    }

    /**
     * restores all websites cookies from a PROVIDERNAME.cookie file
     */
    protected function restoreCookies(): void
    {
        $file = $this->getCookieFile();
        if (is_file($file)) {
            $this->browser->get($this->getDefaultUrl());
            $cookies = unserialize(file_get_contents($file));
            foreach ($cookies as $cookie) {
                $this->browser->manage()->addCookie($cookie);
            }
            Utils::sleepRandomSeconds(2, 5);
        }
    }

    protected function getProviderName()
    {
        return (new ReflectionClass($this))->getShortName();
    }

    private function getCookieFile(): string
    {
        return get_called_class() . '.cookie';
    }
}
