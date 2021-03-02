<?php
/**
 * @global $APPLICATION
 * @global $USER
 * @var $USER_FIELD_MANAGER
 * @var $REQUEST_METHOD
 * @var $space_code
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

if (!$USER->IsAdmin()) {
    return;
}

use \Ion\Settings;

$cur_page_url = urlencode($APPLICATION->GetCurPage());
$install_status = CModule::IncludeModuleEx("ion");
$settings = new Settings();

$space_name = Settings::getSpaceField("UF_NAME", $space_code);
if ($space_name === false) {
    $space_name = $space_code;
}

if ($REQUEST_METHOD === "POST" && isset($ENTITY_ID) && check_bitrix_sessid()) {
    $settings->fillFields($ENTITY_ID);

    if ($settings->updateFields($ENTITY_ID)) {
        LocalRedirect($APPLICATION->GetCurPageParam("success=Y", array("success", "error")));
    } else {
        LocalRedirect($APPLICATION->GetCurPageParam("error=Y", array("success", "error")));
    }
}

$tabControl = new CAdminTabControl("tabControl", array(
    array(
        "DIV" => strtolower($space_code),
        "TAB" => "Пространство \"$space_name\"",
        "TITLE" => "Поля пространства \"$space_name\"",
        "ICON" => ""
    )
));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?php
if (isset($success) && $success === "Y") {
    CAdminMessage::ShowMessage(
        array(
            "TYPE" => "OK",
            "MESSAGE" => "Успешно",
            "DETAILS" => "Конфигурация модуля сохранена",
            "HTML" => true
        )
    );
}

if (isset($error) && $error === "Y") {
    CAdminMessage::ShowMessage(
        array(
            "TYPE" => "error",
            "MESSAGE" => "Ошибка",
            "DETAILS" => "Не удалось сохранить конфигурацию модуля. $settings->LAST_ERROR",
            "HTML" => true
        )
    );
}
?>

<?php
global $ION_HEADER;
echo $ION_HEADER;
?>

<?php $tabControl->Begin(); ?>
<?= $USER_FIELD_MANAGER->ShowScript() ?>
<form method="post" action="<?= $APPLICATION->GetCurPageParam() ?>" enctype="multipart/form-data">
    <?= bitrix_sessid_post() ?>
    <?php $tabControl->BeginNextTab(); ?>
    <?php
    $fields_entity_id = "ION_SPACE_" . $space_code;
    $fields = $USER_FIELD_MANAGER->GetUserFields($fields_entity_id, ION_SETTINGS_ID, LANGUAGE_ID);
    ?>
    <input type="hidden" name="ENTITY_ID" value="<?= $fields_entity_id ?>">
    <?php foreach ($fields as $field_name => $field): ?>
        <tr>
            <td></td>
            <td>
                <span class="ion_primary_span">ID: <?= $field["ID"] ?></span>
                <span class="ion_primary_span">FIELD_NAME: <?= $field["FIELD_NAME"] ?></span>
                <span class="ion_primary_span">USER_TYPE_ID: <?= $field["USER_TYPE_ID"] ?></span>
                <span class="ion_primary_span">SORT: <?= $field["SORT"] ?></span>
            </td>
        </tr>
        <?= $USER_FIELD_MANAGER->GetEditFormHTML(false, $GLOBALS[$field_name], $field) ?>
    <?php endforeach; ?>
    <tr>
        <td style="text-align: left">
            <a href="/bitrix/admin/userfield_edit.php?ENTITY_ID=<?= $fields_entity_id ?>&back_url=<?= $cur_page_url ?>">
                Добавить поле в пространство <?= $space_name ?>
            </a>
        </td>
        <td style="text-align: right">
            <input type="submit" name="APPLY" value="Применить">
        </td>
    </tr>
    <?php $tabControl->EndTab(); ?>
</form>
<?php $tabControl->End(); ?>

<div class="ion_docblock">
    <p>Для указания пространству имени, необходимо создать и заполнить в нем поле UF_NAME.</p>
    <b>Код для получения полей пространства:</b>
    <div class="ion_docblock_code">
        Ion\Settings::getSpaceField("UF_NAME", "<?= $space_code ?>");<br>
        Ion\Settings::getSpaceFields("<?= $space_code ?>");<br>
    </div>
</div>

<?php
global $ION_FOOTER;
echo $ION_FOOTER;
?>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>
