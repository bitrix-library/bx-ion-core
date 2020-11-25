<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php"); ?>

<?php
/**
 * @global $APPLICATION
 * @var $USER_FIELD_MANAGER
 * @var $REQUEST_METHOD
 * @var $Update
 */

$error = "";
$install_status = CModule::IncludeModuleEx("ion");

if ($REQUEST_METHOD === "POST" && strlen($Update) > 0 && check_bitrix_sessid()) {
	$arUpdateFields = array();
	$USER_FIELD_MANAGER->EditFormAddFields("ION", $arUpdateFields); // fill $arUpdateFields from $_POST and $_FILES

	$settings = new \Ion\Settings();
	$res = $settings->Update($arUpdateFields);
	if ($res) {
		LocalRedirect($APPLICATION->GetCurPageParam("ok=Y", array("ok")));
	} else {
		$error = $settings->LAST_ERROR;
	}
}

$APPLICATION->SetTitle("ION_TITLE");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => "ION_TAB1_TITLE",
		"ICON" => "",
		"TITLE" => "ION_TAB1_TITLE"
	),
);
?>

<?php if (isset($_REQUEST["ok"]) && $_REQUEST["ok"] === "Y"): ?>
	<?php
	CAdminMessage::ShowMessage(
		array(
			"TYPE" => "OK",
			"MESSAGE" => "ION_SUCCESS",
			"DETAILS" => "",
			"HTML" => true
		)
	);
	?>
<?php endif; ?>

<?php
if ($error !== "") {
	CAdminMessage::ShowMessage(
		array(
			"TYPE" => "ERROR",
			"MESSAGE" => $error,
			"DETAILS" => "",
			"HTML" => true
		)
	);
}
?>

<?php
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
<form method="post" action="<?= $APPLICATION->GetCurPage() ?>" enctype="multipart/form-data">
	<?= bitrix_sessid_post() ?>

	<?php $tabControl->BeginNextTab(); ?>

	<?= $USER_FIELD_MANAGER->ShowScript() ?>

	<?php
	$bVarsFromForm = false;
	$arUserFields = $USER_FIELD_MANAGER->GetUserFields("ION", ION_SETTINGS_ID);
	?>

	<?php foreach ($arUserFields as $FIELD_NAME => $arUserField): ?>
        <?php $arUserField['VALUE_ID'] = ION_SETTINGS_ID; ?>
        <tr>
            <td colspan="2">
            <span class="ion_primary_field">
                <?= $FIELD_NAME ?> (<?= $arUserField["SORT"] ?>)
            </span>
            </td>
        </tr>
		<?= $USER_FIELD_MANAGER->GetEditFormHTML($bVarsFromForm, $GLOBALS[$FIELD_NAME], $arUserField) ?>
	<?php endforeach; ?>

    <tr>
        <td colspan="2">
            <a href="/bitrix/admin/userfield_edit.php?ENTITY_ID=ION">
				<?= "ION_ADD_UF" ?>
            </a>
        </td>
    </tr>
	<?php $tabControl->EndTab(); ?>

	<?php $tabControl->Buttons(); ?>
    <input type="submit" name="Update" value="<?= "ION_SAVE" ?>">
</form>
<?php $tabControl->End(); ?>

<?= BeginNote() ?>
<b>Legacy:</b> <i>\COption::GetOptionString("ion", "UF_PHONE");</i>
<br>
<br>
<b>D7:</b> <i>\Bitrix\Main\Config\Option::get("ion", "UF_PHONE");</i>
<?= EndNote() ?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>
