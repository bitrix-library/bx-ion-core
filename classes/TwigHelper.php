<?php

namespace Ion;

use Exception;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\ArrayLoader;

/**
 * Class TwigHelper
 * @package Ion
 */
class TwigHelper
{
	public static function render(string $string, array $params, array $envOptions = []): string
	{
		$envDefaults = [
			'debug' => true
		];
		$envOptions = array_merge($envDefaults, $envOptions);

		$loader = new ArrayLoader();
		$twig = new Environment($loader, $envOptions);

		$twig->addExtension(new DebugExtension());

		try {
			$template = $twig->createTemplate($string);

			return $template->render($params);
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
}
