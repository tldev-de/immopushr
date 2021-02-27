<p align="center">
  <img src="https://i.imgur.com/7fZBlWR.png">
</p>

<p align="center">
  <img src="https://badges.fw-web.space/github/license/tldev-de/immopushr">
</p>

<p align="center">
  <img src="https://badges.fw-web.space/github/languages/code-size/tldev-de/immopushr">
</p>

<h3 align="center">Telegram Bot that finds flat offers on immobilienscout,de, immowelt.de and ebay-kleinanzeigen.de.</h3>

<div align="center">
  <h3>
    <a href="#-features">Features</a>
    <span> | </span>
    <a href="#-how-to-use">How to use</a>
    <span> | </span>
    <a href="https://github.com/tldev-de/immopushr/issues">Issues</a>
    <span> | </span>
    <a href="https://github.com/tldev-de">Contact</a>
  </h3>
</div>

## Table of Contents
* [Features](#-features)
* [How to use](#-how-to-use)
* [Configuration Options](#-configuration-options)
* [Props](#-props)
* [Contributing](#-contributing)
* [License](#-license)

## Features
ImmoPushr is a [web scraper](https://en.wikipedia.org/wiki/Web_scraping) for immobilienscout24.de, immowelt.de and ebay-kleinanzeigen.de.
In germany, these three sites are often used to offer property or rental flats and houses.
ImmoPushr helps to find a flat or house on these platforms by periodically scraping the configured search websites.
Once a new offer is found, ImmoPushr notifies you via [Telegram](https://en.wikipedia.org/wiki/Telegram_%28software%29).

> ImmoPushr saves you time and gives you a small time advantage :)

#### ImmoPushr is...
* 100 % free
* open-source
* very simple to configure
* self-hosted
* able to solve captchas (by 2captcha)
* really fast

### How is it working?
ImmoPushr is implemented as small PHP script, which is triggered by `crond` inside a docker container.
It uses [Selenium](https://www.selenium.dev/) to visit the configured websites and extracts the relevant information (title, price, size, amount of rooms, location and link).
If it detects a captcha, it uses 2captcha to solve the captcha automatically.
You can specify, how often ImmoPushr should check for new offers on the configured sites. By default, it runs every hour between 7 and 22 o'clock at `x:50`.

## How to use?
The simplest way to use ImmoPushr is using the prebuilt docker images from [Dockerhub (`tldevde/immopushr`)](https://hub.docker.com/r/tldevde/immopushr) with `docker-compose`.

You can also run it without docker, which is currently not documented.

You can use the `docker-compose.yml` file from the repository.
Just copy the file and change the environment variables of the `immopushr` container to fit your needs.
The configuration options are described below.
Then you can start it using `docker-compose up -d`. Run `docker-compose logs` to see the logs if you experience any problems.

## Configuration Options
You can specify configuration options either via a `.env` file in the applications directory or via environment variables (preferred, especially if you use docker).
Here is an overview of all options:

| Environment Variable | Mandatory | Default Value          | Example Value                                  |
|----------------------|-----------|------------------------|------------------------------------------------|
| `CAPTCHA_TOKEN`      | no        | -                      | `098f6bcd4621d373cade4e832627b4f6`             |
| `TELEGRAM_BOT_TOKEN` | yes       | -                      | `12123121:AaBbCcDdEeFf00112233445566778899Ggh` |
| `TELEGRAM_CHATS`     | yes       | -                      | `123123\|1231234`                              |
| `CRON_PATTERN`       | no        | `50 7-22 * * *`        | `20,50 7-22 * * *`                             |
| `SELENIUM_URL`       | no        | `http://selenium:4444` | `http://selenium:4444`                         |
| `URLS`               | yes       | -                      | `https://www.immobilienscout24.de/Suche/radius/wohnung-mieten?centerofsearchaddress=Berlin;;;;;&numberofrooms=1.5-&price=-1000.0&pricetype=rentpermonth&geocoordinates=52.51051;13.43068;4.0&sorting=2` |

### CAPTCHA_TOKEN
Some sites use captchas (e.g. [Google reCAPTCHA](https://de.wikipedia.org/wiki/ReCAPTCHA)) to block evil web scrapers.
Since this bot is not evil, you can automate solving these captchas using [2captcha](https://2captcha.com).
Just register at their website and add funds. 2captcha costs about 3 $ per 1000 captchas!
You can find your API token on their [customer panel](https://2captcha.com/enterpage).

If you don't want to solve captchas automatically, ImmoPushr will skip the search site if it detects a captcha.
As of February 2020, Immobilienscout24.de won't be crawlable without a valid 2captcha token.

### TELEGRAM_BOT_TOKEN / TELEGRAM_CHATS
ImmoPushr needs a registered Telegram bot to send messages to you.
You can register a new bot with the [Botfather](https://telegram.me/BotFather).
Just create a new chat with [Botfather](https://telegram.me/BotFather) and send the command `/newbot` as message.
The Botfather will ask for a name of the new bot and send you a bot token (e.g. `12123121:AaBbCcDdEeFf00112233445566778899Ggh`) to access the HTTP API.
This token needs to be provided in the environment variable `TELEGRAM_BOT_TOKEN`.

To get your chat id, you need to send a message to the newly registered bot. After that you can use the following bash command to get the chat id:
```
$ curl https://api.telegram.org/bot[TELEGRAM_BOT_TOKEN]/getUpdates
```
The result will look like this:
```
{"ok":true,"result":[{"update_id":123123123,
"message":{"message_id":123,"from":{"id":XXXXXXXX,"is_bot":false,"first_name":"YOUR_NAME","language_code":"en"},"chat":{"id":XXXXXXXX,"first_name":"YOUR_NAME","type":"private"},"date":1231231231,"text":"XYZ"}}]}
```

The relevant chat id in this example is `XXXXXXXX`.
This chat id needs to be provided in the environment variable `TELEGRAM_CHATS`.
You can enter multiple chat ids separated by `|`.

### CRON_PATTERN
ImmoPushr runs periodically triggered by `crond`.
You can change the default behaviour by providing your own cron expression.
Please make sure to stay within limits. Do not run the crawler too often!
This produces high load on the platforms and will result in banned ip addresses.
If you are not experienced with cron expressions you should have a look at [Crontab Guru](https://crontab.guru/) for further information.

### SELENIUM_URL
[Selenium](https://www.selenium.dev/) is a framework for automated browser tests.
Nevertheless, it can also be used to automate tasks and crawl websites.
To do so, it remote controls modern browsers - in our case it uses a Chrome browser.

In this environment variable you need to provide a link to a selenium grid or standalone instance.
The simplest choice to run a standalone instance is to use the [docker container](https://hub.docker.com/r/selenium/standalone-chrome/), which is used in our docker-compose file as well.

### URLS
Simply add you search URLs seperated by `|`.
A search URL may look like this: `https://www.immobilienscout24.de/Suche/radius/wohnung-mieten?centerofsearchaddress=Berlin;;;;;&numberofrooms=1.5-&price=-1000.0&pricetype=rentpermonth&geocoordinates=52.51051;13.43068;4.0&sorting=2`.
Since only the first page will be crawled, you should order the results by creation date.

## Props
Props to [Flathunter](https://github.com/flathunters/flathunter), which I actually wanted to use.
Since their crawler was not working for me, I decided to build my own alternative using selenium.
Nevertheless, it's a great project, and if you are a python developer you should consider supporting it.

## Contributing
I'm really happy to accept contributions from the community, that’s the main reason why I open-sourced it!
There are many ways to contribute, even if you’re not a technical person.
You can report issues directly on Github.
Please document as much as possible the steps to reproduce your problem (even better with logs and cour configuration).

## License
GPL-v3 @ [Tobias Dillig](https://tobias-dillig.de)