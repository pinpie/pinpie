<?php

$runner->addTestsFromDirectory(__DIR__ . '/units');
$script->addDefaultReport();



$script->addDefaultReport();

$telemetry = new mageekguy\atoum\telemetry\report();
$telemetry->addWriter(new mageekguy\atoum\writers\std\out());
$runner->addReport($telemetry);



$cloverWriter = new atoum\writers\file('coverage.xml');

/*
Generate a clover XML report.
*/
$cloverReport = new atoum\reports\asynchronous\clover();
$cloverReport->addWriter($cloverWriter);

$runner->addReport($cloverReport);