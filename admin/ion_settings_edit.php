<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin.php");
?>

<?php
/**
 * @global $APPLICATION
 * @var $USER_FIELD_MANAGER
 * @var $REQUEST_METHOD
 */

use \Ion\Settings;

$APPLICATION->SetTitle("Конфигурация модуля");

$cur_page_url = urlencode($APPLICATION->GetCurPage());

$install_status = CModule::IncludeModuleEx("ion");
$settings = new Settings();
$sp_codes = Settings::getSystemField("UF_SPACES");

$spaces = array();
foreach ($sp_codes as $space_code) {
	$space_name = Settings::getSpaceField("UF_NAME", $space_code);
	if ($space_name === false) {
		$space_name = $space_code;
	}
	$spaces[] = array(
		"CODE" => $space_code,
		"NAME" => $space_name
	);
}

if ($REQUEST_METHOD === "POST" && isset($ENTITY_ID) && check_bitrix_sessid()) {
	$settings->fillFields($ENTITY_ID);

	if ($settings->updateFields($ENTITY_ID)) {
		LocalRedirect($APPLICATION->GetCurPageParam("SUCCESS=Y", array("SUCCESS", "ERROR")));
	} else {
		LocalRedirect($APPLICATION->GetCurPageParam("ERROR=Y", array("SUCCESS", "ERROR")));
	}
}

if (isset($SUCCESS) && $SUCCESS === "Y") {
	CAdminMessage::ShowMessage(
		array(
			"TYPE" => "OK",
			"MESSAGE" => "Успешно",
			"DETAILS" => "Конфигурация модуля сохранена",
			"HTML" => true
		)
	);
}

if (isset($ERROR) && $ERROR === "Y") {
	CAdminMessage::ShowMessage(
		array(
			"TYPE" => "ERROR",
			"MESSAGE" => "Ошибка",
			"DETAILS" => "Не удалось сохранить конфигурацию модуля. $settings->LAST_ERROR",
			"HTML" => true
		)
	);
}

$tabs = array(
	array(
		"DIV" => "system",
		"TAB" => "Системные настройки",
		"TITLE" => "Системные настройки",
		"ICON" => ""
	)
);
foreach ($spaces as $space) {
	$tabs[] = array(
		"DIV" => "space_" . $space["CODE"],
		"TAB" => $space["NAME"],
		"TITLE" => "Пространство " . $space["NAME"],
		"ICON" => ""
	);
}
$tabs[] = array(
	"DIV" => "docs",
	"TAB" => "Документация",
	"TITLE" => "Документация",
	"ICON" => ""
);
$tabControl = new CAdminTabControl("tabControl", $tabs);
?>

<?php $tabControl->Begin(); ?>

<?= $USER_FIELD_MANAGER->ShowScript() ?>

<form method="post" action="<?= $APPLICATION->GetCurPage() ?>" enctype="multipart/form-data">
	<?= bitrix_sessid_post() ?>
	<?php $tabControl->BeginNextTab(); ?>
	<?php
	$system_fields_entity_id = "ION_SYSTEM";
	$system_fields = $USER_FIELD_MANAGER->GetUserFields($system_fields_entity_id, ION_SETTINGS_ID, LANGUAGE_ID);
	?>
    <input type="hidden" name="ENTITY_ID" value="<?= $system_fields_entity_id ?>">
	<?php foreach ($system_fields as $system_field_name => $system_field): ?>
        <tr>
            <td></td>
            <td>
                <span class="ion_primary_span">ID: <?= $system_field["ID"] ?></span>
                <span class="ion_primary_span">FIELD_NAME: <?= $system_field["FIELD_NAME"] ?></span>
                <span class="ion_primary_span">USER_TYPE_ID: <?= $system_field["USER_TYPE_ID"] ?></span>
                <span class="ion_primary_span">SORT: <?= $system_field["SORT"] ?></span>
            </td>
        </tr>
		<?= $USER_FIELD_MANAGER->GetEditFormHTML(false, $GLOBALS[$system_field_name], $system_field) ?>
	<?php endforeach; ?>
    <tr>
        <td style="text-align: left">
            <a href="/bitrix/admin/userfield_edit.php?ENTITY_ID=<?= $system_fields_entity_id ?>&back_url=<?= $cur_page_url ?>">
                Добавить поле в системные настройки
            </a>
        </td>
        <td style="text-align: right">
            <input type="submit" name="APPLY" value="Применить">
        </td>
    </tr>
	<?php $tabControl->EndTab(); ?>
</form>

<?php foreach ($spaces as $space): ?>
    <form method="post" action="<?= $APPLICATION->GetCurPage() ?>" enctype="multipart/form-data">
		<?= bitrix_sessid_post() ?>
		<?php $tabControl->BeginNextTab(); ?>
		<?php
		$fields_entity_id = "ION_SPACE_" . $space["CODE"];
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
                    Добавить поле в пространство <?= $space["NAME"] ?>
                </a>
            </td>
            <td style="text-align: right">
                <input type="submit" name="APPLY" value="Применить">
            </td>
        </tr>
		<?php $tabControl->EndTab(); ?>
    </form>
<?php endforeach; ?>

<?php $tabControl->BeginNextTab(); ?>
<?= BeginNote() ?>
<h3>Документация</h3>
<p>Для указания пространству имени, необходимо создать и заполнить в нем поле UF_NAME.</p>
<b>Код для получения полей системного пространства:</b>
<div class="ion_docblock_code">
    \Ion\Settings::getSystemField("UF_FIELD");<br>
    \Ion\Settings::getSystemFields();<br>
</div>
<b>Код для получения полей пользовательского пространства:</b>
<div class="ion_docblock_code">
    \Ion\Settings::getSpaceField("UF_FIELD", "SPACE");<br>
    \Ion\Settings::getSpaceFields("SPACE");<br>
</div>
<?= EndNote() ?>
<?php $tabControl->EndTab(); ?>

<?php $tabControl->End(); ?>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>
