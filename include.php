<?php
use Bitrix\Main\Loader;

$arClasses = array(
	'Ion' => 'classes/Ion.php'
);

Loader::registerAutoLoadClasses("ion", $arClasses);