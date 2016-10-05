<?php

namespace pinpie\pinpie\Cachers\tests\units;

use atoum;

class Cacher extends atoum {

  public function test2() {
    $settings = [
      'file' => false,
      'pinpie' => [
        'cache class' => '\pinpie\pinpie\Cachers\Disabled',
      ],
    ];

    /** @var \pinpie\pinpie\PP $pp */
    $pp = new \mock\pinpie\pinpie\PP($settings);
    $url = ['url path' => '', 'url query' => ''];
    $this->calling($pp)->getHashURL = $url;
    /** @var \pinpie\pinpie\Tags\Tag $tag */
    $tag = new \mock\pinpie\pinpie\Tags\Tag($pp, [], 'fulltag', 'type', 'placeholder', 'template', 'cachetime', 'fullname');
    $_SERVER['SERVER_NAME'] = 'test';
    $tag->settings['random stuff'] = 'abcdefgh';

    $this->given($this->newTestedInstance($pp));

    $this
      ->boolean($this->testedInstance->get($tag))
      ->isFalse();

    $this
      ->boolean($this->testedInstance->set($tag, []))
      ->isTrue();

    $this
      ->array($this->testedInstance->hashBase($tag))
      ->isIdenticalTo([
        $_SERVER['SERVER_NAME'],
        $url['url path'],
        $url['url query'],
        $tag->tagpath,
        $tag->fulltag,
        $tag->childIndex,
        $tag->filetime,
      ]);

    $this
      ->string($this->testedInstance->getHash($tag))
      ->isEqualTo('type.406cadb150a0eeb5e55a37a368e0f1b645ee8373', $this->testedInstance->getHash($tag));
  }
}