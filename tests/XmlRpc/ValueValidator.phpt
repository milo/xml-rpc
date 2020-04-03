<?php

declare(strict_types=1);

/**
 * Test: ValueValidator
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$validator = new Milo\XmlRpc\ValueValidator;

function pass($value, $pattern)
{
	global $validator;
	Assert::same($validator, $validator->validate($value, $pattern));
}

function fail($value, $schema, $message = '', $exceptionClass = 'Milo\XmlRpc\InvalidValueException')
{
	global $validator;
	return Assert::exception(function() use ($validator, $value, $schema) {
		$validator->validate($value, $schema, 'testVar');
	}, $exceptionClass, $message);
}

function schemaFail($value, $schema, $message)
{
	return fail($value, $schema, $message, 'Milo\XmlRpc\InvalidSchemaException');
}



# String patterns
pass([], '*');
pass(['', true, 0], '*[]');

pass([], 'array');
fail(null, 'array');

pass([], '[]');
fail(null, '[]');

pass(true, 'bool');
fail(null, 'bool');

pass(function() {}, 'callable');
fail(null, 'callable');

pass(0.0, 'float');
fail(null, 'float');

pass(0, 'int');
fail(null, 'int');

pass(0, 'integer');
fail(null, 'integer');

pass(null, 'null');
fail(0, 'null');

pass(0, 'numeric');
fail(null, 'numeric');

pass((object) null, 'object');
fail(null, 'object');

pass(fopen(__FILE__, 'r'), 'resource');
fail(null, 'object');

pass(0, 'scalar');
fail([], 'scalar');

pass('', 'string');
fail(null, 'string');

pass(new DateTime, '\DateTime');
fail(new stdClass, '\DateTime');

pass([null, null], 'null[]');



# Invalid patterns
$e = schemaFail(null, 'class', "Invalid pattern 'class'.");
$e = Assert::exception(function() use ($e) {
	throw $e->getPrevious();
}, 'Milo\XmlRpc\InvalidSchemaException', "Matching to pattern 'class' is not implemented.");
Assert::null($e->getPrevious());


$e = schemaFail(null, '#foo', "Invalid pattern '#foo'.");
$e = Assert::exception(function() use ($e) {
	throw $e->getPrevious();
}, 'Milo\XmlRpc\InvalidSchemaException', 'Usertypes are not implemented yet. Send me an issue.');
Assert::null($e->getPrevious());


$e = schemaFail(null, '', "Invalid pattern ''.");
$e = Assert::exception(function() use ($e) {
	throw $e->getPrevious();
}, 'Milo\XmlRpc\InvalidSchemaException', "Matching to pattern '' is not implemented.");
Assert::null($e->getPrevious());



# Patterns by array
fail(null, [], 'Value of testVar must be an array.');

# Optional vs. required member
pass([], ['foo?' => '*']);
fail([], ['foo' => '*'], "Member testVar[foo] is missing.");

# Other members allowed
pass(['a' => '', 'b' => '', 'c' => ''], ['a' => 'string', '*' => '*']);
fail(['a' => '', 'b' => '', 'c' => ''], ['a' => 'string'], 'Not allowed member testVar[b, c].');

# Array required
pass(['a' => []], ['a' => 'string[]']);
fail(['a' => null], ['a' => 'string[]'], "Value of testVar[a] does not match to 'string[]'.");

# List (array indexed 0 by one)
fail(['a' => ''], ['string', 'string'], 'Value of testVar must be a list.');
fail(['', '', ''], ['string', 'string'], 'Value of testVar must be a list with 2 items but contains 3.');



# Complex example
$value = [
	'name' => 'Miloslav Hůla',
	'born' => new DateTime,
	'languages' => ['cs', 'en', 'php'],
	'address' => [
		'city' => 'Sin',
		'street' => 'Jump',
		'zip' => null,
		'country' => 'Radio',
	],
	'score' => 69,
	'active' => true,
];

$schema = [
	'name' => 'string',
	'nick?' => 'string',
	'born' => '\DateTime',
	'languages' => 'string[]',
	'address' => [
		'city' => 'string',
		'street' => 'string',
		'zip' => 'string|null',
		'country?' => 'string',
	],
	'score' => 'int',
	'active' => 'bool',
];

pass($value, $schema);
