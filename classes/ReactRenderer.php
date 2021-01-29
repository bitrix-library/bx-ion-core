<?php

namespace Ion;

use Bitrix\Main\Page\Asset;

/**
 * Class ReactRenderer
 * @package Ion
 */
final class ReactRenderer extends Singleton implements RendererInterface
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

            if ($instance->installed[$name] === null && $ext === "js") {
                $instance->installed[$name] = $DOCUMENT_ROOT . $relative_dir . "/" . $file;

                $asset_inst = Asset::getInstance();
                $asset_inst->addString("<script type=\"text/babel\" src=\"$relative_dir/$file\"></script>");
            }
        }
    }

    public static function render(string $name, array $params = [], string $placeholder = ''): void
    {
        $id = uniqid("react_", false);

        $props = json_encode($params, JSON_THROW_ON_ERROR);

        echo <<< JS
        <div id="$id">
            $placeholder
            <script type="text/babel">
                ReactDOM.render(<$name {...$props}/>, document.querySelector("#$id"));
            </script>
        </div>
        JS;
    }
}