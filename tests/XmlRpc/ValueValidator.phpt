<?php

/**
 * Test: ValueValidator
 *
 * @author  Miloslav Hůla
 */

require __DIR__ . '/../bootstrap.php';


$validator = new Milo\XmlRpc\ValueValidator;

function pass($value, $pattern)
{
	global $validator;
	Assert::same( $validator, $validator->validate($value, $pattern) );
}

function fail($value, $schema, $message = '', $exceptionClass = 'Milo\XmlRpc\InvalidValueException')
{
	global $validator;
	return Assert::exception(function() use ($validator, $value, $schema) {
		$validator->validate($value, $schema, 'testVar');
	}, $exceptionClass, $message );
}

function schemaFail($value, $schema, $message)
{
	return fail($value, $schema, $message, 'Milo\XmlRpc\InvalidSchemaException');
}



# String patterns
pass( array(), '*' );
pass( array('', TRUE, 0), '*[]' );

pass( array(), 'array' );
fail( NULL, 'array' );

pass( array(), '[]' );
fail( NULL, '[]' );

pass( TRUE, 'bool' );
fail( NULL, 'bool' );

pass( function() {}, 'callable' );
fail( NULL, 'callable' );

pass( 0.0, 'float' );
fail( NULL, 'float' );

pass( 0, 'int' );
fail( NULL, 'int' );

pass( 0, 'integer' );
fail( NULL, 'integer' );

pass( NULL, 'null' );
fail( 0, 'null' );

pass( 0, 'numeric' );
fail( NULL, 'numeric' );

pass( (object) NULL, 'object' );
fail( NULL, 'object' );

pass( fopen(__FILE__, 'r'), 'resource' );
fail( NULL, 'object' );

pass( 0, 'scalar' );
fail( array(), 'scalar' );

pass( '', 'string' );
fail( NULL, 'string' );

pass( new DateTime, '\DateTime' );
fail( new stdClass, '\DateTime' );

pass( array(NULL, NULL), 'null[]' );



# Invalid patterns
$e = schemaFail( NULL, 'class', "Invalid pattern 'class'." );
$e = Assert::exception(function() use ($e) {
	throw $e->getPrevious( );
}, 'Milo\XmlRpc\InvalidSchemaException', "Matching to pattern 'class' is not implemented." );
Assert::null( $e->getPrevious() );


$e = schemaFail( NULL, '#foo', "Invalid pattern '#foo'." );
$e = Assert::exception(function() use ($e) {
	throw $e->getPrevious( );
}, 'Milo\XmlRpc\InvalidSchemaException', 'Usertypes are not implemented yet. Send me an issue.' );
Assert::null( $e->getPrevious() );


$e = schemaFail( NULL, NULL, "Invalid pattern ''." );
$e = Assert::exception(function() use ($e) {
	throw $e->getPrevious( );
}, 'Milo\XmlRpc\InvalidSchemaException', 'Pattern must be a string.' );
Assert::null( $e->getPrevious() );



# Patterns by array
fail( NULL, array(), 'Value of testVar must be an array.' );

# Optional vs. required member
pass( array(), array('foo?' => '*') );
fail( array(), array('foo' => '*'), "Missing member 'foo' of testVar value." );

# Other members allowed
pass( array('a'=>'', 'b'=>'', 'c'=>''), array('a'=>'string', '*'=>'*') );
fail( array('a'=>'', 'b'=>'', 'c'=>''), array('a'=>'string'), 'Value of testVar contains not allowed member(s) [b, c].' );

# Array required
pass( array('a'=>array()), array('a'=>'string[]') );
fail( array('a'=>NULL), array('a'=>'string[]'), "Value of testVar[a] does not match to 'string[]'." );

# List (array indexed 0 by one)
fail( array('a'=>''), array('string', 'string'), 'Value of testVar must be a list.' );
fail( array('', '', ''), array('string', 'string'), 'Value of testVar must be a list with 2 items but contains 3.' );



# Complex example
$value = array(
	'name' => 'Miloslav Hůla',
	'born' => new DateTime,
	'languages' => array('cs', 'en', 'php'),
	'address' => array(
		'city' => 'Sin',
		'street' => 'Jump',
		'zip' => NULL,
		'country' => 'Radio',
	),
	'score' => 69,
	'active' => TRUE,
);

$schema = array(
	'name' => 'string',
	'nick?' => 'string',
	'born' => '\DateTime',
	'languages' => 'string[]',
	'address' => array(
		'city' => 'string',
		'street' => 'string',
		'zip' => 'string|null',
		'country?' => 'string',
	),
	'score' => 'int',
	'active' => 'bool',
);

pass( $value, $schema );
