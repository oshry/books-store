<?php
namespace Src\Traits;

use Src\Delivery\Exception;

trait Render {

	/**
	 * @var  string  $template  Templates base directory
	 */
	public $template_dir = '';

	/**
	 * Implement render
	 */
	protected function render($template)
	{
		$template = rtrim($this->template_dir, '/')
			.DIRECTORY_SEPARATOR
			.$template
			.'.php';

		if ( ! is_file($template))
			throw new Exception(
				'The requested view :file could not be found',
				[ ':file' => $template ]
			);

		// Import the view variables to local namespace
		$view = $this->view;
		//die($template);

		// Capture the view output
		ob_start();

		try
		{
			// Load the view within the current scope
			include $template;
		}
		catch (Exception $e)
		{
			// Delete the output buffer
			ob_end_clean();

			// Re-throw the exception
			throw $e;
		}
		// Get the captured output and close the buffer
		return ob_get_clean();
	}

}
