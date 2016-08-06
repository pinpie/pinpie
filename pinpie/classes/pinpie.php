<?php

use PinPIE\Cache;

class PinPIE {
  public static $currentTag = null;
  private static $depth = 0, $totaltagsprocessed = 0;
  private static $path = [];
  public static
    //Template. Use false to prevent template usage, to output json or any other raw data.
    $template = 'default';
  private static $type_to_execute = [
    '' => false,
    '$' => true,
    '#' => false,
    '@' => false,
    '%' => false,
    '=' => false,
  ];
  /** @var null|\PinPIE\Cache */
  private static $cache = null;
  public static $times = [];
  private static $tags = [];
  private static $errors = [];


  public static $document = null;

  private static $initDone = false;

  public static function Init() {
    if (static::$initDone) {
      return true;
    }
    static::$initDone = true;
    $url = parse_url($_SERVER['REQUEST_URI']);
    static::$document = static::getDocument($url['path']);
    if (static::$document === false) {
      //requested url not found
      http_response_code(404);
      static::$document = trim(CFG::$pinpie['page not found'], DS);
    }
    static::$tags = [
      0 => [
        'index' => 0,
        'depth' => 0,
        'name' => 'PAGE',
        'fulltag' => 'PAGE',
        'parent' => false,
        'parents' => [],
        'type' => '',
        'cacheble parents' => false,
        'cachetime' => false,
        'vars' => [],
        'parent vars' => [],
        'files' => [],
        'children' => [],
        'time' => ['start' => microtime(true), 'end' => (float)0, 'total' => (float)0, 'processing' => (float)0,],
      ],
    ];
    static::$currentTag = 0;
    static::$cache = new \PinPIE\Cache();
    ob_start();
  }

  private static $getDocumentRecur = -1;

  private static function getDocument($url) {
    static::$getDocumentRecur++;
    if (static::$getDocumentRecur > CFG::$pinpie['route to parent']) {
      return false;
    }
    if (empty($url)) {
      return false;
    }
    $doc = false;
    /////////////////////////////////////////////////////////
    if (is_array($url)) {
      $surl = implode(DS, $url);
    } else {
      $url = trim((string)$url, '/');
      $surl = $url;
      $url = explode('/', $url);
    }
    //if $surl is "ololo/ajaja":
    //First step. Look for "/pages/ololo/ajaja.php".
    $path = CFG::$pinpie['pages folder'] . DS . $surl . '.php';

    if (file_exists($path)) {
      /* file found */
      if (CFG::$pinpie['pages realpath check'] AND !static::checkPathIsInFolder($path, CFG::$pinpie['pages folder'])) {
        /* if file was found, but had to check realpath and check failed (file is not in dir where it have to be) */
        return false;
      }
      $doc = $surl . '.php';
    } else {
      //Second step. If it is directory, look for "/pages/ololo/ajaja/index.php".
      $path = CFG::$pinpie['pages folder'] . DS . $surl;
      if (is_dir($path) AND file_exists($path . DS . 'index.php')) {
        if (CFG::$pinpie['pages realpath check'] AND !static::checkPathIsInFolder($path, CFG::$pinpie['pages folder'])) {
          return false;
        }
        $doc = $surl . DS . 'index.php';
      } else {
        //Third step. If CFG::$route_to_parent is set greater than zero, will look for nearest parent. Mean "/pages/ololo/ajaja/index.php" if not exist, goes to"/pages/ololo.php" or "/pages/ololo/index.php". (BUT NOT "/pages/index.php" anyway)
        if (CFG::$pinpie['route to parent'] > 0) {
          unset($url[count($url) - 1]);
          $doc = static::getDocument($url);
        }
      }
    }
    return $doc;
  }

  public static function logit($str = '') {
    if (empty(CFG::$pinpie['log'])) {
      return false;
    }
    if (CFG::$pinpie['log']['show']) {
      echo $str . "<br>\n";
    }
    return file_put_contents(CFG::$pinpie['log']['path'], date('Y.m.d H:i:s') . ' - ' . $str . "\n", FILE_APPEND);
  }


  /**
   * Check that path is really inside that folder, and return path if yes, and FALSE if not.
   * @param String $path Path to check
   * @param String $folder Path to folder, where $path have to be in
   * @return Mixed False on fail, or $path on success
   *
   */
  public static function checkPathIsInFolder($path, $folder) {
    if (!$path OR !$folder) {
      return false;
    }
    $path = str_replace('\\', '/', $path);
    $folder = str_replace('\\', '/', $folder);
    $folderRealpath = realpath($folder);
    $pathRealpath = realpath($path);
    if ($pathRealpath === false OR $folderRealpath === false) {
      return false;
    }
    $folderRealpath = rtrim($folderRealpath, DS) . DS;
    if (strlen($pathRealpath) < $folderRealpath) {
      return false;
    }
    if (substr(rtrim($pathRealpath, DS) . DS, 0, strlen($folderRealpath)) !== $folderRealpath) {
      return false;
    }
    return $path;
  }

  /**
   * @param String $name Template variable name
   * @param String $content Content to put in template variable.
   */
  public static function putVar($name, $content) {
    static::$tags[static::$currentTag]['vars'][0][$name][] = $content;
  }

  private static $hashQuery = false;

  private static function hash(&$tag) {
    $base = [];
    $code = http_response_code();
    $defaults = ['ignore url' => false, 'ignore query params' => false];
    $rules = [];
    $ruleID = 'default';
    if (isset(CFG::$pinpie['cache rules'][$code])) {
      $ruleID = $code;
    }
    if (is_array(CFG::$pinpie['cache rules'][$ruleID])) {
      $rules = array_merge($defaults, CFG::$pinpie['cache rules'][$ruleID]);
    }
    $url = parse_url($_SERVER['REQUEST_URI']);
    if (empty($url)) {
      $url = ['path' => ''];
    }
    $url = array_merge(['query' => ''], $url);
    $base[] = $_SERVER['SERVER_NAME'];
    //Check, if we have to use 'path' part of url, so caching could be done separately for each page
    if (!$rules['ignore url']) {
      $base[] = $url['path'];
    }
    //Should we ignore all (true) or some (array) of get-params of url, or make it separately. Mean cache of "?page=3" differs from "?page=100".
    if ($rules['ignore query params'] !== true) {
      if (is_array($rules['ignore query params'])) {
        if (static::$hashQuery === false) {
          parse_str($url['query'], $url['query']);
          foreach ($rules['ignore query params'] as $p) {
            if (isset($url['query'][$p])) {
              unset($url['query'][$p]);
            }
          }
          $url['query'] = http_build_query($url['query']);
          static::$hashQuery = $url['query'];
        } else {
          $url['query'] = static::$hashQuery;
        }
      }
      $base[] = $url['query'];
    }
    $base[] = $tag['path'];
    $base[] = $tag['fulltag'];
    $base[] = $tag['child index'];
    $base[] = $tag['filetime'];
    $tag['hash base'] = implode("\n", $base); //&$tag, so this goes to $tag
    return hash(CFG::$pinpie['cache hash algo'], $tag['hash base'] . CFG::$random_stuff, true);
  }

  /*  public static function fileRead($filename) {
      if (file_exists($filename)) {
        return file_get_contents($filename);
      }
      return false;
    }*/

  private static function fileExecute($filename, $params = []) {
    static $i = 0;
    $i++;
    static::$times['executing ' . $filename . ' ' . $i] = microtime(true);
    if (is_string($params)) {
      parse_str($params);
    }
    ob_start();
    include $filename;
    static::$times['executed ' . $filename . ' ' . $i] = microtime(true);
    return ob_get_clean();
  }

  private static function getContent(&$tag) {
    if ($tag['executable']) {
      return static::fileExecute($tag['filename'], $tag['params']);
    } else {
      return file_get_contents($tag['filename']);
    }
  }

  private static function error(&$tag, $text) {
    $tag['errors'][] = $text;
    static::$errors[$tag['index']][] = $text;
  }


  private static function getTagFilePath($tag) {
    switch ($tag['type']) {
      case '$':
        $folder = CFG::$pinpie['snippets folder'];
        $check = CFG::$pinpie['snippets realpath check'];
        break;
      case '':
        $folder = CFG::$pinpie['chunks folder'];
        $check = CFG::$pinpie['chunks realpath check'];
        break;
    }
    $path = $folder . DS . trim($tag['name'], '\\/') . '.php';
    if ($check) {
      $path = PinPIE::checkPathIsInFolder($path, $folder);
    }
    return $path;
  }

  private static function processTag(&$tag, $priority) {
    $time_start = microtime(true);
    static::$totaltagsprocessed++;
    $content = '';
    $tag['depth'] = static::$depth;
    $tag['executable'] = static::$type_to_execute[$tag['type']];
    $tag['errors'] = null;
    $tag['filename'] = static::getTagFilePath($tag);
    static::$times['Tag #' . $tag['index'] . ' ' . $tag['path'] . ' started processing'] = microtime(true);
    try {
      throwOnFalse($tag['filename'], 'File does\'t exist in expected folder');
      throwOnFalse(file_exists($tag['filename']), 'File not found at ' . $tag['filename']);
      throwOnTrue(static::$depth > 100, 'Maximum recursion level achieved');
      throwOnTrue(static::$totaltagsprocessed > 9999, 'Over nine thousands tags processed. It\'s time to stop.');
      $tag['filetime'] = filemtime($tag['filename']);
      if (!$tag['cachetime'] OR $tag['cacheble parents']) {
        //стоит запрет кеширования или не нужно кешировать, если один из родителей будет закеширован
        $tag['action'] = 'processed nocache';
        $content = static::renderTag($tag, $priority);
      } else {
        $tag['hash'] = static::hash($tag);
        //кешируем или читаем из кеша
        $cached = static::cacheGet($tag['hash']);
        //прочекать все файлы вдруг они изменились со времени $cached['time']
        //если вернулся фалс, значит либо нету, либо время у файла новее, либо отвалился кеш =)
        if (
          //не удалось прочесть из кеша или
          $cached === false
          OR
          //время кеширования истекло или есть файлы, которые изменились
          static::checkFiles($tag, $cached) === true
        ) {
          $tag['action'] = 'processed';
          $content = static::renderTag($tag, $priority);
          static::$times['Tag #' . $tag['index'] . ' ' . $tag['path'] . ' finished rendering'] = microtime(true);
          //у нас свежий контент, надо положить в кеш.
          //Глобал но кеш
          //почистим список файлов от пустых строк
          //'hash base' => $tag['hash base'],
          if (PinPIE::cacheSet($tag['hash'], ['fulltag' => $tag['fulltag'], 'content' => $content, 'vars' => $tag['vars'], 'time' => time(), 'files' => array_filter(array_keys(static::collectFiles($tag)))])) {
            // echo "[SAVED]";
            $tag['action'] = 'processed and cached';
          } else {
            // echo '[CANT SAVE]';
            static::error($tag, 'can`t put content to cache');
          }
          static::$times['Tag #' . $tag['index'] . ' ' . $tag['path'] . ' finished caching'] = microtime(true);
        } else {
          //обновлять не надо, файл старый, берём из кеша и усё
          $tag['action'] = 'from cache';
          $tag['vars'] = $cached['vars'];
          $content = $cached['content'];
        }
      }
    } catch (Exception $e) {
      static::error($tag, $e->getMessage());
      PinPIE::logit("Tag processing failed
    url: {$_SERVER['REQUEST_URI']}
    tag: {$tag['fulltag']} in {$tag['path']}
    error: " . $e->getMessage());
    }
    $tag['content'] = $content;
    $tag['time']['processing'] = microtime(true) - $time_start;
    static::$times['Tag #' . $tag['index'] . ' ' . $tag['path'] . ' finished processing'] = microtime(true);
    return $content;
  }


  /** Contains filemtime(file)
   * @var array
   */
  private static $filemtimes = [];

  public static function filemtime($file) {
    if (!isset(static::$filemtimes[$file])) {
      /* file_exists() prevents warning */
      if (file_exists($file)) {
        static::$filemtimes[$file] = filemtime($file);
      } else {
        static::$filemtimes[$file] = false;
      }
    }
    return static::$filemtimes[$file];
  }

  private static function checkFiles(&$tag, $cached) {
    if (!isset($cached['time'])) {
      return true;
    }
    if ($tag['cachetime'] > 0 AND $tag['cachetime'] < (time() - $cached['time'])) {
      return true;
    }
    //array_filter(array_keys(static::collectFiles($tag)))
    foreach ($cached['files'] as $file) {
      $mt = static::filemtime($file);
      if ($mt === false OR $mt > $cached['time'])
        return true;
    }
    return false;
  }

  private static function renderTag(&$tag, $priority) {
    $content = static::getContent($tag);
    $content = static::parseTags($content, $tag['index']);
    //Apply template to tag content
    if (!empty($tag['template'])) {
      $tag['vars'][0]['content'][] = $content;
      $content = static::applyTemplate($tag, $priority);
    }
    return $content;
  }

  private static function applyTemplate(&$tag, $priority = 1000) {
    static::$times['Tag #' . $tag['index'] . ' begin parsing template'] = microtime(true);
    $tag['template filename'] = rtrim(CFG::$pinpie['templates folder'], '\\/') . DS . trim($tag['template'], '\\/') . '.php';
    if (CFG::$pinpie['templates realpath check']) {
      $tag['template filename'] = static::checkPathIsInFolder($tag['template filename'], CFG::$pinpie['templates folder']);
    }
    if ($tag['template filename'] !== false AND !is_file($tag['template filename'])) {
      $tag['template filename'] = false;
    }
    if ($tag['template filename'] === false) {
      static::error($tag, 'Template "' . $tag['template'] . '" is not found. Using "[[*content]]" as template.');
      $template = '[[*content]]';
    } else {
      //$template = static::readFile($tag['template filename']); // non executable reading
      $template = static::fileExecute($tag['template filename']); // executable reading
      if ($template === false) {
        static::error($tag, 'Template "' . $tag['template'] . '" filename "' . $tag['template filename'] . '" appears to be empty. Using "[[*content]]" as template.');
        $template = '[[*content]]';
      }
    }
    $template = static::parseTags($template, $tag['index'], $priority);
    if (CFG::$pinpie['template function']) {
      static::$times['Tag #' . $tag['index'] . ' calling external template function'] = microtime(true);
      $f = CFG::$pinpie['template function']; //can't remember how to call a func by name from an array & m too lazy to google it, sorry
      $template = $f($tag, $template);
      static::$times['Tag #' . $tag['index'] . ' finished external template function'] = microtime(true);
    } else {
      $template = static::expandVars($tag, $template);
    }
    static::$times['Tag #' . $tag['index'] . ' finished parsing template'] = microtime(true);
    return $template;
    //return static::parseStatic($template, $tag);
  }

  private static $counterExpandVars = 0;

  private static function expandVars(&$tag, $content, $localVars = []) {
    static::$counterExpandVars++;
    static::$times['Tag #' . $tag['index'] . ' expanding vars ' . static::$counterExpandVars] = microtime(true);
    //have to do this to use vars on the same tag it was created
    if (isset($tag['vars'][0]) AND isset($tag['vars'][0]['content']) AND strpos($content, '[[*content]]') !== false) {
      $content = str_replace('[[*content]]', implode('', $tag['vars'][0]['content']), $content);
      unset($tag['vars'][0]['content']);
    }
    if (empty($tag['local vars'])) {
      $tag['local vars'] = [];
    }
    if (is_array($tag['local vars']) AND is_array($localVars)) {
      $tag['local vars'] = array_merge($tag['local vars'], $localVars);
    }

    $kill = [];
    $depth = 100;
    $content = static::replacePlaceholdersRecursive($content, $tag, $kill, $depth);

    unset($tag['local vars']);
    foreach ($kill as $storage => $priorities) {
      foreach ($priorities as $priority => $vars) {
        foreach ($vars as $var) {
          unset ($tag[$storage][$priority][$var]);
        }
      }
    }
    static::$times['Tag #' . $tag['index'] . ' finished expanding vars ' . static::$counterExpandVars] = microtime(true);
    return $content;
  }

  private static function replacePlaceholdersRecursive($content, &$tag, &$kill, &$depth) {
    $depth--;
    if (!$depth) {
      static::error($tag, 'Max depth reached while calling replacePlaceholdersRecursive()');
      return '';
    }
    $content = preg_replace_callback('/\[\[\*([^\[\]]+)\]\]/',
      function ($matches) use (&$tag, &$kill, &$depth) {
        $r = '';
        if (empty($matches[1])) {
          return $r;
        }
        $placeholder = explode('=', $matches[1], 2);
        if (!empty($placeholder[1])) {
          $r = $placeholder[1];
        }
        $placeholder = $placeholder[0];
        if (CFG::$debug) {
          $r = '[[*' . $placeholder . ']]' . $r;
        }

        foreach (['vars', 'parent vars', 'local vars'] as $storage) {
          if (empty($tag[$storage])) {
            continue;
          }
          ksort($tag[$storage]);
          foreach ($tag[$storage] as $priority => $vars) {
            if (isset($vars[$placeholder])) {
              $r = static::replacePlaceholdersRecursive(implode('', $vars[$placeholder]), $tag, $kill, $depth);
              if (CFG::$pinpie['template clear vars after use']) {
                $kill[$storage][$priority][] = $placeholder;
              }
            }
          }
        }

        return $r;
      }, $content);
    return $content;
  }

  public static $parseStaticCallCounter = 0;

  private static function processStatic(&$tag) {
    static::$parseStaticCallCounter++;
    static::$times['#' . static::$parseStaticCallCounter . ' begin processing static ' . $tag['name'] . ': ' . $tag['value']] = microtime(true);
    $static = \PinPIE\StatiCon::createStatic($tag);
    static::$times['#' . static::$parseStaticCallCounter . ' created static ' . $tag['name'] . ': ' . $tag['value']] = microtime(true);
    $tag['filename'] = $static['file path'];
    if (empty(CFG::$pinpie['static draw function'])) {
      if ($tag['cachetime']) {
        /* exclamation mark AKA cachetime = return path only */
        $tag['content'] = $static['url'] . '?time=' . $static['time hash'];
      } else {
        $tag['content'] = static::drawStatic($static);
      }
      static::$times['#' . static::$parseStaticCallCounter . ' drawn static ' . $tag['name'] . ': ' . $tag['value']] = microtime(true);
    } else {
      $f = CFG::$pinpie['static draw function'];
      static::$times['#' . static::$parseStaticCallCounter . ' calling custom static draw function for ' . $tag['name'] . ': ' . $tag['value']] = microtime(true);
      $tag['content'] = $f($static);
      static::$times['#' . static::$parseStaticCallCounter . ' finished calling custom static draw function for ' . $tag['name'] . ': ' . $tag['value']] = microtime(true);
    }
    if (!empty($tag['template'])) {
      $tag['local vars'][0]['content'][] = $tag['content'];
      if (isset($static['width'])) {
        $tag['local vars'][0]['width'][] = $static['width'];
      }
      if (isset($static['height'])) {
        $tag['local vars'][0]['height'][] = $static['height'];
      }
      $tag['local vars'][0]['file path'][] = $static['file path'];
      $tag['local vars'][0]['time'][] = $static['time'];
      $tag['local vars'][0]['time hash'][] = $static['time hash'];
      $tag['local vars'][0]['url'][] = $static['url'];
      static::$times['#' . static::$parseStaticCallCounter . ' applying template for ' . $tag['name'] . ': ' . $tag['value']] = microtime(true);
      $tag['content'] = static::applyTemplate($tag);
      static::$times['#' . static::$parseStaticCallCounter . ' finished applying template for ' . $tag['name'] . ': ' . $tag['value']] = microtime(true);
    }
    static::$times['#' . static::$parseStaticCallCounter . ' finished processing static ' . $tag['name'] . ': ' . $tag['value']] = microtime(true);
    return $tag['content'];
  }

  private static function drawStatic($static) {
    if ($static['url'] !== false) {
      switch ($static['type']) {
        case 'js':
          return '<script type="text/javascript" src="' . $static['url'] . '?time=' . $static['time hash'] . '"></script>';
        case 'css':
          return '<link rel="stylesheet" type="text/css" href="' . $static['url'] . '?time=' . $static['time hash'] . '">';
        case 'img':
          return '<img src="' . $static['url'] . '?time=' . $static['time hash'] . '"' . (isset($static['width']) ? ' width="' . $static['width'] . '"' : '') . (isset($static['height']) ? ' height="' . $static['height'] . '"' : '') . '>';
      }
    }
    if (CFG::$debug) {
      return $static['fulltag'];
    }
    return '';
  }

  private static function processCommand(&$tag) {
    $r = false;
    switch ($tag['name']) {
      case 'template':
        PinPIE::$template = $tag['value'];
        $r = 'Template set to ' . PinPIE::$template;
        break;
      default :
        PinPIE::logit('Unknown command skipped. tag:' . $tag['fulltag'] . ' in ' . static::getTagPath($tag));
        $tag['skipped'] = true;
    }
    $tag['content'] = $r;
    return $r;
  }

  private static function processConstant(&$tag) {
    $content = $tag['fullname'];
    if (!empty($tag['template'])) {
      $tag['vars'][0]['content'][] = $content;
      $content = static::applyTemplate($tag);
    }
    return $content;
  }

  private static function getTagPath($tag) {
    return '/' . implode('/', array_merge(static::$path, [$tag['type'] . $tag['name']]));
  }

  public static function parseString($content) {
    return static::parseTags($content);
  }

  private static function parseTags($content, $parent = false, $priority = 10000) {
    static::$depth++;
    if ($parent !== false AND isset(static::$tags[$parent])) {
      static::$path[] = static::$tags[$parent]['type'] . static::$tags[$parent]['name'];
    } else {
      $parent = static::$currentTag;
    }
    $content = preg_replace_callback(/** @lang RegExp */
      '/
        \[
        ([^\[\]]*?)
        \[
        ([!\d]*)
        ([@#$%=]?)
        (?!\*)
        ([^\[\]]+?)
        \]
        ([^\[\]]*?)
        \]    
        (\r\n|\n\r|\r|\n)*
      /xsmuS',
      function ($matches) use ($parent, $priority) {
        $matches += ['', '', '', '', '', '', '']; //defaults =) to prevent warning on last (enter)* detector
        return static::createTag($matches, $parent, $priority);
      }
      , $content);
    array_pop(static::$path);
    static::$depth--;
    return $content;
  }

  /**
   * @param $matches
   * @param $parent
   * @param $priority
   * @return bool|mixed|string
   */
  private static function createTag($matches, $parent, $priority) {
    /*
     * Tag with new line after tag
      array (size=8)
        0 => string '[header[!$snippet]template]
      ' (length=28) <-- New line
        1 => string 'header' (length=6) <-- placeholder to put tag output in
        2 => string '!' (length=1) <-- cache forever
        3 => string '$' (length=1) <-- it is snippet
        4 => string 'snippet' (length=7) <-- snippet name
        5 => string 'template' (length=8) <-- template
        6 => string '
      ' (length=1) <-- New line
    */

    $tag = [
      'time' => ['start' => microtime(true), 'end' => (float)0, 'total' => (float)0, 'processing' => (float)0,],
      'fulltag' => $matches[0],
      'cachetime' => 0,
      'cacheble parents' => false,
      'depth' => static::$depth,
      'type' => $matches[3],
      'name' => '',
      'params' => '',
      'value' => '',
      'delayed' => ($matches[1] == '' ? false : $matches[1]),
      'template' => ($matches[5] == '' ? false : $matches[5]),
      'template filename' => '',
      'filename' => '',
      'parent' => $parent,
      'parents' => [],
      'children' => [],
      'vars' => [],
      'parent vars' => [],
      'files' => [], //to prevent duplication, filenames are stored as KEYS
    ];
    if ($matches[2] === '!') {
      $tag['cachetime'] = CFG::$pinpie['cache forever time'];
    } else {
      $tag['cachetime'] = ($matches[2] == '' ? 0 : (int)$matches[2]);
    }

    $params = null;
    $value = null;
    $tag['fullname'] = $matches[4];
    $name = explode('?', $matches[4], 2);
    // extracting params [[tag?params]]
    if (isset($name[1])) {
      $params = $name[1];
    }
    $name = $name[0];
    //extracting direct value [[tag=value]]
    $name = explode('=', $name, 2);
    if (isset($name[1])) {
      $value = $name[1];
    }
    $name = $name[0];

    $tag['name'] = $name;
    $tag['params'] = $params;
    $tag['value'] = $value;
    $tag['index'] = count(static::$tags);
    static::$tags[] = $tag;
    static::$currentTag = $tag['index'];
    $tag =  &static::$tags[$tag['index']];
    $tag['parents'] = static::$tags[$tag['parent']]['parents'];
    $tag['parents'][] = $tag['parent'];
    static::$tags[$tag['parent']]['children'][] = $tag['index'];
    $tag['child index'] = count(static::$tags[$tag['parent']]['children']) - 1;
    $tag['path'] = static::getTagPath($tag);
    if (static::$tags[$tag['parent']]['cacheble parents'] OR static::$tags[$tag['parent']]['cachetime']) {
      $tag['cacheble parents'] = true;
    }
    //if (!$tag['cacheble parents'] AND !$tag['cachetime']) {
    $tag['parent vars'] = static::$tags[$tag['parent']]['vars'];
    //} else {
    //  $tag['vars'] = static::$tags[$tag['parent']]['vars'];
    //
    static::$tags[$tag['parent']]['vars'] = [];
    ///////////
    $r = '';
    switch ($tag['type']) {
      case '':
      case '$':
        $r = static::processTag($tag, $priority);
        break;

      case '#':
        $r = static::processCommand($tag);
        break;

      case '@':
        static::processCommand($tag);
        break;

      case '%':
        /*
        if ($tag['delayed']) {
          //have to cut the template variable name
          $r = "[[{$tag['type']}{$tag['name']}={$tag['value']}" . ($tag['params'] ? '?' . $tag['params'] : '') . "]]";
        } else {
          $r = $tag['fulltag'];
        }
        */
        $r = static::processStatic($tag);
        break;

      case '=':
        $r = static::processConstant($tag);
        break;

      default :
        PinPIE::logit('Unknown tag found. tag:' . $tag['fulltag'] . ' in ' . static::getTagPath($tag));
    }
    if ($r !== '') {
      $r .= $matches[6];
    }
    if ($tag['delayed']) {
      if (!isset($tag['vars'][$priority])) {
        $tag['vars'][$priority] = [];
      }
      if (!isset($tag['vars'][$priority][$tag['delayed']])) {
        $tag['vars'][$priority][$tag['delayed']] = [];
      }
      $tag['vars'][$priority][$tag['delayed']][] = $r;
      $r = '';
    }
    //Transfer all vars and files to parent
    static::transferVars($tag);
    static::transferFiles($tag);
    $tag['time']['end'] = microtime(true);
    $tag['time']['total'] = $tag['time']['end'] - $tag['time']['start'];
    //возвращаемое пишется в текст содержиого тега или страницы.
    return $r;
  }

  private static function transferVars(&$tag) {
    $from = &static::$tags[$tag['index']];
    $to = &static::$tags[$tag['parent']];
    // when tag is created, it receives all its parent vars ('parent vars' key)
    // at this moment parent has no vars at all
    // so we transfer all this tag vars to parent
    $to['vars'] = $from['vars'];
    // and after we transfering all tag's 'parent vars' back to parent
    foreach ($from['parent vars'] as $priority => $vars) {
      if (!isset($to['vars'][$priority])) {
        $to['vars'][$priority] = [];
      }
      $to['vars'][$priority] = array_merge_recursive($vars, $to['vars'][$priority]);
    }
    $from['vars'] = [];
    $from['parent vars'] = [];
  }

  private static function transferFiles(&$tag) {
    $from = &static::$tags[$tag['index']];
    $to = &static::$tags[$tag['parent']];
    $to['files'] = array_merge($to['files'], static::collectFiles($from));
    $from['files'] = [];
  }

  private static function collectFiles(&$tag) {
    // filenames are stored in keys to prevent duplicates
    return array_merge($tag['files'], [$tag['filename'] => true, $tag['template filename'] => true]);
  }

  /**
   * Set current page template. For plain text output PinPIE::templateSet(false) or just PinPIE::templateSet().
   * @param bool|string $template Template filename without extension, stored in /templates folder.
   */
  public static function templateSet($template = false) {
    if (!$template) {
      $template = false;
    }
    static::$template = $template;
  }

  /**
   * Get current page template. False will be returned for plain text output.
   * @return string|bool
   */
  public static function templateGet() {
    return static::$template;
  }

  public static function report() {
    if (!CFG::$debug) {
      return false;
    } else {
      if (!empty(CFG::$pinpie['report password']) AND (!isset($_GET['PINPIEREPORT']) OR $_GET['PINPIEREPORT'] !== CFG::$pinpie['report password'])) {
        return false;
      }
    }
    echo '<hr>';
    echo '$times (ms):<br>';
    echo 'Total: ' . number_format((microtime(true) - PIN_TIME_START) * 1000, 2) . "ms<br>";
    $prev = PIN_TIME_START;
    foreach (static::$times as $key => $value) {
      echo number_format(($value - $prev) * 1000, 2) . " : " . $key . "<br>";
      $prev = $value;
    }
    echo '<br><br>';
    if (empty(static::$errors)) {
      echo "\n\n<br><br>NO ERRORS<br><br>\n\n";
    } else {
      echo 'Errors:<br>';
      var_dump(static::$errors);
    }
    echo '<br><br>';
    echo '$tags:<br>';
    echo '<pre>';
    foreach (static::$tags as $tag) {
      if (empty($tag['time'])) {
        $tag['time'] = ['total' => 0];
      }
      echo str_repeat('  ', $tag['depth']) . $tag['index'] . ' ' . number_format(round($tag['time']['total'] * 1000, 2), 2) . 'ms ' . trim($tag['fulltag'], " \n\r\t") . "\n";
    }
    echo '</pre><br>';
    foreach (static::$tags as &$tag) {
      ksort($tag);
      $tag = ['fulltag' => $tag['fulltag'], 'index' => $tag['index']] + $tag;
    }
    unset($tag);
    echo '<pre>';
    var_dump(static::$tags);
    echo '</pre>';
    return true;
  }

  public static function render() {
    static::$times['Page code executed'] = microtime(true);
    $content = ob_get_clean();
    if (static::$template === false) {
      //can be used for ajax output
      echo $content;
      return true;
    }
    //парсим содержимое страницы
    $content = static::parseTags($content, 0);
    static::$tags[0]['template'] = static::$template;
    static::$tags[0]['vars'][0]['content'][] = $content; //zero for higher priority
    static::$times['page parsed'] = microtime(true);
    $content = static::applyTemplate(static::$tags[0]);

    static::$tags[0]['time']['end'] = microtime(true);
    static::$tags[0]['time']['total'] = static::$tags[0]['time']['end'] - static::$tags[0]['time']['start'];
    static::$times['template applied'] = microtime(true);
    //выводим
    echo $content;
    static::$times['echo $content'] = microtime(true);
    return true;
  }

  public static function cacheGet($hash) {
    return static::$cache->get($hash);
  }

  public static function cacheSet($hash, $data, $time = false) {
    return static::$cache->set($hash, $data, $time);
  }

  public static function injectCacher($cacher) {
    try {
      return static::$cache->injectCacher($cacher);
    } catch (Exception $ex) {

    }
    return false;
  }
}


