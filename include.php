<?php
require __DIR__ . '/vendor/autoload.php';

use Ion\Ion;
use Ion\Settings;
use Ion\ArrayHelper;
use Ion\ComponentInterface;
use Ion\TwigComponent;
use Ion\TwigHelper;
use Bitrix\Main\Loader;

define('ION_SETTINGS_ID', 1);

Loader::registerAutoLoadClasses('ion', array(
	'\\' . Ion::class => './classes/Ion.php',
	'\\' . Settings::class => './classes/Settings.php',
	'\\' . ArrayHelper::class => './classes/ArrayHelper.php',
	'\\' . TwigHelper::class => './classes/TwigHelper.php',
	'\\' . ComponentInterface::class => './classes/ComponentInterface.php',
	'\\' . TwigComponent::class => './classes/TwigComponent.php',
));
