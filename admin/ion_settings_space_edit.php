<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php"); ?>

<?php
/**
 * @global $APPLICATION
 * @global $USER
 * @var $USER_FIELD_MANAGER
 * @var $REQUEST_METHOD
 */

if (!$USER->IsAdmin()) {
	return;
}
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php"); ?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>