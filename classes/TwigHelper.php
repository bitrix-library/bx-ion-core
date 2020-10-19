<?php

namespace Ion;

use CBitrixComponent;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
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
	 * @param array $params
	 * @return string
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public static function renderComponent(CBitrixComponent $component, array $params): string
	{
		global $DOCUMENT_ROOT;

		$componentPath = $component->getPath();
		$templateName = $component->getTemplateName();
		$templatePath = $DOCUMENT_ROOT . '/' . $componentPath . '/templates/' . $templateName;

		$loader = new FilesystemLoader($templatePath);
		$twig = new Environment($loader, []);
		$template = $twig->load('template.twig');

		return $template->render($params);
	}

	/**
	 * @param string $string
	 * @param array $params
	 * @return string
	 * @throws LoaderError
	 * @throws SyntaxError
	 */
	public static function renderString(string $string, array $params): string
	{
		$loader = new ArrayLoader();
		$twig = new Environment($loader, []);
		$template = $twig->createTemplate($string);

		return $template->render($params);
	}
}
