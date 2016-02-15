<?php

if (!extension_loaded('dom')) {
	throw new LogicException('PHP extension DOM is missing.');
}

require __DIR__ . '/XmlRpc/exceptions.php';
require __DIR__ . '/XmlRpc/Helpers.php';
require __DIR__ . '/XmlRpc/Sanity.php';
require __DIR__ . '/XmlRpc/IMethod.php';
require __DIR__ . '/XmlRpc/IValueConvertible.php';
require __DIR__ . '/XmlRpc/Base64Value.php';
require __DIR__ . '/XmlRpc/Coder.php';
require __DIR__ . '/XmlRpc/Convertor.php';
require __DIR__ . '/XmlRpc/MethodCall.php';
require __DIR__ . '/XmlRpc/MethodResponse.php';
require __DIR__ . '/XmlRpc/MethodFaultResponse.php';
require __DIR__ . '/XmlRpc/ValueValidator.php';
