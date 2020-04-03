<?php

declare(strict_types=1);

/**
 * Test: Server
 */

use Tester\Assert;
use Milo\XmlRpc\Converter;
use Milo\XmlRpc\FaultResponseException;
use Milo\XmlRpc\MethodCall;
use Milo\XmlRpc\MethodFaultResponse;
use Milo\XmlRpc\MethodResponse;
use Milo\XmlRpc\ValueValidator;
use Milo\XmlRpc\Server;

require __DIR__ . '/../bootstrap.php';

$converter = new Milo\XmlRpc\Converter;


function assertResponse($response, $returnValue)
{
	/** @var MethodResponse $response */
	Assert::type(Milo\XmlRpc\MethodResponse::class, $response);
	Assert::same($returnValue, $response->getReturnValue());
}

function assertFaultResponse($response, $message, $code)
{
	/** @var MethodFaultResponse $response */
	Assert::type(Milo\XmlRpc\MethodFaultResponse::class, $response);
	Assert::same($message, $response->getMessage());
	Assert::same($code, $response->getCode());
}


test(function() {
	$server = new Server;
	Assert::type(Milo\XmlRpc\ValueValidator::class, $server->getValidator());
});


test(function() {
	$validator = new ValueValidator;
	$server = new Server($validator);
	Assert::same($validator, $server->getValidator());
});


test(function() {
	$call = new MethodCall('TeSt.Me', [123]);

	$server = new Server;
	$server->registerHandler('test.Me', ['string'], function () {});
	assertFaultResponse(
		$server->handle($call),
		"Method 'TeSt.Me()' parameters are not valid.",
		400
	);

	Assert::exception(function () use ($server) {
		$server->registerHandler('test.Me', ['string'], function () {});
	}, Milo\XmlRpc\LogicException::class, "Method 'test.Me' handler is already registered.", 0);

	$server->replaceHandler('Test.Me', ['int'], function () { return 'OK'; });
	assertResponse(
		$server->handle($call),
		'OK'
	);
});


test(function() {
	$call = new MethodCall('test');

	$server = new Server;
	$server->registerHandler('test', [], function () {
		throw new FaultResponseException('STOP', 231);
	});

	assertFaultResponse(
		$server->handle($call),
		'STOP',
		231
	);
});


test(function() {
	$call = new MethodCall('test');

	$server = new Server;

	assertFaultResponse(
		$server->handle($call),
		"Method 'test' is not handled.",
		400
	);
});


test(function() {
	$call = new MethodCall('test');

	$server = new Server;
	$server->registerHandler('test', [], function () {
		throw new \Exception('STOP', 231);
	});

	assertFaultResponse(
		$server->handle($call),
		'Internal server error occurred.',
		500
	);
});


test(function() {
	$server = new Server;
	$server->registerHandler('test', [], function () {});

	$converter = new Converter;

	$call = new MethodCall('test');
	Assert::match(
		'%a%<nil/>%a%',
		$server->handleXml($converter->toXml($call))
	);

	$response = new MethodResponse(null);
	Assert::match(
		"%a%MethodCall expected but got 'Milo\\XmlRpc\\MethodResponse'.%a%",
		$server->handleXml($converter->toXml($response))
	);

	$response = new MethodFaultResponse('', 0);
	Assert::match(
		"%a%MethodCall expected but got 'Milo\\XmlRpc\\MethodFaultResponse'.%a%",
		$server->handleXml($converter->toXml($response))
	);

	Assert::match(
		"%a%XML source loading failed.%a%",
		$server->handleXml('No XML')
	);
});


test(function() {
	$c = $r = $e = null;

	$server = new Server;
	$server->addLogger(function ($call, $response, $exception) use (& $c, & $r, & $e) {
		list($c, $r, $e) = [$call, $response, $exception];
	});

	$call = new MethodCall('test');
	$server->handle($call);

	Assert::same($call, $c);
	assertFaultResponse(
		$r,
		"Method 'test' is not handled.",
		400
	);

	Assert::null($e);
});
