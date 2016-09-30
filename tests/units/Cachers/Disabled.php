<?php
namespace pinpie\pinpie\Cachers\tests\units;

use atoum;

class Disabled extends atoum {
  public function test() {
    /** @var \pinpie\pinpie\PP $pp */
    $pp = new \mock\pinpie\pinpie\PP();
    $url = ['url path' => '', 'url query' => ''];
    $this->calling($pp)->getHashURL = $url;
    /** @var \pinpie\pinpie\Tags\Tag $tag */
    $tag = new \mock\pinpie\pinpie\Tags\Tag($pp, [], 'fulltag', 'type', 'placeholder', 'template', 'cachetime', 'fullname');

    $this->given($this->newTestedInstance($pp));

    $this
      ->boolean($this->testedInstance->get($tag))
      ->isFalse();

    $this
      ->boolean($this->testedInstance->set($tag, []))
      ->isTrue();

  }
}