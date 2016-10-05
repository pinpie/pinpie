<?php

namespace pinpie\pinpie\Tags\tests\units;

use atoum;

class Tag extends atoum {

	public function test() {
		if (false) {
			$this->testedInstance = new \pinpie\pinpie\Tags\Tag();
		}
		$tagsettings = [];
		$fulltag = 'fulltag';
		$type = 'type';
		$placeholder = 'placeholder';
		$template = 'templage';
		$cachetime = 100;
		$fullname = 'fullname';
		$parentTag = null;
		$priority = 10;
		$depth = 20;

		$settings = [
			'file' => false,
			'pinpie' => [
				'cache class' => '\pinpie\pinpie\Cachers\Disabled',
			],
		];

		/* __construct(PP $pinpie, $settings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, Tag $parentTag = null, $priority = 10000, $depth = 0) */

		$this
			->assert('unknown tag output')
			->if($pp = new \pinpie\pinpie\PP($settings))
			->given($this->newTestedInstance($pp, [], 'fulltag', 'type', 'placeholder', 'template', 'cachetime', 'fullname'))
			->then
			->string($this->testedInstance->getOutput())
			->isEmpty()
			->string($this->testedInstance->errors[0])
			->isEqualTo('Unknown tag found. tag:fulltag in typefullname');

		$this
			->assert('Creating tag, no parent')
			->if($pp = new \pinpie\pinpie\PP($settings))
			->given($this->newTestedInstance($pp, $tagsettings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $parentTag, $priority, $depth))
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
			->if($pp = new \pinpie\pinpie\PP($settings))
			->and($params = ['a' => 'aa', 'b' => 'bb'])
			->and($fullnameWithParams = $fullname . '?' . http_build_query($params))
			->given($this->newTestedInstance($pp, $tagsettings, $fulltag, $type, $placeholder, $template, $cachetime, $fullnameWithParams, $parentTag, $priority, $depth))
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
			->if($pp = new \pinpie\pinpie\PP($settings))
			->given($this->newTestedInstance($pp, $tagsettings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $tag, $priority, $depth))
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

