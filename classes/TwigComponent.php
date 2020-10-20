<?php

namespace Ion;

use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

/**
 * Class TwigComponent
 *
 * @author https://github.com/amensum
 * @package Ion
 */
class TwigComponent implements ComponentInterface {
	/**
	 * @var string
	 */
	private $template;

	/**
	 * @var array
	 */
	private $params;

	/**
	 * TwigComponent constructor.
	 * @param string $template
	 * @param array $params
	 */
	public function __construct(string $template = '', array $params = [])
	{
		$this->setTemplateFromString($template);
		$this->setParams($params);
	}

	/**
	 * @param array|null $params
	 * @throws LoaderError
	 * @throws SyntaxError
	 */
	public function render(array $params = null): void
	{
		echo $this->getRendered($params);
	}

	/**
	 * @param array|null $params
	 * @return string
	 * @throws LoaderError
	 * @throws SyntaxError
	 */
	public function getRendered(array $params = null): string
	{
		if ($params === null) {
			return TwigHelper::renderString($this->template, $this->params);
		}

		return TwigHelper::renderString($this->template, $params);
	}

	/**
	 * @return array
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	/**
	 * @param array $params
	 * @return $this
	 */
	public function setParams(array $params): self
	{
		$this->params = $params;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTemplate(): string
	{
		return $this->template;
	}

	/**
	 * @param string $template
	 * @return $this
	 */
	public function setTemplateFromString(string $template): self
	{
		$this->template = $template;

		return $this;
	}

	/**
	 * @param string $pathToTemplate
	 * @return $this
	 */
	public function setTemplateFromFile(string $pathToTemplate): self
	{
		$this->template = file_get_contents($pathToTemplate);

		return $this;
	}
}