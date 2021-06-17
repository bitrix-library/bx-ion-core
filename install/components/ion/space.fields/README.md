__# Bitrix ion space.fields component

__Component calling example for default template:__
```php
$APPLICATION->IncludeComponent(
    "ion:space.fields",
    "",
    array(
        "SPACE" => "MAIN",
        "FIELDS" => array(
            0 => "UF_FIELD0",
            1 => "UF_FIELD1",
            2 => "UF_FIELD2",
        ),
    ),
);
```
