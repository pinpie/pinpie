<?php

namespace pinpie\pinpie\Tags\tests\units;

use atoum;
use pinpie\pinpie\PP;

class Staticon extends atoum {

	public function test() {
		if (false) {
			$this->testedInstance = new \pinpie\pinpie\Tags\Staticon(null, [], 'fulltag', 'type', 'placeholder', 'template', 'cachetime', 'fullname');
		}

		$settings = [
			'root' => realpath(__DIR__ . '/../../filetests/statics'),
			'file' => false,
			'pinpie' => [
				'cache class' => '\pinpie\pinpie\Cachers\Disabled',
				'site url' => 'site.com',
			],
		];


		$pp = new \mock\pinpie\pinpie\PP($settings);

		$this->function->gzopen = true;
		$this->function->gzwrite = true;

		$this
			->assert('Static js')
			->and($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->given($this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname))
			->then
			->string($this->testedInstance->getOutput())
			->startWith('<script type="text/javascript" src="//site.com/js.js?time=')
			->endWith('"></script>')
			->matches('#^<script type="text\/javascript" src="\/\/site\.com\/js\.js\?time=[\d\w]+"><\/script>$#')
			->function('gzwrite')->wasCalled()->once();

		$this
			->assert('Static css')
			->and($type = '%')
			->and($fullname = 'css=/css.css')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->given($this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname))
			->then
			->if($out = $this->testedInstance->getOutput())
			->string($out)
			->startWith('<link rel="stylesheet" type="text/css" href="//site.com/css.css?time=')
			->endWith('">')
			->matches('#^<link rel="stylesheet" type="text\/css" href="\/\/site\.com\/css\.css\?time=[\d\w]+">$#', 'mismatch: ' . $out)
			->function('gzwrite')->wasCalled()->once();

	}
}