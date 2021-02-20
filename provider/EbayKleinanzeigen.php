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
        foreach ($elements as $key => $element) {
            $foreignId = $element->findElement(WebDriverBy::cssSelector('.aditem'))->getAttribute('data-adid');
            $title = trim($element->findElement(WebDriverBy::cssSelector('h2'))->getAttribute('textContent'));
            $link = $element->findElement(WebDriverBy::cssSelector('h2 > a'))->getAttribute('href');
            try {
                $price = trim($element->findElement(WebDriverBy::cssSelector('.aditem-details > strong'))->getAttribute('textContent'));
            } catch (NoSuchElementException $exception) {
                $price = trim($element->findElement(WebDriverBy::cssSelector('.aditem-main--middle--price'))->getAttribute('textContent'));
            }
            $flatSize = trim($element->findElements(WebDriverBy::cssSelector('.simpletag.tag-small'))[0]->getAttribute('textContent'));
            $rooms = trim(str_replace('Zimmer', '', $element->findElements(WebDriverBy::cssSelector('.simpletag.tag-small'))[1]->getAttribute('textContent')));
            try {
                $address = str_replace($price, '', $element->findElement(WebDriverBy::cssSelector('.aditem-details'))->getAttribute('textContent'));
            } catch (NoSuchElementException $exception) {
                $address = str_replace($price, '', $element->findElement(WebDriverBy::cssSelector('.aditem-main--top--left'))->getAttribute('textContent'));
            }
            $offers[] = new OfferDTO($this->getProviderName(), $foreignId, $title, $link, $price, $flatSize, $rooms, $address);
        }
        return $offers;
    }
}
