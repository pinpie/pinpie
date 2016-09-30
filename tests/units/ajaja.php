<?php

namespace pinpie\pinpie\tests\units;

use atoum;
use pinpie\pinpie\Tags\Constant;

class PP extends atoum {

  public function test_pages() {
    if (false) {
      $this->testedInstance = new \pinpie\pinpie\PP();
    }

    $settings = [
      'root' => realpath(__DIR__ . '/../filetests/pages'),
      'file' => false,
      'pinpie' => [
        'route to parent' => 100,
        'cache class' => '\pinpie\pinpie\Cachers\Disabled',
      ],
    ];

    $this
      ->assert('index page')
      ->if($_SERVER['REQUEST_URI'] = '/');
    $this->newTestedInstance($settings);
    $this->string($this->testedInstance->render())->isEqualTo('index page');

    $this
      ->assert('some page')
      ->if($_SERVER['REQUEST_URI'] = '/somepage');
    $this->newTestedInstance($settings);
    $this->string($this->testedInstance->render())->isEqualTo('some page');

    $this
      ->assert('index page in folder')
      ->if($_SERVER['REQUEST_URI'] = '/folder/');
    $this->newTestedInstance($settings);
    $this->string($this->testedInstance->render())->isEqualTo('index in folder');

    $this
      ->assert('page in folder')
      ->if($_SERVER['REQUEST_URI'] = '/folder/pageinfolder');
    $this->newTestedInstance($settings);
    $this->string($this->testedInstance->render())->isEqualTo('page in folder');

    $this
      ->assert('long url in folder')
      ->if($_SERVER['REQUEST_URI'] = '/somepage/folder/some/long/url');
    $this->newTestedInstance($settings);
    //$this->dump($this->testedInstance->conf->pinpie);
    $this->string($this->testedInstance->render())->isEqualTo('some page');
  }

  public function atest_chunks() {
    if (false) {
      $this->testedInstance = new \pinpie\pinpie\PP();
    }


    $settings = [
      'root' => realpath(__DIR__ . '/../filetests/pages'),
      'file' => false,
      'pinpie' => [
        'route to parent' => 100,
        'cache class' => '\pinpie\pinpie\Cachers\Disabled',
      ],
    ];

    $this
      ->assert('chunk')
      ->given($_SERVER['REQUEST_URI'] = '/one');
    $this->newTestedInstance($settings);
    $this
      ->string($this->testedInstance->render())
      ->isEqualTo('a chunk');

    $this
      ->assert('two chunks')
      ->given($_SERVER['REQUEST_URI'] = '/two');
    $this->newTestedInstance($settings);
    $this
      ->string($this->testedInstance->render())
      ->isEqualTo('a chunk another chunk');

    $this
      ->assert('folder chunk')
      ->given($_SERVER['REQUEST_URI'] = '/folder');
    $this->newTestedInstance($settings);
    $this
      ->string($this->testedInstance->render())
      ->isEqualTo('a folder chunk');

    $this
      ->assert('chunk in chunk')
      ->given($_SERVER['REQUEST_URI'] = '/ch2ch');
    $this->newTestedInstance($settings);
    $this
      ->string($this->testedInstance->render())
      ->isEqualTo('a chunk in another chunk');
  }

  public function test_snippets() {
    if (false) {
      $this->testedInstance = new \pinpie\pinpie\PP();
    }

    $settings = [
      'root' => realpath(__DIR__ . '/../filetests/snippets'),
      'file' => false,
      'pinpie' => [
        'route to parent' => 100,
      ],
    ];

    $this
      ->assert('snippet')
      ->given($_SERVER['REQUEST_URI'] = '/one');
    $this->newTestedInstance($settings);
    $this
      ->string($this->testedInstance->render())
      ->isEqualTo('a snippet');

    $this
      ->assert('two snippets')
      ->given($_SERVER['REQUEST_URI'] = '/two');
    $this->newTestedInstance($settings);
    $this
      ->string($this->testedInstance->render())
      ->isEqualTo('a snippet another snippet');

    $this
      ->assert('folder snippet')
      ->given($_SERVER['REQUEST_URI'] = '/folder');
    $this->newTestedInstance($settings);
    $this
      ->string($this->testedInstance->render())
      ->isEqualTo('a folder snippet');

    $this
      ->assert('snippet in snippet')
      ->given($_SERVER['REQUEST_URI'] = '/sn2sn');
    $this->newTestedInstance($settings);
    $this
      ->string($this->testedInstance->render())
      ->isEqualTo('a snippet in another snippet');
  }

  public function test_tagtemplates() {
    if (false) {
      $this->testedInstance = new \pinpie\pinpie\PP();
    }


    $settings = [
      'root' => realpath(__DIR__ . '/../filetests/pages'),
      'file' => false,
      'pinpie' => [
        'route to parent' => 100,
        'cache class' => '\pinpie\pinpie\Cachers\Disabled',
      ],
    ];

    $this
      ->assert('chunk')
      ->given($_SERVER['REQUEST_URI'] = '/chunk');
    $this->newTestedInstance($settings);
    $this
      ->string($this->testedInstance->render())
      ->isEqualTo('a chunk with template');

    $this
      ->assert('snippet')
      ->given($_SERVER['REQUEST_URI'] = '/snippet');
    $this->newTestedInstance($settings);
    $this
      ->string($this->testedInstance->render())
      ->isEqualTo('a snippet with template');
  }
}