<?php

namespace Ion;

use CBitrixComponent;

/**
 * Class IonFields
 */
class IonFields extends CBitrixComponent
{
    public function executeComponent()
    {

        foreach ($this->arParams["FIELDS"] as $field) {
            $this->arResult["ITEMS"][$field] = Settings::getSpaceField($field, $this->arParams["SPACE"]);
        }

        $this->IncludeComponentTemplate();

        parent::executeComponent();
    }
}
