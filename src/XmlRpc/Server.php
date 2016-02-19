<?php

namespace Milo\XmlRpc;

use Exception;


/**
 * XML-RPC MethodCall handler.
 */
class Server
{
	use Strict;

	/** @internal */
	const
		KEY_RULES = 'rules',
		KEY_HANDLER = 'handler';

	/** @var array */
	private $methods = [];

	/** @var ValueValidator */
	private $validator;

	/** @var callable[] */
	private $loggers = [];


	public function __construct(ValueValidator $validator = NULL)
	{
		$this->validator = $validator ?: new ValueValidator;
	}


	/**
	 * @return ValueValidator
	 */
	public function getValidator()
	{
		return $this->validator;
	}


	/**
	 * @param callable $logger  function(MethodCall|NULL $call, IMethodResponse|NULL $response, Exception|NULL $e)
	 * @return void
	 */
	public function addLogger(callable $logger)
	{
		$this->loggers[] = $logger;
	}


	/**
	 * @param  string $methodName
	 * @param  array $validationRules
	 * @param  callable $handler  function (array $methodParameters)
	 * @return $this
	 */
	public function registerHandler($methodName, array $validationRules, callable $handler)
	{
		$lower = strtolower($methodName);
		if (isset($this->methods[$lower])) {
			throw new LogicException("Method '$methodName' handler is already registered.");
		}

		return $this->replaceHandler($methodName, $validationRules, $handler);
	}


	/**
	 * @param  string $methodName
	 * @param  array $validationRules
	 * @param  callable $handler  function (array $methodParameters)
	 * @return $this
	 */
	public function replaceHandler($methodName, array $validationRules, callable $handler)
	{
		$lower = strtolower($methodName);
		$this->methods[$lower] = [
			self::KEY_RULES => $validationRules,
			self::KEY_HANDLER => $handler,
		];

		return $this;
	}


	/**
	 * @param  MethodCall $method
	 * @return IMethodResponse
	 */
	public function handle(MethodCall $method)
	{
		$name = $method->getName();
		$lower = strtolower($name);

		$e = NULL;
		try {
			if (array_key_exists($lower, $this->methods)) {
				$this->validator->validate($method->getParameters(), $this->methods[$lower][self::KEY_RULES], "$name() args");
				$response = new MethodResponse(call_user_func_array($this->methods[$lower][self::KEY_HANDLER], $method->getParameters()));

			} else {
				$response = new MethodFaultResponse("Method '$name' is not handled.", 400);
			}

		} catch (InvalidValueException $e) {
			$response = new MethodFaultResponse("Method '$name()' parameters are not valid.", 400);

		} catch (FaultResponseException $e) {
			$response = MethodFaultResponse::fromException($e);

		} catch (Exception $e) {
			$response = new MethodFaultResponse('Internal server error occurred.', 500);
		}

		$this->log($method, $response, $e);
		return $response;
	}


	/**
	 * @param  string $xml  input XML
	 * @param  int|NULL $responseCode
	 * @param  Converter|NULL $converter
	 * @return string  output XML
	 */
	public function handleXml($xml, & $responseCode = NULL, Converter $converter = NULL)
	{
		$converter = $converter ?: new Converter;

		try {
			$method = $converter->fromXml($xml);
			if ($method instanceof MethodCall) {
				$response = $this->handle($method);
			} else {
				$response = new MethodFaultResponse("MethodCall expected but got '" . get_class($method) . "'.", 500);
			}

		} catch (Exception $e) {
			$response = MethodFaultResponse::fromException($e);
			$this->log(NULL, NULL, $e);
		}

		if ($response instanceof MethodFaultResponse) {
			$responseCode = $response->getCode();
		}

		return $converter->toXml($response);
	}


	/**
	 * @param  MethodCall|NULL $call
	 * @param  IMethodResponse|NULL $response
	 * @param  Exception|NULL $e
	 */
	protected function log(MethodCall $call = NULL, IMethodResponse $response = NULL, Exception $e = NULL)
	{
		if ($this->loggers) {
			foreach ($this->loggers as $logger) {
				$logger($call, $response, $e);
			}
		}
	}

}
