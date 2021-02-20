<?php

namespace Provider;

use Facebook\WebDriver\WebDriverBy;
use Models\OfferDTO;
use TwoCaptcha\TwoCaptcha;
use Utils\Log;
use Utils\Utils;

class Immobilienscout24 extends Provider
{
    public static function getDomains(): array
    {
        return ['www.immobilienscout24.de', 'immobilienscout24.de'];
    }

    public function getDefaultUrl(): string
    {
        return 'https://www.immobilienscout24.de/';
    }

    public function parseOffers(string $url): array
    {
        $this->restoreCookies();
        $this->browser->get($url);
        Utils::sleepRandomSeconds(2, 20);
        if ($this->hasCaptcha()) {
            Log::info('identified captcha page!');
            $status = $this->solveCaptcha();
            if (!$status) {
                Log::info('2captcha was not able to solve the captcha -> skip url');
                return [];
            }
        }
        $this->saveCookies();
        $elements = $this->browser->findElements(WebDriverBy::cssSelector('.result-list__listing'));
        Log::info('found ' . count($elements) . ' offers!', ['title' => $this->browser->getTitle()]);
        $offers = [];
        foreach ($elements as $element) {
            $foreignId = $element->getAttribute('data-id');
            $title = trim(str_replace('NEU', '', $element->findElement(WebDriverBy::cssSelector('h5'))->getText()));
            $link = $element->findElement(WebDriverBy::cssSelector('a'))->getAttribute('href');
            $fields = $element->findElements(WebDriverBy::cssSelector('.result-list-entry__primary-criterion > dd'));
            $price = trim($fields[0]->getText());
            $flatSize = trim($fields[1]->getText());
            $rooms = trim($fields[2]->getText());
            $address = trim($element->findElement(WebDriverBy::cssSelector('.result-list-entry__map-link'))->getText());
            $offers[] = new OfferDTO($this->getProviderName(), $foreignId, $title, $link, $price, $flatSize, $rooms, $address);
        }
        return $offers;
    }

    private function hasCaptcha()
    {
        return str_contains($this->browser->getTitle(), 'Ich bin kein Roboter');
    }

    private function solveCaptcha()
    {
        $token = env('CAPTCHA_TOKEN');
        if (!$token) {
            Log::info('could not find 2captcha token');
            return false;
        }
        $solver = new TwoCaptcha(env('CAPTCHA_TOKEN'));

        Log::info('solve captcha using 2captcha');
        $result = $solver->recaptcha([
            'sitekey' => $this->browser->findElement(WebDriverBy::cssSelector('.g-recaptcha'))->getAttribute('data-sitekey'),
            'url' => $this->browser->getCurrentURL(),
        ]);
        Log::info('solved captcha!');

        $this->browser->executeScript('document.getElementById("g-recaptcha-response").innerHTML="' . $result->code . '";');
        $this->browser->executeScript('solvedCaptcha("' . $result->code . '")');

        Utils::sleepRandomSeconds(2, 5);
        return !$this->hasCaptcha();
    }
}
