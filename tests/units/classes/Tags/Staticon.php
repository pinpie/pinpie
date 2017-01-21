<?php

namespace pinpie\pinpie\Tags\tests\units;

use atoum;
use mageekguy\atoum\test\data\set;

class Staticon extends atoum {

	public function test() {
		if (false) {
			$this->testedInstance = new \pinpie\pinpie\Tags\Staticon(null, [], 'fulltag', 'type', 'placeholder', 'template', 'cachetime', 'fullname');
		}

		$defaults = [
			'root' => realpath(__DIR__ . '/../../../filetests/statics'),
			'file' => false,
			'pinpie' => [
				'cache class' => '\pinpie\pinpie\Cachers\Disabled',
				'site url' => 'site.com',
			],
			'tags' => [
				'%' => [
					"gzip types" => ["js", "css"],
					"minify types" => ["js", "css"],
					"minify function" => false,
					"dimensions types" => ["img"],
					"dimensions function" => false,
					"draw function" => false,
				],
			],
		];

		$this->function->gzopen = true;
		$this->function->gzwrite = true;


		$this
			->assert('Empty')
			->and($type = '%')
			->and($fullname = 'js=')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($pp = new \pinpie\pinpie\PP($defaults));
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$out = $this->testedInstance->getOutput();
		$this
			->array($this->testedInstance->errors)->size->isEqualTo(1)
			->string($out)->isEqualTo('')
			->then;


		$this
			->assert('Static js')
			->and($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($pp = new \pinpie\pinpie\PP($defaults));
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$out = $this->testedInstance->getOutput();
		$this
			->string($out)
			->startWith('<script type="text/javascript" src="//site.com/js.js?time=')
			->endWith('"></script>')
			->matches('#^<script type="text\/javascript" src="\/\/site\.com\/js\.js\?time=[\d\w]+"><\/script>$#')
			->function('gzwrite')->wasCalled()->once();

		$this
			->assert('Static relative js')
			->and($type = '%')
			->and($fullname = 'js=js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($pp = new \pinpie\pinpie\PP($defaults))
			->and($relurl = 'somepath')
			->and($pp->url->path = $relurl)
			->then($this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname))
			->and($out = $this->testedInstance->getOutput())
			->string($out)->startWith('<script type="text/javascript" src="//site.com/' . $relurl . '/js.js?time=');

		$this
			->assert('Static !js')
			->and($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '!')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($pp = new \pinpie\pinpie\PP($defaults));
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this
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
			->and($pp = new \pinpie\pinpie\PP($defaults));
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this
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
			->and($pp = new \pinpie\pinpie\PP($defaults));
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this
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
			->and($pp = new \pinpie\pinpie\PP($defaults));
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this
			->then
			->if($out = $this->testedInstance->getOutput())
			->and($shash = $this->testedInstance->getStaticHash())
			->and($filename = $this->testedInstance->filename)
			->and($filetime = $this->testedInstance->filetime)
			->string($out)
			->isEqualTo('start ' . $filename . ' //site.com/2xtPK.png ' . $filetime . '  1000 1000 <img src="//site.com/2xtPK.png?time=' . $shash . '" width="1000" height="1000"> end');
	}

	public function test_servers() {

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
				'%' => [
					"gzip types" => ["js", "css"],
					"minify types" => ["js", "css"],
					"minify function" => false,
					"dimensions types" => ["img"],
					"dimensions function" => false,
					"draw function" => false,
				],
			],
		];

		$this
			->assert('No servers')
			->given($type = '%')
			->and($fullname = 'img=/2xtPK.png')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($settings['tags']['%']['servers'] = [])
			->and($pp = new \pinpie\pinpie\PP($settings));
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this->string($this->testedInstance->url)->isEqualTo('//site.com/2xtPK.png');


		$this
			->assert('With servers')
			->given($type = '%')
			->and($fullname = 'img=/2xtPK.png')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($settings['tags']['%']['servers'] = ['server-1.com', 'server-2.com'])
			->and($pp = new \pinpie\pinpie\PP($settings));
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this
			->string($this->testedInstance->url)->contains('2xtPK.png');
		$this->boolean(in_array($this->testedInstance->url, ['//server-1.com/2xtPK.png', '//server-2.com/2xtPK.png']))->isTrue('wrong url with servers');


	}

	public function test_minify() {

		if (false) {
			$this->testedInstance = new \pinpie\pinpie\Tags\Staticon(null, [], 'fulltag', 'type', 'placeholder', 'template', 'cachetime', 'fullname');
		}

		$defaults = [
			'root' => '',
			'file' => false,
			'pinpie' => [
				'cache class' => '\pinpie\pinpie\Cachers\Disabled',
				'site url' => 'site.com',
			],
			'tags' => [
				'%' => [
					"gzip types" => ["js", "css"],
					"minify types" => ["js", "css"],
					"minify function" => function ($tag) {
						return $tag->minifiedPath;
					},
					"dimensions types" => ["img"],
					"dimensions function" => false,
					"draw function" => false,
					"realpath check" => false,
				],
			],
		];


		$this
			->assert('Minify js');
		$this->function->gzopen = true;
		$this->function->gzwrite = true;
		$this->function->fread = true;
		$this->function->fopen = true;
		$this->function->flock = true;
		$this->function->fclose = true;
		$this->function->file_exists = true;


		$this
			->given($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($settings = $defaults)
			->and($pp = new \mock\pinpie\pinpie\PP($settings));
		$this->calling($pp)->filemtime = function ($a) {
			return 123;
		};
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this->string($this->testedInstance->url)->isEqualTo('//site.com/min.js.js');

		$this
			->given($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($settings = $defaults)
			->and($settings['tags'][$type]['minify types'] = [])
			->and($pp = new \mock\pinpie\pinpie\PP($settings));
		$this->calling($pp)->filemtime = function ($a) {
			return 123;
		};
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this->string($this->testedInstance->url)->isEqualTo('//site.com/js.js');

		$this
			->assert('with servers')
			->given($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($settings = $defaults)
			->and($settings['tags']['%']['servers'] = ['server-1.com', 'server-2.com'])
			->and($pp = new \mock\pinpie\pinpie\PP($settings));
		$this->calling($pp)->filemtime = function ($a) {
			return 123;
		};
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this->string($this->testedInstance->url)->contains('min.js.js');
		$this->boolean(in_array($this->testedInstance->url, ['//server-1.com/min.js.js', '//server-2.com/min.js.js']))->isTrue('wrong url with servers');


		$this
			->assert('falses: no func')
			->given($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($settings = $defaults)
			->and($settings['tags']['%']['minify function'] = false)
			->and($pp = new \mock\pinpie\pinpie\PP($settings));
		$mtime = 100;
		$this->calling($pp)->filemtime = function ($a) use (&$mtime) {
			return --$mtime;
		};
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this->string($this->testedInstance->url)->isEqualTo('//site.com/js.js');
		$this->boolean($this->testedInstance->minifiedURL)->isEqualTo(false);


		$this
			->assert('falses: no min types allowed')
			->given($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($settings = $defaults)
			->and($settings['tags']['%']["minify types"] = [])
			->and($pp = new \mock\pinpie\pinpie\PP($settings));
		$mtime = 100;
		$this->calling($pp)->filemtime = function ($a) use (&$mtime) {
			return --$mtime;
		};
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this->string($this->testedInstance->url)->isEqualTo('//site.com/js.js');
		$this->boolean($this->testedInstance->minifiedURL)->isEqualTo(false);


		$this
			->assert('falses: no file')
			->given($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($settings = $defaults)
			->and($this->function->fopen = false)
			->and($pp = new \mock\pinpie\pinpie\PP($settings));
		$mtime = 100;
		$this->calling($pp)->filemtime = function ($a) use (&$mtime) {
			return --$mtime;
		};
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this->string($this->testedInstance->url)->isEqualTo('//site.com/js.js');
		$this->boolean($this->testedInstance->minifiedURL)->isEqualTo(false);

		$this
			->assert('falses: flock fail')
			->given($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($settings = $defaults)
			->and($this->function->fopen = true)
			->and($this->function->flock = false)
			->and($pp = new \mock\pinpie\pinpie\PP($settings));
		$mtime = 100;
		$this->calling($pp)->filemtime = function ($a) use (&$mtime) {
			return --$mtime;
		};
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this->string($this->testedInstance->url)->isEqualTo('//site.com/js.js');
		$this->boolean($this->testedInstance->minifiedURL)->isEqualTo(false);

		$this
			->assert('falses: flock fail')
			->given($type = '%')
			->and($fullname = 'js=/js.js')
			->and($placeholder = '')
			->and($template = '')
			->and($cachetime = '')
			->and($fulltag = '[' . $placeholder . '[' . $cachetime . $type . $fullname . ']' . $template . ']')
			->and($settings = $defaults)
			->and($this->function->fopen = true)
			->and($this->function->flock = true)
			->and($pp = new \mock\pinpie\pinpie\PP($settings));
		$mtime = 100;
		$this->calling($pp)->filemtime = function ($a) use (&$mtime) {
			return --$mtime;
		};
		$this->newTestedInstance($pp, $pp->conf->tags[$type], $fulltag, $type, $placeholder, $template, $cachetime, $fullname);
		$this->string($this->testedInstance->url)->isEqualTo('//site.com/js.js');
		$this->boolean($this->testedInstance->minifiedURL)->isEqualTo(false);
	}
}