<?php

namespace pinpie\pinpie\Cachers\tests\units;

use atoum;
use mageekguy\atoum\php\tokenizer\iterator\value;

class APC extends atoum {

  public function test() {
    /** @var \pinpie\pinpie\PP $pp */
    $pp = new \mock\pinpie\pinpie\PP();
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