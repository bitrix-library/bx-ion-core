<?php

namespace Ion;

/**
 * Interface ComponentInterface
 */
interface ComponentInterface
{
	/**
	 * ComponentInterface constructor.
	 * @param string $template
	 * @param array $params
	 */
	public function __construct(string $template, array $params);

	/**
	 * @return string
	 */
	public function __toString(): string;

	/**
	 * @param array $params
	 * @return string
	 */
	public function render(array $params): string;

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
	public function setTemplate(string $template);
}