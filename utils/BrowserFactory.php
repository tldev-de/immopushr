<?php

namespace Utils;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class BrowserFactory
{
    /**
     * @return RemoteWebDriver
     */
    public static function getBrowser()
    {
        $options = (new ChromeOptions)->addArguments([
            '--window-size=1920,1080',
            '--lang=de-DE',
            '--no-sandbox',
            '--start-maximized',
            '--disable-infobars',
            '--disable-gpu',
            '--disable-blink-features=AutomationControlled',
            '--user-agent=Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36',
        ]);

        $desiredCapabilities = DesiredCapabilities::chrome();
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $options);

        return RemoteWebDriver::create(env('SELENIUM_URL'), $desiredCapabilities);
    }
}
