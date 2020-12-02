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
			"ION_SYSTEM",
			"UF_SPACES",
			ION_SETTINGS_ID
		);

		$include_js_field = $this->USER_FIELD_MANAGER->GetUserFieldValue(
			"ION_SYSTEM",
			"UF_INCLUDE_JS",
			ION_SETTINGS_ID
		);

		$include_css_field = $this->USER_FIELD_MANAGER->GetUserFieldValue(
			"ION_SYSTEM",
			"UF_INCLUDE_CSS",
			ION_SETTINGS_ID
		);

		$include_react_field = $this->USER_FIELD_MANAGER->GetUserFieldValue(
			"ION_SYSTEM",
			"UF_INCLUDE_REACT",
			ION_SETTINGS_ID
		);

		if ($spaces_field === false) {
			$ru_label = "Список пространств для хранения полей";

			$field = array(
				"ENTITY_ID" => "ION_SYSTEM",
				"FIELD_NAME" => "UF_SPACES",
				"USER_TYPE_ID" => "string",
				"XML_ID" => "",
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

		if ($include_js_field === false) {
			$ru_label = "Подключать JS из модуля";

			$field = array(
				"ENTITY_ID" => "ION_SYSTEM",
				"FIELD_NAME" => "UF_INCLUDE_JS",
				"USER_TYPE_ID" => "boolean",
				"XML_ID" => "",
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
				"ENTITY_ID" => "ION_SYSTEM",
				"FIELD_NAME" => "UF_INCLUDE_CSS",
				"USER_TYPE_ID" => "boolean",
				"XML_ID" => "",
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
				"ENTITY_ID" => "ION_SYSTEM",
				"FIELD_NAME" => "UF_INCLUDE_REACT",
				"USER_TYPE_ID" => "boolean",
				"XML_ID" => "",
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

	public static function getSystemFields()
	{
		global $USER_FIELD_MANAGER;

		$entity_id = "ION_SYSTEM";

		return $USER_FIELD_MANAGER->GetUserFields(
			$entity_id,
			ION_SETTINGS_ID
		);
	}

	public static function getSpaceFields($space)
	{
		global $USER_FIELD_MANAGER;

		$entity_id = "ION_SPACE_" . $space;

		return $USER_FIELD_MANAGER->GetUserFields(
			$entity_id,
			ION_SETTINGS_ID
		);
	}

	public static function getSystemField($field)
	{
		global $USER_FIELD_MANAGER;

		$entity_id = "ION_SYSTEM";

		return $USER_FIELD_MANAGER->GetUserFieldValue(
			$entity_id,
			$field,
			ION_SETTINGS_ID
		);
	}

	public static function getSpaceField($field, $space)
	{
		global $USER_FIELD_MANAGER;

		$entity_id = "ION_SPACE_" . $space;

		return $USER_FIELD_MANAGER->GetUserFieldValue(
			$entity_id,
			$field,
			ION_SETTINGS_ID
		);
	}
}
