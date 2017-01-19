<?php

$runner->addTestsFromDirectory(__DIR__ . '/units');
$script->addDefaultReport();

$cloverWriter = new atoum\writers\file('coverage.xml');
$cloverReport = new atoum\reports\asynchronous\clover();
$cloverReport->addWriter($cloverWriter);
$runner->addReport($cloverReport);

$script->addDefaultReport();
$telemetry = new mageekguy\atoum\reports\telemetry();
$telemetry->addWriter(new mageekguy\atoum\writers\std\out());
$telemetry->setProjectName('pinpie/pinpie');
$runner->addReport($telemetry);