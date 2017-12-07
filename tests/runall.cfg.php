<?php

$runner->addTestsFromDirectory(__DIR__ . '/units');
$script->addDefaultReport();

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
