<?php

use Bitrix\Main\Loader;

$arClasses = array(
	'\Ion\Ion' => 'classes/Ion.php',
	'\Ion\Util' => 'classes/Util.php'
);

Loader::registerAutoLoadClasses('ion', $arClasses);