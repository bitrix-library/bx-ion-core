<?php

namespace Ion;

class ReactHelper
{
	private static $registry;

	public function __construct()
	{
		self::$registry = array();
	}

	public static function registerFolder(string $path): string
	{
		global $DOCUMENT_ROOT;

		$els = scandir($DOCUMENT_ROOT . $path);
		$scripts = "";

		foreach ($els as $file) {
			$file_name = substr($file, 0, -3);
			$file_ext = substr($file, -3);

			if ($file_ext === ".js") {
				self::$registry[$file_name] = array(
					"NAME" => $file_name,
				);

				$src = $path . "/" . $file;

				$scripts .= <<< SCRIPT
				<script type="text/babel" src="$src"></script>
				SCRIPT;
			}
		}

		return $scripts;
	}

	public static function render(string $name, array $params = []): string
	{
		if (self::$registry[$name] !== null) {
			$id = uniqid("react_", false);
			$props = json_encode($params);

			return <<< JS
			<script id="$id" type="text/babel">
			ReactDOM.render(<$name {...$props}/>, document.querySelector("#$id"), () => {
				const parent = document.querySelector("#$id");
				const firstChild = parent.childNodes[0];
				parent.replaceWith(firstChild);
			});
			</script>
			JS;
		}

		return false;
	}
}