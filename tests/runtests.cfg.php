<?php

$runner->addTestsFromDirectory(__DIR__ . '/units');
$script->addDefaultReport();


if (PHP_MAJOR_VERSION === 5 AND PHP_MINOR_VERSION > 4) {
	$cloverWriter = new atoum\writers\file('coverage.xml');
	$cloverReport = new atoum\reports\asynchronous\clover();
	$cloverReport->addWriter($cloverWriter);
	$runner->addReport($cloverReport);

	$script->addDefaultReport();
	$telemetry = new mageekguy\atoum\reports\telemetry();
	$telemetry->addWriter(new mageekguy\atoum\writers\std\out());
	$telemetry->setProjectName('pinpie/pinpie');
	$runner->addReport($telemetry);
}