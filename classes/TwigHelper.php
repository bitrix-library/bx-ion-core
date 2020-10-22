<?php

namespace Ion;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;

/**
 * Class TwigHelper
 *
 * @author https://github.com/amensum
 * @package Ion
 */
class TwigHelper
{
	/**
	 * @param string $string
	 * @param array $params
	 * @param array $envOptions
	 * @return string
	 * @throws LoaderError
	 * @throws SyntaxError
	 */
	public static function renderString(string $string, array $params, array $envOptions = []): string
	{
		$loader = new ArrayLoader();
		$twig = new Environment($loader, $envOptions);
		$template = $twig->createTemplate($string);

		return $template->render($params);
	}
}
