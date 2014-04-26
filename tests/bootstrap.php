<?php

if (!is_file($autoloadFile = __DIR__ . '/../vendor/autoload.php')) {
	echo "Tester not found. Install Nette Tester using `composer update --dev`.\n";
	exit(1);
}
include $autoloadFile;
unset($autoloadFile);


Tester\Environment::setup();
class_alias('Tester\Assert', 'Assert');
date_default_timezone_set('UTC');
