<?php

$runner->addTestsFromDirectory(__DIR__ . '/units');
$script->addDefaultReport();


if (PHP_MAJOR_VERSION === 7 OR PHP_MAJOR_VERSION === 5 AND PHP_MINOR_VERSION > 4) {
	/* generate xml report for further analyzes */
	$cloverWriter = new atoum\writers\file('coverage.xml');
	$cloverReport = new atoum\reports\asynchronous\clover();
	$cloverReport->addWriter($cloverWriter);
	$runner->addReport($cloverReport);

	/* send report to Atoum headquarters to make them work even harder */
	$telemetry = new mageekguy\atoum\telemetry\report();
	$telemetry->addWriter(new mageekguy\atoum\writers\std\out());
	$telemetry->setProjectName('pinpie/pinpie');
	$runner->addReport($telemetry);
}



