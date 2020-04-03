<?php

declare(strict_types=1);

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


	public function __construct(ValueValidator $validator = null)
	{
		$this->validator = $validator ?: new ValueValidator;
	}


	public function getValidator(): ValueValidator
	{
		return $this->validator;
	}


	/**
	 * @param callable $logger  function(MethodCall|null $call, IMethodResponse|null $response, Exception|null $e)
	 */
	public function addLogger(callable $logger): void
	{
		$this->loggers[] = $logger;
	}


	/**
	 * @param  string $methodName
	 * @param  array $validationRules
	 * @param  callable $handler  function (array $receivedParameters)
	 * @return $this
	 */
	public function registerHandler(string $methodName, array $validationRules, callable $handler): self
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
	 * @param  callable $handler  function (array $receivedParameters)
	 * @return $this
	 */
	public function replaceHandler(string $methodName, array $validationRules, callable $handler): self
	{
		$lower = strtolower($methodName);
		$this->methods[$lower] = [
			self::KEY_RULES => $validationRules,
			self::KEY_HANDLER => $handler,
		];

		return $this;
	}


	public function handle(MethodCall $method): IMethodResponse
	{
		$name = $method->getName();
		$lower = strtolower($name);

		$e = null;
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


	public function handleXml(string $xml, int &$responseCode = null, Converter $converter = null): string
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
			$this->log(null, null, $e);
		}

		if ($response instanceof MethodFaultResponse) {
			$responseCode = $response->getCode();
		}

		return $converter->toXml($response);
	}


	protected function log(MethodCall $call = null, IMethodResponse $response = null, Exception $e = null): void
	{
		if ($this->loggers) {
			foreach ($this->loggers as $logger) {
				$logger($call, $response, $e);
			}
		}
	}
}
