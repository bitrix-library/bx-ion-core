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

		if ($spaces_field === false) {
			$ru_label = 'Список пространств для хранения полей';

			$field = array(
				'ENTITY_ID' => 'ION_SYSTEM',
				'FIELD_NAME' => 'UF_SPACES',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => '',
				'SORT' => 100,
				'MULTIPLE' => 'Y',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => '',
				'EDIT_IN_LIST' => '',
				'IS_SEARCHABLE' => 'N',
				'SETTINGS' => array(
					'DEFAULT_VALUE' => '',
					'SIZE' => '20',
					'ROWS' => '1',
					'MIN_LENGTH' => '0',
					'MAX_LENGTH' => '0',
					'REGEXP' => '',
				),
				'EDIT_FORM_LABEL' => array(
					'ru' => $ru_label,
					'en' => '',
				),
				'LIST_COLUMN_LABEL' => array(
					'ru' => $ru_label,
					'en' => '',
				),
				'LIST_FILTER_LABEL' => array(
					'ru' => $ru_label,
					'en' => '',
				),
				'ERROR_MESSAGE' => array(
					'ru' => $ru_label,
					'en' => '',
				),
				'HELP_MESSAGE' => array(
					'ru' => $ru_label,
					'en' => '',
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
