<?php

namespace Facebook\WebDriver;

include('vendor/autoload.php');

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Chrome\ChromeDriver;

$args = [];
if (!empty($_GET)) {
    $args = $_GET;
} else {
    foreach ($argv as $arg) {
        $e=explode("=",$arg);
        if(count($e)==2) {
            $args[$e[0]] = $e[1];
        }
    }
}

date_default_timezone_set(!empty($args['timezone']) ? $args['timezone'] : 'Europe/Rome');
$tomorrow = strtotime('+1 day');
$day = date('d/m/Y', $tomorrow);

$username = $args['username'];
$password = $args['password'];
$books = !empty($args['books']) ? $args['books'] : 2;
$waitUntill = mktime(!empty($args['untill']) ? $args['untill'] : 14, 0, 0);

$host = 'https://www.bibi1app.it/';
if (empty($args['spaces'])) {
    $spaces = [
        6, 7, 8,
        //2, 3, 4,
        11, 12,
        13, 14, 15, 16,
        17, 18, 19, 20,
        21, 22, 23, 24, 41, 42,
        25, 26, 27, 28, 45, 46,
        29, 30, 31, 32, 49, 50,
        33, 34, 35, 36,
        37, 38, 39, 40,
        42, 44, 47, 48,
        51, 52, 53, 54,
        55, 56, 57, 58,
        59, 60, 61, 62,
        63, 64, 65, 66,
        67, 68, 69, 70,
        71, 72, 73, 74,
        75, 76, 77, 78,
        79, 80
    ];
} else {
    $spaces = explode(',', $args['spaces']);
}

$capabilities = DesiredCapabilities::chrome();
$capabilities->setCapability('goog:chromeOptions', ['args' => ['--headless', '--disable-dev-shm-usage', '--no-sandbox']]);
putenv('WEBDRIVER_CHROME_DRIVER='.getenv('CHROMEDRIVER_PATH'));
$driver = ChromeDriver::start($capabilities);
//$driver = RemoteWebDriver::create('http://localhost:4444/', $capabilities);

$driver->get($host.'Account/Login');
//$driver->get('https://www.bibi1app.it/Account/Login?ReturnUrl=%2FPrenotazione%2FListaPrenotazioniUtente');

// Wait for at most 10s and retry every 1000ms
$driver->wait(10, 1000)->until(
    WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('h2'), 'Accedi.')
);

$driver
    ->findElement(WebDriverBy::id('Email'))
    ->sendKeys($username);

$driver
    ->findElement(WebDriverBy::id('Password'))
    ->sendKeys($password);

$driver
    ->findElement(WebDriverBy::xpath("//input[@value='Accedi']"))
    ->click();

$driver->get('https://www.bibi1app.it/Prenotazione/GetZona');
//$driver->wait(10, 1000)->until(
//    WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('body'), 'Lido del Sole')
//);
//
//$driver
//    ->findElement(WebDriverBy::linkText('Lido del Sole'))
//    ->click();
$driver->get($host.'Prenotazione/SetZona?IDZona=2');

time_sleep_until($waitUntill);

$driver->get($host.'Prenotazione/SetDomani');

restart:

$driver->get('https://www.bibi1app.it/Prenotazione/SetSettore?IDSettore=7');

try {
    $driver->wait(10, 1000)->until(
        WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('h2'), 'Picchetto')
    );
} catch(NoSuchElementException $e) {}

foreach($spaces as $idx => $space) {
    try {

        $driver
            ->findElement(WebDriverBy::xpath("//a[@class='btn btn-success'][text() = '" . $space . "']"))
            ->click();

    } catch(NoSuchElementException $e) {

        unset($spaces[$idx]);

        continue;

    }

    try {
        $driver->wait()->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('label'), 'Nucleo famigliare')
        );

        $paxSelect = new WebDriverSelect( $driver->findElement(WebDriverBy::id('Pax')) );
        $paxSelect->selectByVisibleText('4 persone');

        $driver
            ->findElement(WebDriverBy::xpath("//input[@value='Invia']"))
            ->click();

        $driver->wait()->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('h2'), 'Conferma')
        );

        $driver
            ->findElement(WebDriverBy::id("PrivacyAccettata"))
            ->click();

        $driver
            ->findElement(WebDriverBy::xpath("//input[@value='Invia']"))
            ->click();

        $driver->wait()->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('h2'), 'Grazie !')
        );

        $books--;

        if (empty($books)) {
            $driver->quit();
        }

        goto restart;

    } catch(NoSuchElementException $e) {

        unset($spaces[$idx]);

        goto restart;

    }
}

mail('paolo.battistella@gmail.com','test','test');
