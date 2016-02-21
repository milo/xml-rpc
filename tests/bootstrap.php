<?php

if (!is_file(__DIR__ . '/../vendor/autoload.php')) {
	echo "Tester not found. Install Nette Tester using `composer update`.\n";
	exit(1);
}
include __DIR__ . '/../vendor/autoload.php';


Tester\Environment::setup();
date_default_timezone_set('UTC');


function test(\Closure $cb) {
	$cb();
}
