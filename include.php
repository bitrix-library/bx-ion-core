<?php
require __DIR__ . '/vendor/autoload.php';

use Ion\Ion;
use Ion\ArrayHelper;
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('ion', array(
    '\\' . Ion::class => './classes/Ion.php',
    '\\' . ArrayHelper::class => './classes/ArrayHelper.php'
));
