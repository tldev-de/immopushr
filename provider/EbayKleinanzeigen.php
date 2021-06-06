<?php

namespace Provider;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Models\OfferDTO;
use Utils\Log;
use Utils\Utils;

class EbayKleinanzeigen extends Provider
{
    public static function getDomains(): array
    {
        return ['www.ebay-kleinanzeigen.de', 'ebay-kleinanzeigen.de'];
    }

    public function getDefaultUrl(): string
    {
        return 'https://www.ebay-kleinanzeigen.de/';
    }

    public function parseOffers(string $url): array
    {
        $this->restoreCookies();
        $this->browser->get($url);
        Utils::sleepRandomSeconds(3, 5);
        $this->saveCookies();
        $elements = $this->browser->findElements(WebDriverBy::cssSelector('#srchrslt-adtable > .ad-listitem.lazyload-item'));
        Log::info('found ' . count($elements) . ' offers!', ['title' => $this->browser->getTitle()]);
        $offers = [];
        foreach ($elements as $element) {
            $foreignId = $element->findElement(WebDriverBy::cssSelector('.aditem'))->getAttribute('data-adid');
            $title = $element->findElement(WebDriverBy::cssSelector('h2'));
            $this->browser->executeScript('window.scrollTo(0,' . $title->getLocation()->getY() . ');');
            $title = trim($title->getText());
            $link = 'https://www.ebay-kleinanzeigen.de/' . $element->findElement(WebDriverBy::cssSelector('h2 > a'))->getAttribute('href');
            try {
                $price = trim($element->findElement(WebDriverBy::cssSelector('.aditem-details > strong'))->getText());
            } catch (NoSuchElementException $exception) {
                $price = trim($element->findElement(WebDriverBy::cssSelector('.aditem-main--middle--price'))->getText());
            }
            $flatSize = trim($element->findElements(WebDriverBy::cssSelector('.simpletag.tag-small'))[0]->getText());
            $rooms = trim(str_replace('Zimmer', '', $element->findElements(WebDriverBy::cssSelector('.simpletag.tag-small'))[1]->getText()));
            try {
                $address = str_replace($price, '', $element->findElement(WebDriverBy::cssSelector('.aditem-details'))->getText());
            } catch (NoSuchElementException $exception) {
                $address = str_replace($price, '', $element->findElement(WebDriverBy::cssSelector('.aditem-main--top--left'))->getText());
            }
            $dto = new OfferDTO($this->getProviderName(), $foreignId, $title, $link, $price, $flatSize, $rooms, $address);
            $offers[] = $dto;
        }
        return $offers;
    }
}
