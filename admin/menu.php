<?php
/**
 * @global $APPLICATION
 * @global $USER
 */

if(!$USER->IsAdmin()) {
	return;
}

return array(
	array(
		"parent_menu" => "global_menu_content",
		"section" => "ion",
		"sort" => 10,
		"module_id" => "ion",
		"text" => "ion",
		"title" => "ion",
		"icon" => "ion_menu_icon",
		"items_id" => "menu_ion_settings",
		"items" => array(
			array(
				"sort" => 10,
				"text" => "Пространства",
				"title" => "Пространства",
				"url" => "ion_settings_space_list.php",
				"more_url" => array(
					"ion_settings_space_view.php",
					"ion_settings_space_edit.php",
				)
			),
			array(
				"sort" => 20,
				"text" => "Конфигурация",
				"title" => "Конфигурация",
				"url" => "ion_settings_conf.php",
			),
			array(
				"sort" => 30,
				"text" => "Документация",
				"title" => "Документация",
				"url" => "ion_settings_docs.php",
			)
		)
	),
);
