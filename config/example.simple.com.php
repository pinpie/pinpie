<?php
$random_stuff = 'some random string';
$pinpie['page not found'] = 'notfound.php';
$pinpie['cache']['type'] = 'filecache';

$databases['main'] = [
  'host' => 'localhost',
  'dbname' => 'database',
  'login' => 'login',
  'password' => 'password',
  'port' => 3306,
  'socket' => null,
  'codepage' => 'utf8'
];

//Custom settings accessible through CFG::$conf.
$conf['any settings'] = 'some';