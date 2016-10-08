<?php

namespace pinpie\pinpie\tests\units;

use atoum;
use pinpie\pinpie\Tags\Constant;

class CFG extends atoum {


	public function test_config() {
		if (false) {
			$this->testedInstance = new \pinpie\pinpie\CFG();
		}
		$_SERVER['SERVER_NAME'] = 'site.com';
		$cfg = new \pinpie\pinpie\CFG();
		$defaults = $cfg->getDefaults();;

		$this
			->assert('file config')
			->if($_SERVER['SERVER_NAME'] = 'site.com')
			->and($settings = [
				'root' => realpath(__DIR__ . '/../../filetests/config'),
				'file' => true,
			]);
		$this->newTestedInstance($settings);

		$this->string($this->testedInstance->file)->isIdenticalTo($settings['root'] . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $_SERVER['SERVER_NAME'] . '.php');
		$this->boolean($this->testedInstance->debug)->isIdenticalTo(true);
		$this->integer($this->testedInstance->pinpie['route to parent'])->isIdenticalTo(123);
		$this->boolean($this->testedInstance->tags['%']['realpath check'])->isIdenticalTo(false);
		$this->integer($this->testedInstance->tags['%']['gzip level'])->isIdenticalTo(25);
		$this->array($this->testedInstance->tags['%']['gzip types'])->isIdenticalTo(['js']);


		$this
			->assert('file config another')
			->if($_SERVER['SERVER_NAME'] = 'site.com')
			->and($settings['root'] = realpath(__DIR__ . '/../../filetests/config'))
			->and($settings['file'] = $settings['root'] . '/config/another.php')
			->then();
		$this->newTestedInstance($settings);

		$this->string($this->testedInstance->file)->isIdenticalTo($settings['root'] . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'another' . '.php');
		$this->boolean($this->testedInstance->debug)->isIdenticalTo(true);
		$this->integer($this->testedInstance->pinpie['route to parent'])->isIdenticalTo(456);
		$this->boolean($this->testedInstance->tags['%']['realpath check'])->isIdenticalTo(false);
		$this->integer($this->testedInstance->tags['%']['gzip level'])->isIdenticalTo(33);
		$this->array($this->testedInstance->tags['%']['gzip types'])->isIdenticalTo(['css']);

		$this
			->assert('direct config')
			->if($_SERVER['SERVER_NAME'] = 'site.com')
			->and($settings = [
				'root' => realpath(__DIR__ . '/../../filetests/config'),
				'file' => false,
				'pinpie' => [
					'route to parent' => 11,
					'log' => ['show' => 'abc']
				],
				'tags' => [
					'%' => [
						'gzip level' => 29,
						'gzip types' => ['css'],
					],
				],
				'debug' => 54,
			]);
		$this->newTestedInstance($settings);
		$this->boolean($this->testedInstance->file)->isIdenticalTo(false);
		$this->integer($this->testedInstance->debug)->isIdenticalTo($settings['debug']);
		$this->integer($this->testedInstance->pinpie['route to parent'])->isIdenticalTo($settings['pinpie']['route to parent']);
		$this->string($this->testedInstance->pinpie['log']['show'])->isIdenticalTo($settings['pinpie']['log']['show']);
		$this->string($this->testedInstance->pinpie['log']['path'])->isIdenticalTo($defaults['pinpie']['log']['path']);
		$this->boolean($this->testedInstance->pinpie['templates realpath check'])->isIdenticalTo($defaults['pinpie']['templates realpath check']);
		$this->integer($this->testedInstance->tags['%']['gzip level'])->isIdenticalTo(29);
		$this->array($this->testedInstance->tags['%']['gzip types'])->isIdenticalTo(['css']);


		$this
			->assert('direct config with file')
			->if($_SERVER['SERVER_NAME'] = 'site.com')
			->and($settings = [
				'root' => realpath(__DIR__ . '/../../filetests/config'),
				'file' => true,
				'pinpie' => [
					'route to parent' => 22,
					'log' => ['show' => 'abc']
				],
				'tags' => [
					'%' => [
						'gzip types' => ['zzz'],
					],
				],
			]);
		$this->newTestedInstance($settings);
		$this->string($this->testedInstance->file)->isIdenticalTo($settings['root'] . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $_SERVER['SERVER_NAME'] . '.php');
		$this->boolean($this->testedInstance->debug)->isIdenticalTo(true);
		$this->integer($this->testedInstance->pinpie['route to parent'])->isIdenticalTo($settings['pinpie']['route to parent']);
		$this->string($this->testedInstance->pinpie['log']['show'])->isIdenticalTo($settings['pinpie']['log']['show']);
		$this->string($this->testedInstance->pinpie['log']['path'])->isIdenticalTo($defaults['pinpie']['log']['path']);
		$this->boolean($this->testedInstance->pinpie['templates realpath check'])->isIdenticalTo($defaults['pinpie']['templates realpath check']);
		$this->integer($this->testedInstance->tags['%']['gzip level'])->isIdenticalTo(25);
		$this->array($this->testedInstance->tags['%']['gzip types'])->isIdenticalTo(['zzz']);

	}


}