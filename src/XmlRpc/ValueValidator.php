<?php

namespace Milo\XmlRpc;


/**
 * Helper for checking variable structure and content.
 *
 * @author Miloslav Hůla (https://github.com/milo)
 */
class ValueValidator extends Sanity
{
	/** Any member or any type. */
	const ANY = '*';

	/** Array shortcut type. */
	const T_ARRAY = '[]';


	/**
	 * @param  mixed  value to validate
	 * @param  scalar|array  validation schema
	 * @param  string  path to structure
	 * @return self
	 *
	 * @throws InvalidValueException
	 * @throws InvalidSchemaException
	 */
	public function validate($value, $schema, $path = 'root')
	{
		if (is_array($schema)) {
			if (!is_array($value)) {
				throw new InvalidValueException("Value of $path must be an array.");
			}

			$this->validateByArray($value, $schema, $path);

		} else {
			$this->validateByPattern($value, $schema, $path);
		}

		return $this;
	}


	/**
	 * @param  array
	 * @param  array
	 * @param  string  path to structure
	 * @return void
	 *
	 * @throws InvalidValueException
	 * @throws InvalidSchemaException
	 */
	protected function validateByArray(array $value, array $schema, $path)
	{
		if (static::isList($schema) && array_keys($schema) !== array_keys($value)) {
			if (!static::isList($value)) {
				throw new InvalidValueException("Value of $path must be a list.");
			} elseif (count($schema) !== count($value)) {
				throw new InvalidValueException("Value of $path must be a list with " . count($schema) . " items but contains " . count($value) . ".");
			}
		}

		$keys = array();
		$allowOthers = FALSE;
		foreach ($schema as $index => $pattern) {
			if ($index === self::ANY) {
				$allowOthers = TRUE;
				continue;
			}

			$member = $this->parseMember($index);
			if (!array_key_exists($member->name, $value)) {
				if (!$member->isOptional) {
					throw new InvalidValueException("Missing member '$member->name' of $path value.");
				}
				continue;
			}

			$this->validate($value[$member->name], $pattern, "{$path}[$member->name]");
			$keys[] = $member->name;
		}

		if (count($value) !== count($keys)) {
			$diff = array_diff_key(array_keys($value), $keys);
			if (!$allowOthers) {
				throw new InvalidValueException("Value of $path contains not allowed member(s) [" . implode(', ', $diff) . "].");
			}

			foreach ($keys as $key) {
				$this->validate($value[$key], $schema[self::ANY], "{$path}[$key]");
			}
		}
	}


	/**
	 * @param  mixed
	 * @param  string  type pattern
	 * @param  string  path to structure
	 * @return void
	 *
	 * @throws InvalidValueException
	 * @throws InvalidSchemaException
	 */
	protected function validateByPattern($value, $patternStr, $path)
	{
		try {
			foreach ($this->parsePatterns($patternStr) as $pattern) {
				if ($this->match($value, $pattern)) {
					return;
				}
			}

		} catch (InvalidSchemaException $e) {
			throw new InvalidSchemaException("Invalid pattern '$patternStr'.", 0, $e);
		}

		throw new InvalidValueException("Value of $path does not match to '$patternStr'.");
	}


	/**
	 * @param  mixed
	 * @return bool
	 *
	 * @throws InvalidSchemaException
	 */
	protected function match($value, \stdClass $pattern)
	{
		if ($pattern->isArray) {
			if (!is_array($value)) {
				return FALSE;
			}

			$clone = clone $pattern;
			$clone->isArray = FALSE;

			$match = TRUE;
			foreach ($value as $v) {
				$match &= $this->match($v, $clone);
			}

			return $match;
		}

		if ($pattern->type === self::ANY) {
			return TRUE;

		} elseif ($pattern->type === self::T_ARRAY) {
			return is_array($value);

		} elseif ($pattern->isClass) {
			return $value instanceof $pattern->type;

		} elseif ($pattern->isUserType) {
			throw new InvalidSchemaException('Usertypes are not implemented yet. Send me an issue.');
		}

		static $functions = array('array', 'bool', 'callable', 'double', 'float',
			'int', 'integer', 'null', 'numeric', 'object', 'resource', 'scalar',
			'string');

		if (in_array($pattern->type, $functions, TRUE)) {
			return call_user_func("is_$pattern->type", $value);
		}

		throw new InvalidSchemaException("Matching to pattern '$pattern->type' is not implemented.");
	}


	/**
	 * @param  string
	 * @return stdClass
	 */
	protected function parseMember($name)
	{
		$optional = is_string($name) && substr($name, -1) === '?';
		return (object) array(
			'name' => $optional ? substr($name, 0, -1) : $name,
			'isOptional' => $optional,
		);
	}


	/**
	 * @param  string
	 * @return stdClass[]
	 *
	 * @throws InvalidSchemaException
	 */
	protected function parsePatterns($pattern)
	{
		static $cache = array();

		if (!is_string($pattern)) {
			throw new InvalidSchemaException("Pattern must be a string.");
		}

		$patterns = & $cache[$pattern];
		if ($patterns === NULL) {
			$patterns = array();
			foreach (explode('|', $pattern) as $raw) {
				preg_match('~^([#\\\\])?(.*?)(\[])?\z~', $raw, $m);
				$patterns[] = (object) array(
					'type' => $m[2],
					'isArray' => isset($m[3]),
					'isClass' => $m[1] === '\\',
					'isUserType' => $m[1] === '#',
				);
			}
		}

		return $patterns;
	}


	/**
	 * @return bool
	 */
	private static function isList(array $value)
	{
		$length = count($value);
		return $length === 0 || array_keys($value) === range(0, $length - 1);
	}

}
