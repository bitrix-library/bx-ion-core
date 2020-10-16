<?php

use Ion\Ion as I;
use Bitrix\Main\EventManager;

/**
 * Class IonModule
 * @module Ion
 */
class Ion extends CModule
{
	public $MODULE_ID = "ion";
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_CSS;

	/**
	 * Ion constructor.
	 */
	public function __construct()
	{
		$this->MODULE_VERSION = "1.3";
		$this->MODULE_VERSION_DATE = "2020-10-15 15:00";
		$this->MODULE_NAME = "ION";
		$this->MODULE_DESCRIPTION = "Sources: github.com/amensum/ion";
	}

	/**
	 * @return bool
	 */
	public function InstallFiles(): bool
	{
		// CopyDirFiles(
		// 	$_SERVER["DOCUMENT_ROOT"]."/local/modules/ion/install/components",
		// 	$_SERVER["DOCUMENT_ROOT"]."/local/components",
		// 	true,
		// 	true
		// );
		return true;
	}

	/**
	 * @return bool
	 */
	public function UnInstallFiles(): bool
	{
		// DeleteDirFilesEx("/local/components/ion");
		return true;
	}

	/**
	 * @return void
	 */
	public function DoInstall(): void
	{
		// global $DOCUMENT_ROOT, $APPLICATION;
		$this->InstallFiles();

		$eventManager = EventManager::getInstance();
		$eventManager->registerEventHandler("main", "OnProlog", $this->MODULE_ID, I::class, "connectOnProlog");
		$eventManager->registerEventHandler("main", "OnEpilog", $this->MODULE_ID, I::class, "connectOnEpilog");
		$eventManager->registerEventHandler("main", "OnAfterEpilog", $this->MODULE_ID, I::class, "connectOnAfterEpilog");

		RegisterModule($this->MODULE_ID);
	}

	/**
	 * @return void
	 */
	public function DoUninstall(): void
	{
		// global $DOCUMENT_ROOT, $APPLICATION;
		$this->UnInstallFiles();

		$eventManager = EventManager::getInstance();
		$eventManager->unRegisterEventHandler("main", "OnProlog", $this->MODULE_ID, I::class, "connectOnProlog");
		$eventManager->unRegisterEventHandler("main", "OnEpilog", $this->MODULE_ID, I::class, "connectOnEpilog");
		$eventManager->unRegisterEventHandler("main", "OnAfterEpilog", $this->MODULE_ID, I::class, "connectOnAfterEpilog");

		UnRegisterModule($this->MODULE_ID);
	}
}