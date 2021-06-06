<?php

namespace Provider;

use Facebook\WebDriver\WebDriverBy;
use Models\OfferDTO;
use Utils\Log;
use Utils\Utils;

class Immowelt extends Provider
{
    public static function getDomains(): array
    {
        return ['www.immowelt.de', 'immowelt.de'];
    }

    public function getDefaultUrl(): string
    {
        return 'https://www.immowelt.de/';
    }

    public function parseOffers(string $url): array
    {
        $this->restoreCookies();
        $this->browser->get($url);
        $this->browser->executeScript('window.scrollTo(0,document.body.scrollHeight/3);');
        Utils::sleepRandomSeconds(1, 2);
        $this->browser->executeScript('window.scrollTo(0,document.body.scrollHeight/2);');
        Utils::sleepRandomSeconds(1, 2);
        $this->browser->executeScript('window.scrollTo(0,document.body.scrollHeight);');
        Utils::sleepRandomSeconds(1, 2);
        $this->saveCookies();
        $elements = $this->browser->findElements(WebDriverBy::cssSelector('.listitem'));
        Log::info('found ' . count($elements) . ' offers!', ['title' => $this->browser->getTitle()]);
        $offers = [];
        foreach ($elements as $element) {
            $foreignId = $element->findElement(WebDriverBy::cssSelector('.btn_remember'))->getAttribute('data-estateid');
            $title = $element->findElement(WebDriverBy::cssSelector('h2'));
            $this->browser->executeScript('window.scrollTo(0,' . $title->getLocation()->getY() . ');');
            $title = trim($title->getText());
            $link = 'https://www.immowelt.de' . $element->findElement(WebDriverBy::cssSelector('a'))->getAttribute('href');
            $price = trim($element->findElement(WebDriverBy::cssSelector('.price_sale > strong'))->getText());
            $flatSize = trim(str_replace('Wohnfläche (ca.)', '', $element->findElement(WebDriverBy::cssSelector('.square_meters'))->getText()));
            $rooms = trim(str_replace('Zimmer', '', $element->findElement(WebDriverBy::cssSelector('.rooms'))->getText()));
            $address = trim($element->findElement(WebDriverBy::cssSelector('.listlocation'))->getText());
            $dto = new OfferDTO($this->getProviderName(), $foreignId, $title, $link, $price, $flatSize, $rooms, $address);
            $offers[] = $dto;
        }
        return $offers;
    }
}
