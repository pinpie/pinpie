<?php
/**
 * Created by PhpStorm.
 * User: igors
 * Date: 2016-08-27
 * Time: 21:39
 */

namespace igordata\PinPIE\Tags;

use \igordata\PinPIE\PP as PP;


class Snippet extends Tag {

  public function __construct(PP $pinpie, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, Tag $parentTag = null, $priority, $depth) {
    parent::__construct($pinpie, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $parentTag, $priority, $depth);

    $this->folder = $this->pinpie->conf->pinpie['snippets folder'];
    $this->folderCheck = $this->pinpie->conf->pinpie['snippets realpath check'];
  }

  public function getOutput() {
    $time_start = microtime(true);
    $this->pinpie->totaltagsprocessed++;
    $this->filename = $this->getFilePath();
    $this->pinpie->times['Tag #' . $this->index . ' ' . $this->tagpath . ' started processing'] = microtime(true);
    if (!$this->doChecks()) {
      return '';
    }
    $content = $this->draw();
    $this->time['processing'] = microtime(true) - $time_start;
    $this->pinpie->times['Tag #' . $this->index . ' ' . $this->tagpath . ' finished processing'] = microtime(true);
    return $content;
  }

  protected function doChecks() {
    if (empty($this->filename)) {
      $this->error('File does\'t exist in expected folder');
      return false;
    }
    if (!file_exists($this->filename)) {
      $this->error('File not found at ' . $this->filename);
      return false;
    }
    if ($this->depth > 100) {
      $this->error('Maximum recursion level achieved');
      return false;
    }

    if ($this->pinpie->totaltagsprocessed > 9999) {
      $this->error('Over nine thousands tags processed. It\'s time to stop.');
      return false;
    }
    return true;
  }

  protected function draw() {
    $content = '';
    $this->filetime = filemtime($this->filename);
    if (!$this->cachetime OR $this->cachebleParents) {
      //стоит запрет кеширования или не нужно кешировать, если один из родителей будет закеширован
      $content = $this->render();
    } else {
      //кешируем или читаем из кеша
      $cached = $this->pinpie->cache->get($this);
      //прочекать все файлы вдруг они изменились со времени $cached['time']
      //если вернулся фалс, значит либо нету, либо время у файла новее, либо отвалился кеш =)
      if (
        //не удалось прочесть из кеша или
        $cached === false
        OR
        //время кеширования истекло
        $this->cacheCheckTime($cached) === false
        OR
        //или есть файлы, которые изменились
        $this->cacheCheckFiles($cached) === false
      ) {
        $this->action = 'processed';
        $content = $this->render();
        $this->pinpie->times['Tag #' . $this->index . ' ' . $this->tagpath . ' finished rendering'] = microtime(true);
        // Fresh new content, have to put into the cache.
        // To clean list of files from empty entries I use array_filter(array_keys($this->collectFiles()))].
        if ($this->pinpie->cache->set($this, ['fulltag' => $this->fulltag, 'content' => $content, 'vars' => $this->vars, 'time' => time(), 'files' => array_filter(array_keys($this->collectFiles()))], $this->cachetime)) {
          // SAVED
          $this->action = 'processed and cached';
        } else {
          // CAN'T SAVE
          $this->error('can`t put content to cache');
        }
        $this->pinpie->times['Tag #' . $this->index . ' ' . $this->tagpath . ' finished caching'] = microtime(true);
      } else {
        //обновлять не надо, файл старый, берём из кеша и усё
        $this->action = 'from cache';
        $this->varsInject($cached['vars']);
        $content = $cached['content'];
      }
    }
    return $content;
  }

  protected function getContent() {
    return $this->fileExecute($this->filename, $this->params);
  }


  protected function getFilePath() {
    if (empty($this->folder)) {
      return false;
    }
    $path = $this->folder . DIRECTORY_SEPARATOR . trim($this->name, '\\/') . '.php';
    if ($this->folderCheck) {
      $path = $this->pinpie->checkPathIsInFolder($path, $this->folder);
      if ($path === false) {
        $this->error('File path "' . $path . '" does\'nt belongs to it\'s expected folder "' . $this->folder . '".');
      }
    }
    return $path;
  }

  protected function cacheCheckTime($cached) {
    if (!isset($cached['time'])) {
      return false;
    }
    if ($this->cachetime > 0 AND $this->cachetime < (time() - $cached['time'])) {
      return false;
    }
    return true;
  }

  protected function cacheCheckFiles($cached) {
    foreach ($cached['files'] as $file) {
      $mt = $this->pinpie->filemtime($file);
      if ($mt === false OR $mt > $cached['time'])
        return false;
    }
    return true;
  }

  protected function collectFiles() {
    // filenames are stored as keys to prevent duplicates
    return array_merge($this->files, [$this->filename => true, $this->templateFilename => true]);
  }

}