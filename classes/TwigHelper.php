<?php

namespace Ion;

use CBitrixComponent;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

/**
 * Class TwigHelper
 *
 * @author https://github.com/amensum
 * @package Ion
 */
class TwigHelper
{
	/**
	 * @param CBitrixComponent $component
	 * @return string
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public static function render(CBitrixComponent $component): string
	{
		global $DOCUMENT_ROOT;

		$componentPath = $component->getPath();
		$templateName = $component->getTemplateName();
		$templatePath = $DOCUMENT_ROOT . '/' . $componentPath . '/templates/' . $templateName;

		$loader = new FilesystemLoader($templatePath);
		$twig = new Environment($loader, []);
		$template = $twig->load('template.twig');

		return $template->render([
			'arParams' => $component->arParams,
			'arResult' => $component->arResult,
		]);
	}
}
