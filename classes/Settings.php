<?php

namespace Ion;

/**
 * Class Settings
 * @package Ion
 */
class Settings
{
    public $USER_FIELD_MANAGER;
    public $LAST_ERROR;
    public $FIELDS;

    public function __construct()
    {
        global $USER_FIELD_MANAGER;

        $this->USER_FIELD_MANAGER = $USER_FIELD_MANAGER;
        $this->LAST_ERROR = "";
        $this->FIELDS = array();

        $this->prepareSpacesField();
    }

    public function prepareSpacesField()
    {
        $spaces_field = $this->USER_FIELD_MANAGER->GetUserFieldValue(
            "ION_SPACE_SYSTEM",
            "UF_SPACES",
            ION_SETTINGS_ID
        );

        $dev_mode_field = $this->USER_FIELD_MANAGER->GetUserFieldValue(
            "ION_SPACE_SYSTEM",
            "UF_DEV_MODE",
            ION_SETTINGS_ID
        );

        $include_js_field = $this->USER_FIELD_MANAGER->GetUserFieldValue(
            "ION_SPACE_SYSTEM",
            "UF_INCLUDE_JS",
            ION_SETTINGS_ID
        );

        $include_css_field = $this->USER_FIELD_MANAGER->GetUserFieldValue(
            "ION_SPACE_SYSTEM",
            "UF_INCLUDE_CSS",
            ION_SETTINGS_ID
        );

        $include_react_field = $this->USER_FIELD_MANAGER->GetUserFieldValue(
            "ION_SPACE_SYSTEM",
            "UF_INCLUDE_REACT",
            ION_SETTINGS_ID
        );

        if ($spaces_field === false) {
            $ru_label = "Список пространств для хранения полей";

            $field = array(
                "ENTITY_ID" => "ION_SPACE_SYSTEM",
                "FIELD_NAME" => "UF_SPACES",
                "USER_TYPE_ID" => "string",
                "SORT" => 100,
                "MULTIPLE" => "Y",
                "MANDATORY" => "N",
                "SHOW_FILTER" => "N",
                "SHOW_IN_LIST" => "",
                "EDIT_IN_LIST" => "",
                "IS_SEARCHABLE" => "N",
                "SETTINGS" => array(
                    "DEFAULT_VALUE" => "",
                    "SIZE" => "20",
                    "ROWS" => "1",
                    "MIN_LENGTH" => "0",
                    "MAX_LENGTH" => "0",
                    "REGEXP" => "",
                ),
                "EDIT_FORM_LABEL" => array(
                    "ru" => $ru_label,
                    "en" => "",
                ),
            );

            $entity = new \CUserTypeEntity();
            $entity->Add($field);
        }

        if ($dev_mode_field === false) {
            $ru_label = "Режим разработки";

            $field = array(
                "ENTITY_ID" => "ION_SPACE_SYSTEM",
                "FIELD_NAME" => "UF_DEV_MODE",
                "USER_TYPE_ID" => "boolean",
                "SORT" => 125,
                "MULTIPLE" => "N",
                "MANDATORY" => "N",
                "SHOW_FILTER" => "N",
                "SHOW_IN_LIST" => "",
                "EDIT_IN_LIST" => "",
                "IS_SEARCHABLE" => "N",
                "SETTINGS" => array(
                    "DEFAULT_VALUE" => false,
                ),
                "EDIT_FORM_LABEL" => array(
                    "ru" => $ru_label,
                    "en" => "",
                ),
            );

            $entity = new \CUserTypeEntity();
            $entity->Add($field);
        }

        if ($include_js_field === false) {
            $ru_label = "Подключать JS из модуля";

            $field = array(
                "ENTITY_ID" => "ION_SPACE_SYSTEM",
                "FIELD_NAME" => "UF_INCLUDE_JS",
                "USER_TYPE_ID" => "boolean",
                "SORT" => 150,
                "MULTIPLE" => "N",
                "MANDATORY" => "N",
                "SHOW_FILTER" => "N",
                "SHOW_IN_LIST" => "",
                "EDIT_IN_LIST" => "",
                "IS_SEARCHABLE" => "N",
                "SETTINGS" => array(
                    "DEFAULT_VALUE" => false,
                ),
                "EDIT_FORM_LABEL" => array(
                    "ru" => $ru_label,
                    "en" => "",
                ),
            );

            $entity = new \CUserTypeEntity();
            $entity->Add($field);
        }

        if ($include_css_field === false) {
            $ru_label = "Подключать CSS из модуля";

            $field = array(
                "ENTITY_ID" => "ION_SPACE_SYSTEM",
                "FIELD_NAME" => "UF_INCLUDE_CSS",
                "USER_TYPE_ID" => "boolean",
                "SORT" => 200,
                "MULTIPLE" => "N",
                "MANDATORY" => "N",
                "SHOW_FILTER" => "N",
                "SHOW_IN_LIST" => "",
                "EDIT_IN_LIST" => "",
                "IS_SEARCHABLE" => "N",
                "SETTINGS" => array(
                    "DEFAULT_VALUE" => false,
                ),
                "EDIT_FORM_LABEL" => array(
                    "ru" => $ru_label,
                    "en" => "",
                ),
            );

            $entity = new \CUserTypeEntity();
            $entity->Add($field);
        }

        if ($include_react_field === false) {
            $ru_label = "Подключать React и Babel";

            $field = array(
                "ENTITY_ID" => "ION_SPACE_SYSTEM",
                "FIELD_NAME" => "UF_INCLUDE_REACT",
                "USER_TYPE_ID" => "boolean",
                "SORT" => 250,
                "MULTIPLE" => "N",
                "MANDATORY" => "N",
                "SHOW_FILTER" => "N",
                "SHOW_IN_LIST" => "",
                "EDIT_IN_LIST" => "",
                "IS_SEARCHABLE" => "N",
                "SETTINGS" => array(
                    "DEFAULT_VALUE" => false,
                ),
                "EDIT_FORM_LABEL" => array(
                    "ru" => $ru_label,
                    "en" => "",
                ),
            );

            $entity = new \CUserTypeEntity();
            $entity->Add($field);
        }

        return true;
    }

    public function fillFields($entity_id)
    {
        return $this->USER_FIELD_MANAGER->EditFormAddFields($entity_id, $this->FIELDS);
    }

    public function updateFields($entity_id)
    {
        return $this->USER_FIELD_MANAGER->Update($entity_id, ION_SETTINGS_ID, $this->FIELDS);
    }

    public static function getSpaces()
    {
        $spaces = array();
        foreach (self::getSpaceField("UF_SPACES", "SYSTEM") as $space_code) {
            $space_name = self::getSpaceField("UF_NAME", $space_code);

            if ($space_name === false) {
                $space_name = $space_code;
            }

            $spaces[] = array(
                "CODE" => $space_code,
                "NAME" => $space_name
            );
        }

        return $spaces;
    }

    public static function getSpaceField($field, $space)
    {
        global $USER_FIELD_MANAGER;

        $entity_id = "ION_SPACE_" . $space;

        return $USER_FIELD_MANAGER->GetUserFieldValue(
            $entity_id,
            $field,
            ION_SETTINGS_ID,
            LANGUAGE_ID
        );
    }

    public static function getSpaceFields($space)
    {
        global $USER_FIELD_MANAGER;

        $entity_id = "ION_SPACE_" . $space;

        return $USER_FIELD_MANAGER->GetUserFields(
            $entity_id,
            ION_SETTINGS_ID,
            LANGUAGE_ID
        );
    }

    public static function cloneSpace($space_from, $space_to)
    {
        $to_entity_id = "ION_SPACE_" . $space_to;

        $fields = self::getSpaceFields($space_from);

        foreach ($fields as $field) {
            $cloned_field = array(
                "ENTITY_ID" => $to_entity_id,
                "FIELD_NAME" => $field["FIELD_NAME"],
                "USER_TYPE_ID" => $field["USER_TYPE_ID"],
                "SORT" => $field["SORT"],
                "MULTIPLE" => $field["MULTIPLE"],
                "MANDATORY" => $field["MANDATORY"],
                "SHOW_FILTER" => $field["SHOW_FILTER"],
                "SHOW_IN_LIST" => $field["SHOW_IN_LIST"],
                "EDIT_IN_LIST" => $field["EDIT_IN_LIST"],
                "IS_SEARCHABLE" => $field["IS_SEARCHABLE"],
                "SETTINGS" => $field["SETTINGS"],
                "EDIT_FORM_LABEL" => array(
                    "ru" => $field["EDIT_FORM_LABEL"],
                    "en" => ""
                ),
            );

            $entity = new \CUserTypeEntity();
            $entity->Add($cloned_field);
        }
    }
}
