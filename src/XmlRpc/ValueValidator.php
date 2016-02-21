<?php

namespace Milo\XmlRpc;


/**
 * Helper for checking variable structure and content.
 *
 * @author Miloslav Hůla (https://github.com/milo)
 */
class ValueValidator
{
	use Strict;

	/** Any member or any type. */
	const ANY = '*';

	/** Array shortcut type. */
	const T_ARRAY = '[]';

	/** @var string */
	public $pathPrefix = '[';

	/** @var string */
	public $pathSuffix = ']';


	/**
	 * @param  mixed $value  value to validate
	 * @param  mixed $schema  validation schema
	 * @param  string $path  dumped path to structure when validation fails
	 * @return self
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
	 * @param  array $value
	 * @param  array $schema
	 * @param  string $path
	 * @return void
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

		$keys = [];
		$allowOthers = FALSE;
		foreach ($schema as $index => $pattern) {
			if ($index === self::ANY) {
				$allowOthers = TRUE;
				continue;
			}

			$member = $this->parseMember($index);
			if (!array_key_exists($member->name, $value)) {
				if (!$member->isOptional) {
					throw new InvalidValueException("Member {$path}{$this->pathPrefix}{$member->name}{$this->pathSuffix} is missing.");
				}
				continue;
			}

			$this->validate($value[$member->name], $pattern, "{$path}{$this->pathPrefix}{$member->name}{$this->pathSuffix}");
			$keys[] = $member->name;
		}

		if (count($value) !== count($keys)) {
			if (!$allowOthers) {
				$diff = array_diff(array_keys($value), $keys);
				throw new InvalidValueException("Not allowed member {$path}{$this->pathPrefix}" . implode(', ', $diff) . "{$this->pathSuffix}.");
			}

			foreach ($keys as $key) {
				$this->validate($value[$key], $schema[self::ANY], "{$path}{$this->pathPrefix}{$key}{$this->pathSuffix}");
			}
		}
	}


	/**
	 * @param  mixed $value
	 * @param  string $patternStr  validation pattern
	 * @param  string $path
	 * @return void
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
	 * @param  mixed $value
	 * @param  \stdClass $pattern
	 * @return bool
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

		static $functions = [
			'array', 'bool', 'callable', 'double', 'float', 'int', 'integer',
			'null', 'NULL', 'numeric', 'object', 'resource', 'scalar', 'string'
		];

		if ($pattern->type === self::ANY) {
			return TRUE;

		} elseif ($pattern->type === self::T_ARRAY) {
			return is_array($value);

		} elseif ($pattern->isUserType) {
			throw new InvalidSchemaException('Usertypes are not implemented yet. Send me an issue.');

		} elseif (in_array($pattern->type, $functions, TRUE)) {
			return call_user_func("is_$pattern->type", $value);

		} elseif (class_exists($pattern->type)) {
			return $value instanceof $pattern->type;
		}

		throw new InvalidSchemaException("Matching to pattern '$pattern->type' is not implemented.");
	}


	/**
	 * @param  string $name
	 * @return \stdClass
	 */
	protected function parseMember($name)
	{
		$optional = is_string($name) && substr($name, -1) === '?';
		return (object) [
			'name' => $optional ? substr($name, 0, -1) : $name,
			'isOptional' => $optional,
		];
	}


	/**
	 * @param  string $pattern
	 * @return \stdClass[]
	 * @throws InvalidSchemaException
	 */
	protected function parsePatterns($pattern)
	{
		static $cache = [];

		if (!is_string($pattern)) {
			throw new InvalidSchemaException("Pattern must be a string.");
		}

		$patterns = & $cache[$pattern];
		if ($patterns === NULL) {
			$patterns = [];
			foreach (explode('|', $pattern) as $raw) {
				preg_match('~^(#)?(.*?)(\[])?\z~', $raw, $m);
				$patterns[] = (object) [
					'type' => $m[2],
					'isArray' => isset($m[3]),
					'isUserType' => $m[1] === '#',
				];
			}
		}

		return $patterns;
	}


	/**
	 * @param  array $value
	 * @return bool
	 */
	private static function isList(array $value)
	{
		$length = count($value);
		return $length === 0 || array_keys($value) === range(0, $length - 1);
	}

}
