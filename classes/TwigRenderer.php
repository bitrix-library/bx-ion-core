<?php

namespace Ion;

use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\ArrayLoader;

/**
 * Class TwigRenderer
 * @package Ion
 */
final class TwigRenderer extends Singleton implements RendererInterface
{
    private $installed;

    public function __construct()
    {
        $this->installed = array();
    }

    public static function connect(string $relative_dir): void
    {
        $instance = self::getInstance();

        global $DOCUMENT_ROOT;

        $els = scandir($DOCUMENT_ROOT . $relative_dir);

        foreach ($els as $file) {
            $matches = array();
            preg_match("/^(.*)\.(.*)$/", $file, $matches);
            [$full, $name, $ext] = $matches;

            if ($instance->installed[$name] === null && $ext === "twig") {
                $instance->installed[$name] = $DOCUMENT_ROOT . $relative_dir . "/" . $file;
            }
        }
    }

    public static function render(string $name, array $params = []): void
    {
        $instance = self::getInstance();

        $envOptions = array('debug' => true);

        $loader = new ArrayLoader();
        $twig = new Environment($loader, $envOptions);

        $twig->addExtension(new DebugExtension());

        $templateContent = file_get_contents($instance->installed[$name]);

        $template = $twig->createTemplate($templateContent);

        echo $template->render($params);
    }
}
