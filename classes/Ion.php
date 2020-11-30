<?php

namespace Ion;

use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;

/**
 * Class Ion
 *
 * @author https://github.com/amensum
 * @package Ion
 */
class Ion
{
	private static $instance;
	private $context;
	private $request;
	private $module_absolute_path;
	private $module_relative_path;

	private function __construct()
	{
		$this->context = Application::getInstance()->getContext();
		$this->request = $this->context->getRequest();
		$this->module_absolute_path = str_replace("\\", "/", dirname(__DIR__ . '\\..\\'));
		$this->module_relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->module_absolute_path);
	}

	public static function getInstance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function onProlog(): void
	{
		$instance = self::getInstance();

		$include_js = Settings::getSystemField("UF_INCLUDE_JS");
		$include_css = Settings::getSystemField("UF_INCLUDE_CSS");

		if ($include_js) {
			Asset::getInstance()->addJs($instance->module_relative_path . '/assets/js/ion.js');
		}

		if ($include_css) {
			Asset::getInstance()->addCss($instance->module_relative_path . '/assets/css/ion.css');
		}
	}

	public static function onEpilog(): void
	{
		$instance = self::getInstance();
	}

	public static function onAfterEpilog(): void
	{
		$instance = self::getInstance();
	}
}
