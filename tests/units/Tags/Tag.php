<?php

namespace pinpie\pinpie\Tags\tests\units;

use atoum;

class Tag extends atoum {

  public function test() {
    if (false) {
      $this->testedInstance = new \pinpie\pinpie\Tags\Tag();
    }
    $settings = [];
    $fulltag = 'fulltag';
    $type = 'type';
    $placeholder = 'placeholder';
    $template = 'templage';
    $cachetime = 100;
    $fullname = 'fullname';
    $parentTag = null;
    $priority = 10;
    $depth = 20;

    /** @var \pinpie\pinpie\PP $pp */
    $pp = new \mock\pinpie\pinpie\PP();

    /* __construct(PP $pinpie, $settings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, Tag $parentTag = null, $priority = 10000, $depth = 0) */

    $this
      ->assert('Creating tag, no parent')
      ->given($this->newTestedInstance($pp, $settings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $parentTag, $priority, $depth))
      ->then
      ->string($this->testedInstance->fulltag)->isIdenticalTo($fulltag)
      ->string($this->testedInstance->type)->isIdenticalTo($type)
      ->string($this->testedInstance->placeholder)->isIdenticalTo($placeholder)
      ->string($this->testedInstance->template)->isIdenticalTo($template)
      ->integer($this->testedInstance->cachetime)->isIdenticalTo($cachetime)
      ->string($this->testedInstance->fullname)->isIdenticalTo($fullname)
      ->variable($this->testedInstance->parent)->isIdenticalTo($parentTag)
      ->integer($this->testedInstance->priority)->isIdenticalTo($priority)
      ->integer($this->testedInstance->depth)->isIdenticalTo($depth)
      ->then;


    $this
      ->assert('Creating tag with params, no parent')
      ->and($params = ['a' => 'aa', 'b' => 'bb'])
      ->and($fullnameWithParams = $fullname . '?' . http_build_query($params))
      ->given($this->newTestedInstance($pp, $settings, $fulltag, $type, $placeholder, $template, $cachetime, $fullnameWithParams, $parentTag, $priority, $depth))
      ->then
      ->string($this->testedInstance->fulltag)->isIdenticalTo($fulltag)
      ->string($this->testedInstance->type)->isIdenticalTo($type)
      ->string($this->testedInstance->placeholder)->isIdenticalTo($placeholder)
      ->string($this->testedInstance->template)->isIdenticalTo($template)
      ->integer($this->testedInstance->cachetime)->isIdenticalTo($cachetime)
      ->string($this->testedInstance->fullname)->isIdenticalTo($fullnameWithParams)
      ->variable($this->testedInstance->parent)->isIdenticalTo($parentTag)
      ->integer($this->testedInstance->priority)->isIdenticalTo($priority)
      ->integer($this->testedInstance->depth)->isIdenticalTo($depth)
      ->array($this->testedInstance->params)->isIdenticalTo($params)
      ->then;


    /** @var \pinpie\pinpie\Tags\Tag $tag */
    $tag2 = new \mock\pinpie\pinpie\Tags\Tag($pp, [], 'fulltag', 'type', 'placeholder', 'template', 'cachetime', 'fullname');
    $tag = new \mock\pinpie\pinpie\Tags\Tag($pp, [], 'fulltag', 'type', 'placeholder', 'template', 'cachetime', 'fullname', $tag2);

    $this
      ->assert('Creating tag with parent')
      ->given($this->newTestedInstance($pp, $settings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $tag, $priority, $depth))
      ->then
      ->string($this->testedInstance->fulltag)->isIdenticalTo($fulltag)
      ->string($this->testedInstance->type)->isIdenticalTo($type)
      ->string($this->testedInstance->placeholder)->isIdenticalTo($placeholder)
      ->string($this->testedInstance->template)->isIdenticalTo($template)
      ->integer($this->testedInstance->cachetime)->isIdenticalTo($cachetime)
      ->string($this->testedInstance->fullname)->isIdenticalTo($fullname)
      ->variable($this->testedInstance->parent)->isIdenticalTo($tag)
      ->integer($this->testedInstance->priority)->isIdenticalTo($priority)
      ->integer($this->testedInstance->depth)->isIdenticalTo($depth)
      ->then;

  }



}