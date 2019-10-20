<?php
namespace Src\Controller;

use Src\Delivery\Exception;

abstract class HTML extends Base {

	/**
	 * @var  mixed  $view The content View object
	 */
	protected $view;

	/**
	 * @var  string $content Formatted content
	 */
	protected $content = '';

	/**
	 * @var  string  $template Content's template
	 */
	protected $template = NULL;

	/**
	 * Child controllers must use render traits
	 */
	abstract protected function render($template);

	public function after()
	{
		// If content is NULL, no View to render
		if ($this->view === NULL)
		{
			// Verify if response was handled manually
			if ( ! $this->response->body())
				throw new Exception('An empty view can\'t fulfill request');
		}
		elseif (is_object($this->view))
		{
			$this->response->body(
				$this->render($this->template)
			);
		}
		elseif (is_string($this->view))
		{
			$this->response->body($this->view);
		}
	}

}
