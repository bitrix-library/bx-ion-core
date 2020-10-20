<?php
require __DIR__ . '/vendor/autoload.php';

use Ion\Ion;
use Ion\ArrayHelper;
use Ion\ComponentInterface;
use Ion\TwigComponent;
use Ion\TwigHelper;
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('ion', array(
    '\\' . Ion::class => './classes/Ion.php',
    '\\' . ArrayHelper::class => './classes/ArrayHelper.php',
    '\\' . TwigHelper::class => './classes/TwigHelper.php',
    '\\' . ComponentInterface::class => './classes/ComponentInterface.php',
    '\\' . TwigComponent::class => './classes/TwigComponent.php',
));
