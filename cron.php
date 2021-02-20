<?php

use Models\Offer;
use Utils\BrowserFactory;
use Utils\Log;
use Utils\ProviderHelper;
use Utils\Utils;

require 'boot.php';

//get urls
$urls = explode('|', env('URLS'));
if (!count($urls)) {
    Log::critical('stopped execution since no urls have been found!');
    exit;
}

// register providers
ProviderHelper::initialize();
if (!ProviderHelper::count()) {
    Log::critical('stopped execution since no providers have been found!');
    exit;
}

// get browser
$browser = BrowserFactory::getBrowser();

foreach ($urls as $url) {
    Log::info('new url', ['url' => $url]);
    $provider = ProviderHelper::getInstanceFor($url, $browser);
    if (!$provider) {
        Log::critical('skipped url since no matching provider has been found!');
        continue;
    }
    $offers = $provider->parseOffers($url);
    foreach ($offers as $offer) {
        // skip existing offers
        if (Offer::where('provider', $offer->provider)->where('foreign_id', $offer->foreignId)->exists()) {
            Log::info('skipped offer: ' . $offer->title);
            continue;
        }

        // log new offer
        Log::info('new offer: ' . $offer->title, [
            'foreign_id' => $offer->foreignId,
            'title' => $offer->title,
            'price' => $offer->price,
            'flat_size' => $offer->flatSize,
            'rooms' => $offer->rooms,
            'address' => $offer->address,
        ]);

        // save new offer to database
        $dbOffer = Offer::fromOfferDTO($offer);
        $dbOffer->save();

        // send telegram message
        Utils::sendTelegramMessages($offer->toString());
    }
    Utils::sleepRandomSeconds(2, 20);
}
$browser->quit();
