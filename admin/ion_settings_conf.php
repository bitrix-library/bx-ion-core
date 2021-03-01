<?php
$space_code = "SYSTEM";

$ION_FOOTER = <<< HTML
<div class="collabsible">
    <input id="collapsible" class="toggle" type="checkbox">
    <label for="collapsible" class="lbl-toggle">Документация</label>
    <div class="collapsible-content">
        <div class="content-inner">
            <p>Для указания пространству имени, необходимо создать и заполнить в нем поле UF_NAME.</p>
            <b>Код для получения полей пространства:</b>
            <div class="ion_docblock_code">
                \Ion\Settings::getSpaceField("UF_FIELD", "SPACE");<br>
                \Ion\Settings::getSpaceFields("SPACE");<br>
            </div>

        </div>
    </div>
</div>

<style type="text/css">
    input[type='checkbox'] {
        display: none;
    }

    .collabsible {
        display: inline-block;
        margin: 10px 0;
        border: 1px solid #c172ed;
        border-radius: 2px;
    }

    .lbl-toggle {
        display: block;
        font-size: 14px;
        padding: 10px;
        color: #ffffff;
        background: #c172ed;
        cursor: pointer;
        transition: all 0.25s ease-out;
    }

    .lbl-toggle::after {
        content: '';
        display: inline-block;
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        border-left: 5px solid #ffffff;
        vertical-align: middle;
        margin-left: 10px;
        margin-top: 4px;
        transform: translateY(-2px);
        transition: transform .2s ease-out;
    }

    .toggle:checked + .lbl-toggle::after {
        transform: rotate(90deg) translateX(-3px);
        border-left: 5px solid #c172ed;
    }

    .collapsible-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height .25s ease-in-out;
    }

    .toggle:checked + .lbl-toggle + .collapsible-content {
        max-height: 140px;
        overflow-y: scroll;
    }

    .toggle:checked + .lbl-toggle {
        background: #ffffff;
        color: #c172ed;
        border-bottom: 1px solid #c172ed;
    }

    .collapsible-content .content-inner {
        background: #ffffff;
        padding: 2px 20px;
    }
</style>
HTML;

require(__DIR__ . "/ion_settings_space_view.php");
