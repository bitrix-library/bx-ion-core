<?php

namespace Ion;

/**
 * Interface ComponentInterface
 */
interface ComponentInterface {
	/**
	 * ComponentInterface constructor.
	 * @param string $template
	 * @param array $params
	 */
	public function __construct(string $template, array $params);

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function render(array $params): void;

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function getRendered(array $params): string;

	/**
	 * @return array
	 */
	public function getParams(): array;

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function setParams(array $params);

	/**
	 * @return string
	 */
	public function getTemplate(): string;

	/**
	 * @param string $template
	 * @return mixed
	 */
	public function setTemplateFromString(string $template);

	/**
	 * @param string $pathToTemplate
	 * @return mixed
	 */
	public function setTemplateFromFile(string $pathToTemplate);
}