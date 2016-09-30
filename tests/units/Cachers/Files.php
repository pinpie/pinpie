<?php

namespace pinpie\pinpie\Cachers\tests\units;

use atoum;
use mageekguy\atoum\php\tokenizer\iterator\value;

class Files extends atoum {

  public function test_General() {
    if (false) {
      /* IDE autocomplete */
      $this->testedInstance = new \pinpie\pinpie\Cachers\Files();
    }

    $data = ['test' => 'data', 'uniqid' => uniqid()];
    $files = [];
    $this->function->is_dir = true;
    $this->function->mkdir = true;
    $this->function->touch = true;
    $this->function->file_exists = true;
    $this->function->file_put_contents = function ($file, $data) use (&$files) {
      $files[$file] = $data;
      return strlen($data);
    };
    $this->function->file_get_contents = function ($file) use (&$files) {
      if (isset($files[$file])) {
        return $files[$file];
      }
      return false;
    };

    /** @var \pinpie\pinpie\PP $pp */
    $pp = new \mock\pinpie\pinpie\PP();
    $url = ['url path' => '', 'url query' => ''];
    $this->calling($pp)->getHashURL = $url;
    /** @var \pinpie\pinpie\Tags\Tag $tag */
    $tag = new \mock\pinpie\pinpie\Tags\Tag($pp, [], 'fulltag', 'type', 'placeholder', 'template', 'cachetime', 'fullname');
    $_SERVER['SERVER_NAME'] = 'test';

    $this
      ->assert('init')
      ->if($this->newTestedInstance($pp, ['random stuff' => 'abcdefgh']))
      ->then
      ->function('is_dir')->wasCalled()->once()
      ->function('touch')->wasCalled()->once();

    $this
      ->assert('write file')
      ->boolean($this->testedInstance->set($tag, $data))
      ->isTrue()
      ->function('touch')->wasCalled()->once()
      ->function('file_put_contents')->wasCalled()->once();

    $this
      ->assert('read file')
      ->array($d = $this->testedInstance->get($tag))
      ->isEqualTo($data, $d)
      ->function('file_exists')->wasCalled()->once()
      ->function('file_get_contents')->wasCalled()->once();


    $this
      ->assert('read failed: file get contents')
      ->given($this->function->file_get_contents = false)
      ->boolean($d = $this->testedInstance->get($tag))
      ->isFalse();

    $this
      ->assert('read failed: file not exists')
      ->given($this->function->file_exists = false)
      ->boolean($d = $this->testedInstance->get($tag))
      ->isFalse();

    $this
      ->assert('write failed: touch')
      ->given($this->function->touch = false)
      ->boolean($d = $this->testedInstance->set($tag, $data))
      ->isFalse();

    $this
      ->assert('creating cache folder')
      ->if($this->function->is_dir = false)
      ->and($this->function->mkdir = true)
      ->and($this->function->touch = false)
      ->given($this->newTestedInstance($pp))
      ->then
      ->function('is_dir')->wasCalled()->once()
      ->function('mkdir')->wasCalled()->once()
      ->function('touch')->wasCalled()->once()
      ->boolean($this->testedInstance->getOK())
      ->isFalse();

    $this
      ->assert('failed to write to cache folder')
      ->if($this->function->is_dir = true)
      ->and($this->function->mkdir = true)
      ->and($this->function->touch = false)
      ->given($this->newTestedInstance($pp))
      ->then
      ->function('is_dir')->wasCalled()->once()
      ->function('touch')->wasCalled()->once()
      ->boolean($this->testedInstance->getOK())
      ->isFalse();


    $this
      ->assert('write file when not ->ok')
      ->boolean($this->testedInstance->set($tag, $data))
      ->isFalse()
      ->function('touch')->wasCalled()->never()
      ->function('file_put_contents')->wasCalled()->never();

    $this
      ->assert('read file when not ->ok')
      ->boolean($d = $this->testedInstance->get($tag))
      ->isFalse()
      ->function('file_exists')->wasCalled()->never()
      ->function('file_get_contents')->wasCalled()->never();
  }

  public function test_Multiple() {
    if (false) {
      /* IDE autocomplete */
      $this->testedInstance = new \pinpie\pinpie\Cachers\Files();
    }
    /** @var \pinpie\pinpie\PP $pp */
    $pp = new \mock\pinpie\pinpie\PP();
    $url = ['url path' => '', 'url query' => ''];
    $this->calling($pp)->getHashURL = $url;
    $_SERVER['SERVER_NAME'] = 'test';

    $files = [];
    $this->function->is_dir = true;
    $this->function->mkdir = true;
    $this->function->touch = true;
    $this->function->file_exists = true;
    $this->function->file_put_contents = function ($file, $data) use (&$files) {
      $files[$file] = $data;
      return strlen($data);
    };
    $this->function->file_get_contents = function ($file) use (&$files) {
      if (isset($files[$file])) {
        return $files[$file];
      }
      return false;
    };
    $this->newTestedInstance($pp, ['random stuff' => 'abcdefgh']);


    $tags = [];
    foreach ([1, 2, 3] as $i) {
      $t = [];
      $t['i'] = $i;
      $t['tag'] = new  \mock\pinpie\pinpie\Tags\Tag($pp, [], 'fulltag' . $i, 'type', 'placeholder', 'template', 'cachetime', 'fullname');
      $t['data'] = ['i' => $i, 'uniqid' => uniqid()];
      $tags[] = $t;
    }

    foreach ($tags as $t) {
      $this
        ->assert('Multiple writes ' . $t['i'])
        ->boolean($this->testedInstance->set($t['tag'], $t['data']))
        ->isTrue()
        ->function('touch')->wasCalled()->once()
        ->function('file_put_contents')->wasCalled()->once();
    }

    foreach ($tags as $t) {
      $this
        ->assert('Multiple reads ' . $t['i'])
        ->array($d = $this->testedInstance->get($t['tag']))
        ->isEqualTo($t['data'], $d)
        ->function('file_exists')->wasCalled()->once()
        ->function('file_get_contents')->wasCalled()->once();
    }

  }
}