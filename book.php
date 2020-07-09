<?php

namespace Facebook\WebDriver;

include('vendor/autoload.php');

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Chrome\ChromeDriver;

$tomorrow = strtotime('+1 day');
$day = date('d/m/Y', $tomorrow);

$username = getenv('WEBAPP_USERNAME');
$password = getenv('WEBAPP_PASSWORD');
//$username = '';
//$password = '';

$spaces = [
    //1, 2, 3, 5, 6, 7,
    //4, 8,
    9, 10, 11, 12,
    13, 14, 15, 16,
    17, 18, 19, 20,
    21, 22, 23, 24,
    25, 26, 27, 28,
    29, 30, 31, 32,
    33, 34, 35, 36,
    37, 38, 39, 40
];

$capabilities = DesiredCapabilities::chrome();
$capabilities->setCapability('goog:chromeOptions', ['args' => ['--headless', '--disable-dev-shm-usage', '--no-sandbox']]);
putenv('WEBDRIVER_CHROME_DRIVER='.getenv('CHROMEDRIVER_PATH'));
$driver = ChromeDriver::start($capabilities);
//$driver = RemoteWebDriver::create('http://localhost:4444/', $capabilities);

$driver->get('https://www.bibi1app.it/Account/Login');
//$driver->get('https://www.bibi1app.it/Account/Login?ReturnUrl=%2FPrenotazione%2FListaPrenotazioniUtente');

// Wait for at most 10s and retry every 500ms
$driver->wait(10, 500)->until(
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

//$driver->wait(10, 500)->until(
//    WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('h2'), 'Lista Prenotazioni')
//);
//
//try {
//    $driver
//        ->findElement(WebDriverBy::xpath("//td[contains(text(), '" . $day . "')]"));
//    $driver->quit();
//} catch(NoSuchElementException $e) {}

$driver->get('https://www.bibi1app.it/Prenotazione/GetZona');
$driver->wait(10, 500)->until(
    WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('body'), 'Lido del Sole')
);

$driver
    ->findElement(WebDriverBy::linkText('Lido del Sole'))
    ->click();

$driver->wait(10, 500)->until(
    WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('body'), 'Domani')
);

$driver
    ->findElement(WebDriverBy::linkText('Domani'))
    ->click();

$driver->wait(10, 500)->until(
    WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('body'), 'L07')
);

try {
    $driver
        ->findElement(WebDriverBy::xpath("//h2[contains(text(), '" . $day . "')]"));
} catch(NoSuchElementException $e) {
    $driver->quit();
}


$driver->get('https://www.bibi1app.it/Prenotazione/SetSettore?IDSettore=7');
//$driver
//    ->findElement(WebDriverBy::xpath("//a[@href='/Prenotazione/SetSettore?IDSettore=7']"))
//    ->click();

$driver->wait(10, 500)->until(
    WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('body'), 'Picchetto')
);

foreach($spaces as $space) {
    try {

        $driver
            ->findElement(WebDriverBy::xpath("//a[@class='btn btn-success'][contains(text(), '" . $space . "')]"))
            ->click();

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

        $driver->quit();

    } catch(NoSuchElementException $e) {}
}