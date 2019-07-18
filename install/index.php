<?php

/**
 * Class Ion
 */
class ion extends CModule
{
	var $MODULE_ID = "ion";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	
	function __construct()
	{
		$arModuleVersion = array();
		
		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");
		

		$this->MODULE_VERSION = "1.0.0";
		$this->MODULE_VERSION_DATE = "2019-07-18 18:00";
		$this->MODULE_NAME = "ION";
		$this->MODULE_DESCRIPTION = "Sources: github.com/amensum/ion";
	}
	
	function InstallFiles()
	{
//		CopyDirFiles(
//			$_SERVER["DOCUMENT_ROOT"]."/local/modules/github.amensum.ion/install/components",
//			$_SERVER["DOCUMENT_ROOT"]."/bitrix/components",
//			true,
//			true
//		);
		return true;
	}
	
	function UnInstallFiles()
	{
//		DeleteDirFilesEx("/local/components/dv");
		return true;
	}
	
	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->InstallFiles();
		
		// <Events>
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler("main", "OnAfterEpilog", $this->MODULE_ID, "Ion", "connectOnAfterEpilog");
		// </Events>
		
		RegisterModule("ion");
	}
	
	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->UnInstallFiles();
		
		// <Events>
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler("main", "OnAfterEpilog", $this->MODULE_ID, "Ion", "connectOnAfterEpilog");
		// </Events>
		
		UnRegisterModule("ion");
	}
}