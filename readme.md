XML-RPC
=======
This library helps to work with XML-RPC calls and responses. It requires only PHP DOM extension. It is based on word written specification on http://www.xmlrpc.com/.

Simple XML-RPC client/server examples follow.


Client
======
```php
require 'src/xml-rpc.php';

use Milo\XmlRpc;

# Convertor between XML source and PHP classes
$convertor = new XmlRpc\Convertor;

# Method we are calling
$call = new XmlRpc\MethodCall('math.power', array(2, 3));

# Perform request over HTTP
$context = stream_context_create(array(
	'http' => array(
		'method' => 'POST',
		'header' => "Content-type: text/xml",
		'content' => $convertor->toXml($call),
	),
));
$xml = file_get_content('http://example.com', FALSE, $context);

# XML response parsing
$response = $convertor->fromXml($xml);
if (!$response instanceof XmlRpc\MethodResponse) {
	throw new Exception('Expected method response. Got ' . get_class($response));
}

# Returned value
var_dump($response->getReturnValue());
```


Server
======
An example of "echo" server. It just returns array with method name and its parameters which we called.

```php
require 'src/xml-rpc.php';

use Milo\XmlRpc;

# Convertor between XML source and PHP classes
$convertor = new XmlRpc\Convertor;

# Incoming XML
$xml = file_get_contents('php://input');

try {
	$call = $convertor->fromXml($xml);
	if (!$call instanceof XmlRpc\MethodCall) {
		throw new Exception('Expected method call. Got ' . get_class($call));
	}

	# Echo response
	$response = new XmlRpc\MethodResponse(array(
		'youCalled' => $call->getName(),
		'withParameters' => $call->getParameters(),
	));

} catch (Exception $e) {
	# Fault response on error
	$response = XmlRpc\MethodFaultResponse::fromException($e);
}

# Print XML on standard output
echo $convertor->toXml($response);
```


Installation
============
By [Composer](https://getcomposer.org/) `composer require milo/xml-rpc` or download manually and `require 'src/xml-rpc.php';`


License
=======
You may use all files under the terms of the New BSD Licence, or the GNU Public Licence (GPL) version 2 or 3, or the MIT Licence.



Tests
=====
Tests are written for [Nette Tester](https://github.com/nette/tester), composer is required to run them:
```sh
# Download the Tester tool
composer update --dev

# Run the tests
vendor/bin/tester tests
```


------

[![Build Status](https://travis-ci.org/milo/xml-rpc.png?branch=master)](https://travis-ci.org/milo/xml-rpc)
