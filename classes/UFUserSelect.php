<?php

namespace Ion;

/**
 * Class UFUserSelect
 * @package Ion
 */
final class UFUserSelect
{
    public function GetUserTypeDescription(): ?array
    {
        return [
            "CLASS_NAME" => self::class,
            "BASE_TYPE" => "string",
            "USER_TYPE_ID" => "ion_user_select_field",
            "DESCRIPTION" => "Выбор пользователя (ion)"
        ];
    }

    public function GetEditFormHTML($arUserField, $arHtmlControl): ?string
    {
        $form_name = "frm";
        if (substr_count($arUserField["ENTITY_ID"], "HLBLOCK_")) {
            $hl = (int)str_replace("HLBLOCK_", "", $arUserField["ENTITY_ID"]);
            $form_name = "hlrow_edit_" . $hl . "_form";
        } elseif (substr_count($arUserField["ENTITY_ID"], "IBLOCK_") && substr_count($arUserField["ENTITY_ID"], "_SECTION")) {
            $ibs = (int)str_replace("IBLOCK_", "", $arUserField["ENTITY_ID"]);
            $form_name = "form_section_" . $ibs . "_form";
        } elseif (substr_count($arUserField["ENTITY_ID"], "USER_")) {
            $form_name = "user_edit_form";
        }

        return FindUserID(
            $arUserField["FIELD_NAME"],
            $arUserField["VALUE"],
            '',
            $form_name
        );
    }
}
