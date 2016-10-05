<?php

namespace pinpie\pinpie\Tags\tests\units;

use atoum;

class Command extends atoum {


  public function test_unknown() {
    $settings = [
      'file' => false,
      'pinpie' => [
        'cache class' => '\pinpie\pinpie\Cachers\Disabled',
      ],
    ];

    /** @var \pinpie\pinpie\PP $pp */
    $pp = new \mock\pinpie\pinpie\PP($settings);
    $this
      ->assert('Unknown command')
      ->if($pp->parseString('[[@unknowncmd]]'))
      ->then
      ->array($pp->errors)->isEqualTo([1 => [0 => 'Unknown command skipped. tag:[[@unknowncmd]] in @unknowncmd']])
      ->then;
  }

  public function test_template() {
    $settings = [
      'file' => false,
      'pinpie' => [
        'cache class' => '\pinpie\pinpie\Cachers\Disabled',
      ],
    ];

    /** @var \pinpie\pinpie\PP $pp */
    $pp = new \mock\pinpie\pinpie\PP($settings);

    $this
      ->assert('Template')
      ->if($pp->parseString('[[@template=test]]'))
      ->then
      ->variable($pp->page->template)->isEqualTo('test')
      ->array($pp->page->templateParams)->isEqualTo([])
      ->variable($pp->template)->isEqualTo('test')
      ->then;

    $this
      ->assert('Template another')
      ->if($pp->parseString('[[@template=another]]'))
      ->then
      ->variable($pp->page->template)->isEqualTo('another')
      ->array($pp->page->templateParams)->isEqualTo([])
      ->variable($pp->template)->isEqualTo('another')
      ->then;

    $this
      ->assert('Template paramer with params')
      ->if($pp->parseString('[[@template=paramer?foo=one&bar=two]]'))
      ->then
      ->variable($pp->page->template)->isEqualTo('paramer')
      ->array($pp->page->templateParams)->isEqualTo(['foo' => 'one', 'bar' => 'two'])
      ->variable($pp->template)->isEqualTo('paramer')
      ->then;
  }
}