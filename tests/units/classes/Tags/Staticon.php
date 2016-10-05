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
			'root' => realpath(__DIR__ . '/../../../filetests/statics'),
			'file' => false,
			'pinpie' => [
				'cache class' => '\pinpie\pinpie\Cachers\Disabled',
				'site url' => 'site.com',
			],
			'tags' => [
				"gzip types" => ["js", "css"],
				"minify types" => ["js", "css"],
				"minify function" => false,
				"dimensions types" => ["img"],
				"dimensions function" => false,
				"draw function" => false,
			],
		];


		$pp = new \pinpie\pinpie\PP($settings);

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
			->assert('Static !js')
			->and($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '!')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->given($this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname))
			->then
			->string($this->testedInstance->getOutput())
			->startWith('//site.com/js.js?time=')
			->then();

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

		$this
			->assert('Static img')
			->and($type = '%')
			->and($fullname = 'img=/2xtPK.png')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->given($this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname))
			->then
			->if($out = $this->testedInstance->getOutput())
			->string($out)
			->startWith('<img src="//site.com/2xtPK.png?time=')
			->endWith('" width="1000" height="1000">')
			->matches('#^<img src="\/\/site\.com\/2xtPK.png\?time=[\d\w]+" width="1000" height="1000">$#', 'mismatch: ' . $out)
			->function('gzwrite')->wasCalled()->never();

		$this
			->assert('Static img with template')
			->and($type = '%')
			->and($fullname = 'img=/2xtPK.png')
			->and($placeholder = '')
			->and($template = 'stattemplate')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->given($this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname))
			->then
			->if($out = $this->testedInstance->getOutput())
			->and($shash = $this->testedInstance->getStaticHash())
			->and($filename = $this->testedInstance->filename)
			->and($filetime = $this->testedInstance->filetime)
			->string($out)
			->isEqualTo('start '.$filename . ' //site.com/2xtPK.png ' . $filetime . '  1000 1000 <img src="//site.com/2xtPK.png?time=' . $shash . '" width="1000" height="1000"> end');
	}
}