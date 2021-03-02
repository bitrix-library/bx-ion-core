<?php
/**
 * @global $APPLICATION
 * @global $USER
 */

if (!$USER->IsAdmin()) {
    return;
}

use \Ion\Settings;

$spaces = Settings::getSpaces();
$spaces_menu = array();

foreach ($spaces as $i => $space) {
    $spaces_menu[] = array(
        "sort" => $i,
        "text" => $space["NAME"],
        "title" => $space["NAME"],
        "url" => "ion_settings_space_edit.php?space_code=" . $space["CODE"] . "&lang=" . LANGUAGE_ID,
    );
}

return array(
    array(
        "parent_menu" => "global_menu_content",
        "sort" => 10,
        "module_id" => "ion",
        "text" => "ion " . ION_VERSION,
        "title" => "ion " . ION_VERSION,
        "icon" => "ion_menu_icon",
        "items_id" => "menu_ion_settings",
        "items" => array(
            array(
                "sort" => 10,
                "text" => "Пространства",
                "title" => "Пространства",
                "url" => "ion_settings_space_list.php",
                "items_id" => "menu_ion_spaces",
                "items" => $spaces_menu
            ),
            array(
                "sort" => 20,
                "text" => "Конфигурация",
                "title" => "Конфигурация",
                "url" => "ion_settings_conf.php",
            )
        )
    ),
);
