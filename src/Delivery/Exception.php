<?php
namespace Src\Delivery;

class Exception extends \Exception {

	/**
	 * @var mixed $log  For logging purposes
	 */
	public static $log;

	/**
	 * @var  array  $php_errors  PHP error codes as human readable names
	 */
	public static $php_errors = [
		E_ERROR              => 'Fatal Error',
		E_USER_ERROR         => 'User Error',
		E_PARSE              => 'Parse Error',
		E_WARNING            => 'Warning',
		E_USER_WARNING       => 'User Warning',
		E_STRICT             => 'Strict',
		E_NOTICE             => 'Notice',
		E_RECOVERABLE_ERROR  => 'Recoverable Error',
		E_DEPRECATED         => 'Deprecated',
	];

	/**
	 * Creates a new exception.
	 *
	 * @param   string          $message    Error message
	 * @param   array           $variables  Parameter variables
	 * @param   integer|string  $code       The exception code
	 * @param   Exception       $previous   Previous exception
	 * @return  void
	 */
	public function __construct($message = "", array $variables = NULL, $code = 0, \Exception $previous = NULL)
	{
		// Set the message
		$message = strtr($message, $variables ?: []);

		// Pass the message and integer code to the parent
		parent::__construct($message, (int) $code, $previous);
		// Save the unmodified code
		// @link http://bugs.php.net/39615
//        die($code);

		$this->code = $code;
		return $message;
	}

	/**
	 * Inline exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * @param   Exception  $e
	 * @return  void
	 */
	public static function handler(\Exception $e)
	{
		$response = self::_handler($e);

		// Send the response to the browser
		echo $response->body();

		exit(1);
	}

	/**
	 * Exception handler, logs the exception and generates a Response object
	 * for display.
	 *
	 * @param   Exception  $e
	 * @return  Response
	 */
	public static function _handler(\Exception $e)
	{
		try
		{
			// Log the exception
			self::log($e);

			// Generate the response
			$response = self::response($e);

			return $response;
		}
		catch (\Exception $e)
		{
			/**
			 * Things are going *really* badly for us, We now have no choice
			 * but to bail. Hard.
			 */
			// Clean the output buffer if one exists
			ob_get_level() AND ob_clean();

			// Set the Status code to 500, and Content-Type to text/plain.
			header('Content-Type: text/plain; charset=utf-8', TRUE, 500);

			echo $e->getMessage();

			exit(1);
		}
	}

	/**
	 * Logs an exception.
	 *
	 * @param   Exception  $e
	 * @param   int        $level
	 * @return  void
	 */
	public static function log(\Exception $e, $level = 0)
	{
		if (is_object(self::$log))
		{
			// Create a text version of the exception
			$error = self::text($e);

			// Add this exception to the log
			self::$log->add($level, $error, NULL, [ 'exception' => $e ]);

			// Make sure the logs are written
			self::$log->write();
		}
	}

	/**
	 * Get a single line of text representing the exception:
	 *
	 * Error [ Code ]: Message ~ File [ Line ]
	 *
	 * @param   Exception  $e
	 * @return  string
	 */
	public static function text(\Exception $e)
	{
		return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
			get_class($e), $e->getCode(), strip_tags($e->getMessage()), $e->getFile(), $e->getLine());
	}

	/**
	 * Get a Response object representing the exception
	 *
	 * @param   Exception  $e
	 * @return  Response
	 */
	public static function response(\Exception $e)
	{
		try
		{
			$output = '<pre>'.self::text($e).'</pre>'
				.'<pre>'.$e->getTraceAsString().'</pre>';

			$response = (new Response)->body($output);
		}
		catch (\Exception $e)
		{
			$response = (new Response)
				->body(self::text($e));
		}

		return $response;
	}

	/**
	 * @var  array  Types of errors to display at shutdown
	 */
	public static $shutdown_errors = [ E_PARSE, E_ERROR, E_USER_ERROR ];

	/**
	 * PHP error handler, converts all errors into ErrorExceptions. This handler
	 * respects error_reporting settings.
	 *
	 * @throws  ErrorException
	 * @return  TRUE
	 */
	public static function error_handler($code, $error, $file = NULL, $line = NULL)
	{

		if (error_reporting() & $code)
		{
			// This error is not suppressed by current error reporting settings
			// Convert the error into an ErrorException
			throw new \ErrorException($error, $code, 0, $file, $line);
		}

		// Do not execute the PHP error handler
		return TRUE;
	}

	/**
	 * Catches errors that are not caught by the error handler, such as E_PARSE.
	 *
	 * @return  void
	 */
	public static function shutdown_handler()
	{
		if ($error = error_get_last() AND in_array($error['type'], self::$shutdown_errors))
		{
			// Clean the output buffer
			ob_get_level() AND ob_clean();

			// Fake an exception for nice debugging
			Exception::handler(new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

			// Shutdown now to avoid a "death loop"
			exit(1);
		}
	}

}
