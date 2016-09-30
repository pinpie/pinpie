<?php

namespace pinpie\pinpie\Tags\tests\units;

use atoum;

class Constant extends atoum {


  public function test() {
    if (false) {
      $this->testedInstance = new \pinpie\pinpie\Tags\Constant();
    }
    /** @var \pinpie\pinpie\PP $pp */
    $pp = new \mock\pinpie\pinpie\PP();

    $this
      ->assert('Simple')
      ->if($const = 'simple')
      ->and($this->newTestedInstance($pp, [], 'fullname', '=', false, false, 0, $const))
      ->then
      ->string($this->testedInstance->getOutput())->isEqualTo($const)
      ->then;

    $this
      ->assert('Multiline')
      ->if($const = 'one line
      another line')
      ->and($this->newTestedInstance($pp, [], 'fullname', '=', false, false, 0, $const))
      ->then
      ->string($this->testedInstance->getOutput())->isEqualTo($const)
      ->then;

    $this
      ->assert('with placeholder')
      ->if($const = 'this goes to placeholder')
      ->and($placeholder = 'someplaceholder')
      ->and($priority = 101)
      ->and($this->newTestedInstance($pp, [], 'fullname', '=', $placeholder, false, 0, $const, null, $priority))
      ->then
      ->string($this->testedInstance->getOutput())->isEqualTo('')
      ->array($pp->vars)->isEqualTo([$placeholder => [$priority => [$const]]])
      ->then;
  }
}