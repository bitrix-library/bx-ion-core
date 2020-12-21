<?php

/**
 * @global $APPLICATION
 * @global $USER
 */

if(!$USER->IsAdmin()) {
	return;
}

return array(
	"parent_menu" => "global_menu_content",
	"section" => "ion",
	"sort" => 10,
	"module_id" => "ion",
	"text" => "Конфигурация",
	"title" => "Конфигурация",
	"url" => "ion_settings_edit.php?lang=" . LANGUAGE_ID,
	"icon" => "ion_menu_icon",
	"items_id" => "menu_ion_settings",
);
