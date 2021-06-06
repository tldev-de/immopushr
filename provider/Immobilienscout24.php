<?php

namespace Provider;

use Facebook\WebDriver\WebDriverBy;
use Models\OfferDTO;
use TwoCaptcha\Exception\ApiException;
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
        Utils::sleepRandomSeconds(5, 20);
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
            $link = 'https://www.immobilienscout24.de' . $element->findElement(WebDriverBy::cssSelector('a'))->getAttribute('href');
            $fields = $element->findElements(WebDriverBy::cssSelector('dl.result-list-entry__primary-criterion'));
            $price = 'n/a';
            $flatSize = 'n/a';
            $rooms = 'n/a';
            foreach ($fields as $field) {
                if (str_contains($field->getText(), 'Kaufpreis')) {
                    $price = trim($field->findElement(WebDriverBy::tagName('dd'))->getText());
                }
                if (str_contains($field->getText(), 'Wohnfl')) {
                    $flatSize = trim($field->findElement(WebDriverBy::tagName('dd'))->getText());
                }
                if (str_contains($field->getText(), 'Zi.')) {
                    $rooms = trim($field->findElement(WebDriverBy::tagName('dd'))->getText());
                }
            }
            $address = trim($element->findElement(WebDriverBy::cssSelector('.result-list-entry__map-link'))->getText());
            $dto = new OfferDTO($this->getProviderName(), $foreignId, $title, $link, $price, $flatSize, $rooms, $address);
            $offers[] = $dto;
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

        Log::info('click captcha button');
        $this->browser->executeScript('document.querySelector(".geetest_btn").click()');
        Utils::sleepRandomSeconds(2, 6);

        if (!$this->hasCaptcha()) {
            return true;
        }

        Log::info('solve captcha using 2captcha');
        $challenge = $this->browser->executeScript('return GeeChallenge;');
        $gt = $this->browser->executeScript('return GeeGT;');

        if (!$challenge || !$gt) {
            Log::error('unable to get geetest captcha');
            return false;
        }
        return false; // TODO: we can skip here since the challenge is already used at this step and cannot be reused
        try {
            $result = $solver->geetest([
                'gt' => $gt,
                'challenge' => $challenge,
                'url' => $this->browser->getCurrentURL(),
            ]);
            Log::info('solved captcha!');
        } catch (ApiException $e) {
            Log::error('Unable to solve captcha, skip immobilienscout24 for this time');
            return false;
        }

        $this->browser->executeScript('solvedCaptcha({geetest_challenge: "' . $result->challenge . '", geetest_validate: "' . $result->validate . '", geetest_seccode: "' . $result->seccode . '",});');

        Utils::sleepRandomSeconds(2, 5);
        return !$this->hasCaptcha();
    }
}
