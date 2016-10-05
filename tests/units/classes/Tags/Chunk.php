<?php

namespace pinpie\pinpie\Tags\tests\units;

use atoum;
use pinpie\pinpie\PP;

class Chunk extends atoum {

	public function test() {
		if (false) {
			$this->testedInstance = new \pinpie\pinpie\Tags\Chunk(null, [], 'fulltag', 'type', 'placeholder', 'template', 'cachetime', 'fullname');
		}
		$settings = [
			'root' => realpath(__DIR__ . '/../../../filetests/chunks'),
			'file' => false,
			'pinpie' => [
				'cache class' => '\pinpie\pinpie\Cachers\Disabled',
			],
		];


		$this
			->assert('Chunk')
			->if($pp = new PP($settings))
			->and($type = '')
			->and($fullname = 'chunk')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->given($this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname))
			->then
			->string($this->testedInstance->getOutput())
			->isEqualTo('a chunk')
			->then;

		$this
			->assert('Chunk with template')
			->if($pp = new PP($settings))
			->and($type = '')
			->and($fullname = 'chunk')
			->and($placeholder = '')
			->and($template = 'tagtemplate')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->given($this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname))
			->then
			->string($this->testedInstance->getOutput())
			->isEqualTo('a chunk with template')
			->then;


		$this
			->assert('Chunk with placeholder')
			->if($pp = new PP($settings))
			->and($type = '')
			->and($fullname = 'chunk')
			->and($placeholder = 'plaho')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($parent = null)
			->and($priority = 100)
			->given($this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $parent, $priority))
			->then
			->string($this->testedInstance->getOutput())
			->isEqualTo('')
			->string($pp->vars[$placeholder][$priority][0])
			->isEqualTo('a chunk')
			->then;

		$this
			->assert('Chunk with placeholder with template')
			->if($pp = new PP($settings))
			->and($type = '')
			->and($fullname = 'chunk')
			->and($placeholder = 'plaho')
			->and($template = 'tagtemplate')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($parent = null)
			->and($priority = 100)
			->given($this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $parent, $priority))
			->then
			->string($this->testedInstance->getOutput())
			->isEqualTo('')
			->string($pp->vars[$placeholder][$priority][0])
			->isEqualTo('a chunk with template')
			->then;

		$this
			->assert('Chunk with template with params')
			->if($pp = new PP($settings))
			->and($type = '')
			->and($fullname = 'chunk')
			->and($placeholder = '')
			->and($template = 'tagtemplateparams?foo=fu&bar=bur')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->given($this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname))
			->then
			->string($this->testedInstance->getOutput())
			->isEqualTo('a chunk fubur')
			->then;
	}
}