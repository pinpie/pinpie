<?php

$runner->addTestsFromDirectory(__DIR__ . '/units');
$script->addDefaultReport();

$cloverWriter = new atoum\writers\file('coverage.xml');
$cloverReport = new atoum\reports\asynchronous\clover();
$cloverReport->addWriter($cloverWriter);
$runner->addReport($cloverReport);
