<?php

namespace pinpie\pinpie\Tags\tests\units;

use atoum;

class Constant extends atoum {


	public function test() {
		if (false) {
			$this->testedInstance = new \pinpie\pinpie\Tags\Constant();
		}
		$settings = [
			'file' => false,
			'pinpie' => [
				'cache class' => '\pinpie\pinpie\Cachers\Disabled',
			],
		];

		/** @var \pinpie\pinpie\PP $pp */
		$pp = new \mock\pinpie\pinpie\PP($settings);

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


	public function test_templatefunction() {
		if (false) {
			$this->testedInstance = new \pinpie\pinpie\Tags\Tag();
		}
		$t = $this;
		$placeholder = false;
		$template = 'some template';
		$content = 'this is a constant content';
		$tfreturn = 'templates function return';

		$settings = [
			'file' => false,
			'pinpie' => [
				'cache class' => '\pinpie\pinpie\Cachers\Disabled',
				'templates function' => function ($tag) use ($t, $tfreturn, $placeholder, $template, $content) {
					$t->string($tag->template)->isEqualTo($template);
					$t->string($tag->content)->isEqualTo($content);
					$t->boolean($tag->placeholder)->isEqualTo($placeholder);
					$t->string($tag->output)->isEqualTo('');
					return $tfreturn;
				}
			],
		];

		$this
			->assert('template function')
			->given($pp = new \pinpie\pinpie\PP($settings))
			->and($this->newTestedInstance($pp, [], 'fulltag', '=', $placeholder, $template, 0, $content))
			->then();
		$out = $this->testedInstance->getOutput();
		$this->string($out)->isEqualTo($tfreturn);

	}
}