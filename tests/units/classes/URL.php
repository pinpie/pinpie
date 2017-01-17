<?php

namespace pinpie\pinpie\tests\units;

use atoum;

class URL extends atoum {
	public function test_index() {

		if (false) {
			$this->testedInstance = new \pinpie\pinpie\URL(null, null);
		}
		$settings = [
			'root' => realpath(__DIR__ . '/../../filetests/pages'),
			'file' => false,
			'pinpie' => [
				'route to parent' => 10,
				'cache class' => '\pinpie\pinpie\Cachers\Disabled',
			]
		];
		$pp = new \pinpie\pinpie\PP($settings);

		$this
			->assert('empty url')
			->and($url = '')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->boolean($this->testedInstance->file)->isEqualTo(false)
			->boolean($this->testedInstance->found)->isEqualTo(false)
			->array($this->testedInstance->foundUrl)->isEqualTo([])
			->array($this->testedInstance->params)->isEqualTo([])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;

		$this
			->assert('404 case')
			->and($url = '/aberafegaea')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->boolean($this->testedInstance->file)->isEqualTo(false)
			->boolean($this->testedInstance->found)->isEqualTo(false)
			->array($this->testedInstance->foundUrl)->isEqualTo([])
			->array($this->testedInstance->params)->isEqualTo(['aberafegaea'])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;

		$this
			->assert('/')
			->and($url = '/')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->string($this->testedInstance->file)->isEqualTo('index.php')
			->boolean($this->testedInstance->found)->isEqualTo(true)
			->array($this->testedInstance->foundUrl)->isEqualTo([])
			->array($this->testedInstance->params)->isEqualTo([])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;

		$this
			->assert('/somepage')
			->and($url = '/somepage')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->string($this->testedInstance->file)->isEqualTo('somepage.php')
			->boolean($this->testedInstance->found)->isEqualTo(true)
			->array($this->testedInstance->foundUrl)->isEqualTo(['somepage'])
			->array($this->testedInstance->params)->isEqualTo([])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;

		$this
			->assert('/somepage/param')
			->and($url = '/somepage/param')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->string($this->testedInstance->file)->isEqualTo('somepage.php')
			->boolean($this->testedInstance->found)->isEqualTo(true)
			->array($this->testedInstance->foundUrl)->isEqualTo(['somepage'])
			->array($this->testedInstance->params)->isEqualTo(['param'])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;

		$this
			->assert('/folder')
			->and($url = '/folder')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->string($this->testedInstance->file)->isEqualTo('folder' . DIRECTORY_SEPARATOR . 'index.php')
			->boolean($this->testedInstance->found)->isEqualTo(true)
			->array($this->testedInstance->foundUrl)->isEqualTo(['folder'])
			->array($this->testedInstance->params)->isEqualTo([])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;

		$this
			->assert('/folder/non/existing/path')
			->and($url = '/folder/non/existing/path')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->string($this->testedInstance->file)->isEqualTo('folder' . DIRECTORY_SEPARATOR . 'index.php')
			->boolean($this->testedInstance->found)->isEqualTo(true)
			->array($this->testedInstance->foundUrl)->isEqualTo(['folder'])
			->array($this->testedInstance->params)->isEqualTo(['non', 'existing', 'path'])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;

		$this
			->assert('/folder/pageinfolder/non/existing/path')
			->and($url = '/folder/pageinfolder/non/existing/path')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->string($this->testedInstance->file)->isEqualTo('folder' . DIRECTORY_SEPARATOR . 'pageinfolder.php')
			->boolean($this->testedInstance->found)->isEqualTo(true)
			->array($this->testedInstance->foundUrl)->isEqualTo(['folder', 'pageinfolder'])
			->array($this->testedInstance->params)->isEqualTo(['non', 'existing', 'path'])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;

		$this
			->assert('/folder/pageinfolder')
			->and($url = '/folder/pageinfolder')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->string($this->testedInstance->file)->isEqualTo('folder/pageinfolder.php')
			->boolean($this->testedInstance->found)->isEqualTo(true)
			->array($this->testedInstance->foundUrl)->isEqualTo(['folder', 'pageinfolder'])
			->array($this->testedInstance->params)->isEqualTo([])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;


		$this
			->assert('http://username:password@hostname:9090/somepage?arg=value#anchor')
			->and($url = 'http://username:password@hostname:9090/somepage?arg=value#anchor')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->string($this->testedInstance->file)->isEqualTo('somepage.php')
			->boolean($this->testedInstance->found)->isEqualTo(true)
			->array($this->testedInstance->foundUrl)->isEqualTo(['somepage'])
			->array($this->testedInstance->params)->isEqualTo([])
			->string($this->testedInstance->path)->isEqualTo('/somepage')
			->string($this->testedInstance->scheme)->isEqualTo('http')
			->string($this->testedInstance->host)->isEqualTo('hostname')
			->integer($this->testedInstance->port)->isEqualTo(9090)
			->string($this->testedInstance->user)->isEqualTo('username')
			->string($this->testedInstance->pass)->isEqualTo('password')
			->string($this->testedInstance->query)->isEqualTo('arg=value')
			->string($this->testedInstance->fragment)->isEqualTo('anchor')
			->then;
	}

	public function test_routetoparent() {

		if (false) {
			$this->testedInstance = new \pinpie\pinpie\URL(null, null);
		}
		$settings = [
			'root' => realpath(__DIR__ . '/../../filetests/pages'),
			'file' => false,
			'pinpie' => [
				'cache class' => '\pinpie\pinpie\Cachers\Disabled',
			]
		];

		$this
			->assert('/folder/pageinfolder/non/existing/path with route to parent = 1')
			->if($settings['pinpie']['route to parent'] = 1)
			->and($pp = new \pinpie\pinpie\PP($settings))
			->and($url = '/folder/pageinfolder/non/existing/path')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->boolean($this->testedInstance->file)->isEqualTo(false)
			->boolean($this->testedInstance->found)->isEqualTo(false)
			->array($this->testedInstance->foundUrl)->isEqualTo([])
			->array($this->testedInstance->params)->isEqualTo(['folder', 'pageinfolder', 'non', 'existing', 'path'])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;

		$this
			->assert('/folder/pageinfolder/non/existing/path with route to parent = 1')
			->if($settings['pinpie']['route to parent'] = 0)
			->and($pp = new \pinpie\pinpie\PP($settings))
			->and($url = '/folder/pageinfolder/non/existing/path')
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->boolean($this->testedInstance->file)->isEqualTo(false)
			->boolean($this->testedInstance->found)->isEqualTo(false)
			->array($this->testedInstance->foundUrl)->isEqualTo([])
			->array($this->testedInstance->params)->isEqualTo(['folder', 'pageinfolder', 'non', 'existing', 'path'])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;
	}

	public function test_altindex() {
		if (false) {
			$this->testedInstance = new \pinpie\pinpie\URL(null, null);
		}


		$settings = [
			'root' => realpath(__DIR__ . '/../../filetests/pages'),
			'file' => false,
			'pinpie' => [
				'route to parent' => 10,
				'cache class' => '\pinpie\pinpie\Cachers\Disabled',
				'index file name' => 'altindex.php',
			],
			'tags' => [
				'PAGE' => [
					'folder' => realpath(__DIR__ . '/../../filetests/pages/altindex'),
				]
			]
		];


		$this
			->assert('path: /')
			->if($url = '/')
			->and($pp = new \pinpie\pinpie\PP($settings))
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->boolean($this->testedInstance->found)->isEqualTo(true)
			->string($this->testedInstance->file)->isEqualTo('altindex.php')
			->array($this->testedInstance->foundUrl)->isEqualTo([])
			->array($this->testedInstance->params)->isEqualTo([])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;


		$this
			->assert('path /folder')
			->if($url = '/folder')
			->and($pp = new \pinpie\pinpie\PP($settings))
			->given($this->newTestedInstance($url, $pp))
			->string($this->testedInstance->url)->isEqualTo($url)
			->boolean($this->testedInstance->found)->isEqualTo(true)
			->string($this->testedInstance->file)->isEqualTo('folder'.DIRECTORY_SEPARATOR.'altindex.php')
			->array($this->testedInstance->foundUrl)->isEqualTo(['folder'])
			->array($this->testedInstance->params)->isEqualTo([])
			->string($this->testedInstance->path)->isEqualTo($url)
			->then;
	}
}