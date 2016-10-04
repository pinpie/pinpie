<?php

namespace pinpie\pinpie\Cachers\tests\units;

use atoum;
use mageekguy\atoum\php\tokenizer\iterator\value;

class APCu extends atoum {

  public function test() {
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

    $data = ['test' => 'data'];

    $this
      ->boolean($this->testedInstance->set($tag, $data))
      ->isTrue();

    $this
      ->array($this->testedInstance->get($tag))
      ->isEqualTo($data);

  }
}