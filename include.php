<?php
require __DIR__ . '/vendor/autoload.php';

use Ion\Singleton;
use Ion\Main;
use Ion\Settings;
use Ion\RendererInterface;
use Ion\ReactRenderer;
use Ion\TwigRenderer;
use Ion\UFUserSelect;
use Ion\UFVisualEditor;
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

define('ION_SETTINGS_ID', 1);

Loader::registerAutoLoadClasses('ion', array(
    '\\' . Singleton::class => './classes/Singleton.php',
    '\\' . Main::class => './classes/Main.php',
    '\\' . Settings::class => './classes/Settings.php',
    '\\' . RendererInterface::class => './classes/RendererInterface.php',
    '\\' . TwigRenderer::class => './classes/TwigRenderer.php',
    '\\' . ReactRenderer::class => './classes/ReactRenderer.php',
    '\\' . UFVisualEditor::class => './classes/UFVisualEditor.php',
    '\\' . UFUserSelect::class => './classes/UFUserSelect.php',
));

$eManager = EventManager::getInstance();
$eManager->addEventHandlerCompatible('main', 'OnProlog', array(Main::class, 'onProlog'));
$eManager->addEventHandlerCompatible('main', 'OnEpilog', array(Main::class, 'onEpilog'));
$eManager->addEventHandlerCompatible('main', 'OnAfterEpilog', array(Main::class, 'onAfterEpilog'));
$eManager->addEventHandlerCompatible('main', 'OnUserTypeBuildList', array(UFVisualEditor::class, 'GetUserTypeDescription'));
$eManager->addEventHandlerCompatible('main', 'OnUserTypeBuildList', array(UFUserSelect::class, 'GetUserTypeDescription'));

$SERVER_NAME_ARR = explode(".", strtoupper($_SERVER["SERVER_NAME"]));
$SERVER_NAME_ARR_REV = array_reverse($SERVER_NAME_ARR);
$GLOBALS["DOMAIN_FIRST"] = $SERVER_NAME_ARR_REV[0];
$GLOBALS["DOMAIN_SECOND"] = $SERVER_NAME_ARR_REV[1];
$GLOBALS["DOMAIN_THIRD"] = $SERVER_NAME_ARR_REV[2];
$GLOBALS["DOMAIN_FOURTH"] = $SERVER_NAME_ARR_REV[3];
$GLOBALS["DOMAIN_FIFTH"] = $SERVER_NAME_ARR_REV[4];
