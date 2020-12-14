<?php

namespace Ion;

/**
 * Interface RendererInterface
 * @package Ion
 */
interface RendererInterface
{
	public static function connect(string $relative_dir): void;

	public static function render(string $name, array $params = []): void;
}