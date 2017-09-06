XML-RPC
=======
This library helps to work with XML-RPC calls and responses. It requires only PHP DOM extension. It is based on word written specification on http://www.xmlrpc.com/.

Simple XML-RPC client and server examples follow.



Client
======
```php
require 'src/xml-rpc.php';

use Milo\XmlRpc;


# Convertor between XML source and PHP classes
$converter = new XmlRpc\Convertor;


# Method we are calling and its arguments
$call = new XmlRpc\MethodCall('math.power', [2, 3]);

# Perform request over HTTP
$context = stream_context_create([
	'http' => array(
		'method' => 'POST',
		'header' => 'Content-type: text/xml',
		'content' => $converter->toXml($call),
	),
]);
$xml = file_get_contents('http://example.com', FALSE, $context);


# XML response parsing
$response = $converter->fromXml($xml);
if (!$response instanceof XmlRpc\MethodResponse) {
	throw new Exception('Expected method response. Got ' . get_class($response));
}

# Returned value
var_dump($response->getReturnValue());
```



Server - manually
=================
An example of `echo` server. It only returns array with method name and its arguments which we called.

```php
require 'src/xml-rpc.php';

use Milo\XmlRpc;


# Converter between XML source and PHP classes
$converter = new XmlRpc\Converter;


# Incoming XML
$xml = file_get_contents('php://input');

try {
	$call = $converter->fromXml($xml);
	if (!$call instanceof XmlRpc\MethodCall) {
		throw new Exception('Expected method call. Got ' . get_class($call));
	}

	# Echo response
	$response = new XmlRpc\MethodResponse([
		'youCalled' => $call->getName(),
		'withParameters' => $call->getParameters(),
	]);

} catch (XmlRpc\RuntimeException $e) {
	# Fault response on error
	$response = XmlRpc\MethodFaultResponse::fromException($e);
}

# Print XML on standard output
echo $converter->toXml($response);
```



Server - automatically
======================
An example of methods handling more automatically than above.

```php
require 'src/xml-rpc.php';

use Milo\XmlRpc;


# Incoming XML
$xml = file_get_contents('php://input');


# Method call handler server
$server = new XmlRpc\Server;
$server->registerHandler(
	'my.method', ['string', 'int', '2?' => 'bool|NULL'],
	function ($string, $int, $bool = NULL) {
		# Throw XmlRpc\FaultResponseException and client will get your error message and code.
		# Throw anything else and client will get fault response with code 500.
		return [...];
	}
);


# Handle XML directly. All exceptions are caught and converted to fault response. 
echo $server->handleXml($xml, $faultCode);  # $faultCode is filled by fault response code



# Or handle MethodCall object.
$converter = new XmlRpc\Converter;

# It may throw exception on invalid XML.
$call = $converter->fromXml($xml);

# All exceptions are caught and converted to fault response.
$response = $server->handle($call);

# Print XML on standard output
echo $converter->toXml($response);



# To log what's happening inside.
$server->addLogger(function (MethodCall $call = NULL, IMethodResponse $response = NULL, \Exception $e = NULL) {
	...
});
```



Installation
============
By [Composer](https://getcomposer.org/) `composer require milo/xml-rpc` or download manually and `require 'src/xml-rpc.php';`



License
=======
You may use all files under the terms of the New BSD Licence, or the GNU Public Licence (GPL) version 2 or 3, or the MIT Licence.



Tests
=====
Tests are written for [Nette Tester](https://tester.nette.org), the Composer is required to run them:
```sh
# Download the Tester
composer update

# Run the tests
vendor/bin/tester tests
```



------
[![Build Status](https://travis-ci.org/milo/xml-rpc.png?branch=master)](https://travis-ci.org/milo/xml-rpc)
