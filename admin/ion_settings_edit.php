<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?php
/**
 * @global $APPLICATION
 * @var $USER_FIELD_MANAGER
 * @var $REQUEST_METHOD
 */

use \Ion\Settings;

$APPLICATION->SetTitle("Конфигурация модуля");

$install_status = CModule::IncludeModuleEx("ion");
$settings = new Settings();
$spaces = Settings::getSystemField("UF_SPACES");

if ($REQUEST_METHOD === "POST" && isset($ENTITY_ID) && check_bitrix_sessid()) {
	$settings->fillFields($ENTITY_ID);

	if ($settings->updateFields($ENTITY_ID)) {
		LocalRedirect($APPLICATION->GetCurPageParam("SUCCESS=Y", array("SUCCESS", "ERROR")));
	} else {
		LocalRedirect($APPLICATION->GetCurPageParam("ERROR=Y", array("SUCCESS", "ERROR")));
	}
}

if (isset($SUCCESS) && $SUCCESS === "Y") {
	echo CAdminMessage::ShowMessage(
		array(
			"TYPE" => "OK",
			"MESSAGE" => "UPDATE SUCCESS",
			"DETAILS" => "",
			"HTML" => true
		)
	);
}

if (isset($ERROR) && $ERROR === "Y") {
	echo CAdminMessage::ShowMessage(
		array(
			"TYPE" => "ERROR",
			"MESSAGE" => "UPDATE ERROR",
			"DETAILS" => $settings->LAST_ERROR,
			"HTML" => true
		)
	);
}

$tabs = array(
	array(
		"DIV" => "edit_system",
		"TAB" => "Системные настройки",
		"TITLE" => "Системные настройки",
		"ICON" => ""
	),
);
foreach ($spaces as $space) {
	$tabs[] = array(
		"DIV" => "edit_space_" . $space,
		"TAB" => $space,
		"TITLE" => "Пространство " . $space,
		"ICON" => ""
	);
}
$tabControl = new CAdminTabControl("tabControl", $tabs);
?>

<?php $tabControl->Begin(); ?>

<?= $USER_FIELD_MANAGER->ShowScript() ?>

<form method="post" action="<?= $APPLICATION->GetCurPage() ?>" enctype="multipart/form-data">
	<?= bitrix_sessid_post() ?>
	<?php
	$tabControl->BeginNextTab();
	$system_fields_entity_id = "ION_SYSTEM";
	$system_fields = $USER_FIELD_MANAGER->GetUserFields($system_fields_entity_id, ION_SETTINGS_ID);
	?>
    <input type="hidden" name="ENTITY_ID" value="<?= $system_fields_entity_id ?>">
	<?php foreach ($system_fields as $system_field_name => $system_field): ?>
        <tr>
            <td></td>
            <td>
                <span class="ion_primary_span">ID: <?= $system_field["ID"] ?></span>
                <span class="ion_primary_span">CODE: <?= $system_field_name ?></span>
                <span class="ion_primary_span">SORT: <?= $system_field["SORT"] ?></span>
            </td>
        </tr>
		<?= $USER_FIELD_MANAGER->GetEditFormHTML(false, $GLOBALS[$system_field_name], $system_field) ?>
	<?php endforeach; ?>
    <tr>
        <td style="text-align: left">
            <a href="/bitrix/admin/userfield_edit.php?ENTITY_ID=<?= $system_fields_entity_id ?>">
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
		<?php
		$tabControl->BeginNextTab();
		$fields_entity_id = "ION_SPACE_" . $space;
		$fields = $USER_FIELD_MANAGER->GetUserFields($fields_entity_id, ION_SETTINGS_ID);
		?>
        <input type="hidden" name="ENTITY_ID" value="<?= $fields_entity_id ?>">
		<?php foreach ($fields as $field_name => $field): ?>
            <tr>
                <td></td>
                <td>
                    <span class="ion_primary_span">ID: <?= $field["ID"] ?></span>
                    <span class="ion_primary_span">CODE: <?= $field_name ?></span>
                    <span class="ion_primary_span">SORT: <?= $field["SORT"] ?></span>
                </td>
            </tr>
			<?= $USER_FIELD_MANAGER->GetEditFormHTML(false, $GLOBALS[$field_name], $field) ?>
		<?php endforeach; ?>
        <tr>
            <td style="text-align: left">
                <a href="/bitrix/admin/userfield_edit.php?ENTITY_ID=<?= $fields_entity_id ?>">
                    Добавить поле в пространство <?= $space ?>
                </a>
            </td>
            <td style="text-align: right">
                <input type="submit" name="APPLY" value="Применить">
            </td>
        </tr>
		<?php $tabControl->EndTab(); ?>
    </form>
<?php endforeach; ?>

<?php $tabControl->End(); ?>

<?= BeginNote() ?>
<b>Системное:</b> <i>\Ion\Settings:getSystemField("UF_FIELD");</i><br>
<b>Пространство:</b> <i>\Ion\Settings::getSpaceField("UF_FIELD", "SPACE");</i>
<?= EndNote() ?>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>
