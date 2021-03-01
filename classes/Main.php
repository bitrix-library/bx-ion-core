<?php

namespace Ion;

use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;

/**
 * Class Main
 *
 * @author https://github.com/amensum
 * @package Ion
 */
final class Main extends Singleton
{
    private $module_absolute_path;
    private $module_relative_path;

    public function __construct()
    {
        $this->module_absolute_path = str_replace("\\", "/", dirname(__DIR__ . '\\..\\'));
        $this->module_relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->module_absolute_path);
    }

    public static function onProlog(): void
    {
        $instance = self::getInstance();

        $dev_mode = Settings::getSpaceField("UF_DEV_MODE", "SYSTEM");
        $include_js = Settings::getSpaceField("UF_INCLUDE_JS", "SYSTEM");
        $include_css = Settings::getSpaceField("UF_INCLUDE_CSS", "SYSTEM");
        $include_react = Settings::getSpaceField("UF_INCLUDE_REACT", "SYSTEM");

        if ($include_js) {
            $asset_inst = Asset::getInstance();
            $asset_inst->addJs($instance->module_relative_path . '/assets/js/ion.js');
        }

        if ($include_css) {
            $asset_inst = Asset::getInstance();
            $asset_inst->addCss($instance->module_relative_path . '/assets/css/ion.css');
        }

        if ($include_react) {
            $asset_inst = Asset::getInstance();

            if ($dev_mode) {
                $asset_inst->addString("<script src=\"https://unpkg.com/babel-standalone@6/babel.js\"></script>");
                $asset_inst->addString("<script src=\"https://unpkg.com/react@17/umd/react.development.js\"></script>");
                $asset_inst->addString("<script src=\"https://unpkg.com/react-dom@17/umd/react-dom.development.js\"></script>");
                $asset_inst->addString("<script src=\"https://unpkg.com/react-bootstrap@1.4.0/dist/react-bootstrap.js\"></script>");
            } else {
                $asset_inst->addString("<script src=\"https://unpkg.com/babel-standalone@6/babel.min.js\"></script>");
                $asset_inst->addString("<script src=\"https://unpkg.com/react@17/umd/react.production.min.js\"></script>");
                $asset_inst->addString("<script src=\"https://unpkg.com/react-dom@17/umd/react-dom.production.min.js\"></script>");
                $asset_inst->addString("<script src=\"https://unpkg.com/react-bootstrap@1.4.0/dist/react-bootstrap.min.js\"></script>");
            }
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
