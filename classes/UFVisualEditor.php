<?php

namespace Ion;

use \CFileMan;

/**
 * Class UFVisualEditor
 * @package Ion
 */
final class UFVisualEditor
{
	/**
	 * @return string[]|null
	 */
	public function GetUserTypeDescription(): ?array
	{
		return [
			"CLASS_NAME" => self::class,
			"BASE_TYPE" => "string",
			"USER_TYPE_ID" => "editor",
			"DESCRIPTION" => "Визуальный редактор (ion)"
		];
	}

	/**
	 * @return string|null
	 */
	public function GetDBColumnType(): ?string
	{
		return "text";
	}

	/**
	 * @param $arUserField
	 * @param $arHtmlControl
	 * @return string|null
	 */
	public function GetEditFormHTML($arUserField, $arHtmlControl): ?string
	{
		ob_start();
		CFileMan::AddHTMLEditorFrame(
			$arHtmlControl["NAME"],
			$arHtmlControl["VALUE"],
			false,
			"html",
			["height" => "120"]
		);
		return ob_get_clean();
	}
}