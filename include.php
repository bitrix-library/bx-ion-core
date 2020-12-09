<?php
require __DIR__ . '/vendor/autoload.php';

use Ion\Main;
use Ion\Settings;
use Ion\ReactHelper;
use Ion\TwigHelper;
use Ion\UFVisualEditor;
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

define('ION_SETTINGS_ID', 1);

Loader::registerAutoLoadClasses('ion', array(
	'\\' . Main::class => './classes/Main.php',
	'\\' . Settings::class => './classes/Settings.php',
	'\\' . TwigHelper::class => './classes/TwigHelper.php',
	'\\' . ReactHelper::class => './classes/ReactHelper.php',
	'\\' . UFVisualEditor::class => './classes/UFVisualEditor.php',
));

$eventManager = EventManager::getInstance();
$eventManager->addEventHandlerCompatible(
	'main',
	'OnUserTypeBuildList',
	array(UFVisualEditor::class, 'GetUserTypeDescription')
);

$SERVER_NAME_ARR = explode(".", strtoupper($_SERVER["SERVER_NAME"]));
$SERVER_NAME_ARR_REV = array_reverse($SERVER_NAME_ARR);
$GLOBALS["DOMAIN_FIRST"] = $SERVER_NAME_ARR_REV[0];
$GLOBALS["DOMAIN_SECOND"] = $SERVER_NAME_ARR_REV[1];
$GLOBALS["DOMAIN_THIRD"] = $SERVER_NAME_ARR_REV[2];
$GLOBALS["DOMAIN_FOURTH"] = $SERVER_NAME_ARR_REV[3];
$GLOBALS["DOMAIN_FIFTH"] = $SERVER_NAME_ARR_REV[4];
