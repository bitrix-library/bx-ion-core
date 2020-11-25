<?php

/**
 * @global $APPLICATION
 * @global $USER
 */

use Bitrix\Main\Loader;

Loader::includeModule("ion");

return array(
	"parent_menu" => "global_menu_content",
	"section" => "ion",
	"sort" => 10,
	"module_id" => "ion",
	"text" => "Конфигурация ION",
	"title" => "Конфигурация ION",
	"url" => "ion_settings_edit.php",
	"icon" => "ion_menu_icon",
	"items_id" => "menu_ion_settings",
);
