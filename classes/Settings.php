<?php

namespace Ion;

class Settings
{
	static private $arFields = false;
	public $LAST_ERROR = "";

	public static function GetFields()
	{
		$arResult = array();

		if (is_array(self::$arFields)) {
			$arResult = self::$arFields;
		} else {
			$arResult = array();

			$obCache = new \CPHPCache;
			if ($obCache->InitCache(14400, 1, "ion")) {
				$arResult = $obCache->GetVars();
			} elseif ($obCache->StartDataCache()) {
				$arResult = self::__GetFields();

				$obCache->EndDataCache($arResult);
			}

			self::$arFields = $arResult;
		}

		return $arResult;
	}

	private static function __GetFields()
	{
		global $USER_FIELD_MANAGER;

		$arResult = array();

		$arUserFields = $USER_FIELD_MANAGER->GetUserFields("ION", ION_SETTINGS_ID);

		foreach ($arUserFields as $FIELD_NAME => $arUserField) {
			$arResult[$FIELD_NAME] = $arUserField['VALUE'];
		}

		return $arResult;
	}

	public static function ClearCache()
	{
		$obCache = new \CPHPCache();
		$obCache->CleanDir("ion");
	}

	public function Update($arFields)
	{
		global $APPLICATION;

		$result = true;

		$this->LAST_ERROR = "";

		$APPLICATION->ResetException();
		$events = GetModuleEvents("ion", "OnBeforeSettingsUpdate");
		while ($arEvent = $events->Fetch()) {
			$bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
			if ($bEventRes === false) {
				if ($err = $APPLICATION->GetException()) {
					$this->LAST_ERROR .= $err->GetString();
				} else {
					$APPLICATION->ThrowException("Unknown error");
					$this->LAST_ERROR .= "Unknown error";
				}

				$result = false;
				break;
			}
		}

		if ($result) {
			global $USER_FIELD_MANAGER;

			$USER_FIELD_MANAGER->Update("ION", ION_SETTINGS_ID, $arFields);
			self::ClearCache();

			$events = GetModuleEvents("ion", "OnAfterSettingsUpdate");
			while ($arEvent = $events->Fetch()) {
				ExecuteModuleEventEx($arEvent, array(&$arFields));
			}
		}

		return $result;
	}
}
