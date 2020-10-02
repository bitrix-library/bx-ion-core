<?php

/**
 * @module ion
 */
class ion extends CModule
{
	public $MODULE_ID = "ion";
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_CSS;
	
	public function __construct()
	{
		$this->MODULE_VERSION = "dev";
		$this->MODULE_VERSION_DATE = "2020-10-01 18:00";
		$this->MODULE_NAME = "ION";
		$this->MODULE_DESCRIPTION = "Sources: github.com/amensum/ion";
	}
	
	public function InstallFiles()
	{
//		CopyDirFiles(
//			$_SERVER["DOCUMENT_ROOT"]."/local/modules/ion/install/components",
//			$_SERVER["DOCUMENT_ROOT"]."/local/components",
//			true,
//			true
//		);
		return true;
	}
	
	public function UnInstallFiles()
	{
//		DeleteDirFilesEx("/local/components/ion");
		return true;
	}
	
	public function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->InstallFiles();
		
		// <EVENTS>
		$eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler("main", "OnProlog", $this->MODULE_ID, "\Ion\Ion", "connectOnProlog");
        $eventManager->registerEventHandler("main", "OnEpilog", $this->MODULE_ID, "\Ion\Ion", "connectOnEpilog");
        $eventManager->registerEventHandler("main", "OnAfterEpilog", $this->MODULE_ID, "\Ion\Ion", "connectOnAfterEpilog");
		// </EVENTS>
		
		RegisterModule($this->MODULE_ID);
	}
	
	public function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->UnInstallFiles();
		
		// <EVENTS>
		$eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler("main", "OnProlog", $this->MODULE_ID, "\Ion\Ion", "connectOnProlog");
        $eventManager->unRegisterEventHandler("main", "OnEpilog", $this->MODULE_ID, "\Ion\Ion", "connectOnEpilog");
        $eventManager->unRegisterEventHandler("main", "OnAfterEpilog", $this->MODULE_ID, "\Ion\Ion", "connectOnAfterEpilog");
		// </EVENTS>
		
		UnRegisterModule($this->MODULE_ID);
	}
}