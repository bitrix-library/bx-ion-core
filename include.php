<?php
require __DIR__ . '/vendor/autoload.php';

use Ion\Ion;
use Ion\ArrayHelper;
use Ion\TwigHelper;
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('ion', array(
    '\\' . Ion::class => './classes/Ion.php',
    '\\' . ArrayHelper::class => './classes/ArrayHelper.php',
    '\\' . TwigHelper::class => './classes/TwigHelper.php'
));
