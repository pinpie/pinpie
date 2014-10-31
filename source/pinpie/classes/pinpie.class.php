<?php

class PinPIE
{
  private static $currentTag = null;
  private static $depth = 0, $totaltagsprocessed = 0;
  private static $path = [];
  public static
    //Template. Use false to prevent template usage, to output json or any other raw data.
    $template = 'default';
  private static $type_to_execute = [
    '' => false,
    '$' => true
  ];
  private static $type_to_folder = [
    '' => 'chunks',
    '$' => 'snippets'
  ];
  public static $times = [];
  private static $tags = [], $constants = [], $statics = [], $commands = [];
  private static $errors = [];


  public static $document = null;

  public static function Init() {
    $url = parse_url($_SERVER['REQUEST_URI']);
    self::$document = self::getDocument($url['path']);
    if (self::$document === false) {
      //requested url not found
      http_response_code(404);
      self::$document = trim(CFG::$pinpie['page not found'], DS);
    }
    self::$tags = [
      0 => [
        'index' => 0,
        'depth' => 0,
        'name' => 'PAGE',
        'fulltag' => 'PAGE',
        'parent' => false,
        'parents' => [],
        'type' => '',
        'cacheble parents' => false,
        'cache' => false,
        'vars' => [],
        'parent vars' => [],
        'files' => [],
        'children' => [],
      ],
    ];
    self::$currentTag = 0;
    ob_start();
  }

  private static $getDocumentRecur = -1;

  private static function getDocument($url) {
    self::$getDocumentRecur++;
    if (self::$getDocumentRecur > CFG::$pinpie['route to parent']) {
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
      $doc = $surl . '.php';
    } else {
      //Second step. If it is directory, look for "/pages/ololo/ajaja/index.php".
      $path = CFG::$pinpie['pages folder'] . DS . $surl;
      if (is_dir($path) AND file_exists($path . DS . 'index.php')) {
        $doc = $surl . DS . 'index.php';
      } else {
        //Third step. If CFG::$route_to_parent is set greather than zero, will look for nearest parent. Mean "/pages/ololo/ajaja/index.php" if not exist, goes to"/pages/ololo.php" or "/pages/ololo/index.php". (BUT NOT "/pages/index.php" anyway)
        if (CFG::$pinpie['route to parent'] > 0) {
          unset($url[count($url) - 1]);
          $doc = self::getDocument($url);
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
    $folderRealpath = realpath($folder);
    $pathRealath = realpath($path);
    if ($pathRealath === false OR $folderRealpath === false) {
      return false;
    }
    $folderRealpath = rtrim($folderRealpath, DS) . DS;
    if (substr(rtrim($pathRealath, DS) . DS, 0, strlen($folderRealpath)) !== $folderRealpath) {
      return false;
    }
    return $path;
  }

  /**
   * @param $name Template variable name
   * @param $content Content to put in template variable.
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
    $rules = array_merge($defaults, CFG::$pinpie['cache rules'][$ruleID]);
    $url = array_merge(['query' => ''], parse_url($_SERVER['REQUEST_URI']));
    $base[] = $_SERVER['SERVER_NAME'];
    //Should we use path part of url, so caching will be done separately for each page
    if (!$rules['ignore url']) {
      $base[] = $url['path'];
    }
    //Should we ignore all (true) or some (array) get-params of url, or make it separately. Mean cache of "?page=3" differs from "?page=100".
    if ($rules['ignore query params'] !== true) {
      if (is_array($rules['ignore query params'])) {
        if (self::$hashQuery === false) {
          parse_str($url['query'], $url['query']);
          foreach ($rules['ignore query params'] as $p) {
            if (isset($url['query'][$p])) {
              unset($url['query'][$p]);
            }
          }
          $url['query'] = http_build_query($url['query']);
          self::$hashQuery = $url['query'];
        } else {
          $url['query'] = self::$hashQuery;
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

  public static function fileRead($filename) {
    if (file_exists($filename)) {
      return file_get_contents($filename);
    }
    return false;
  }

  private static function fileExecute($filename, $params = []) {
    static $i = 0;
    $i++;
    self::$times['executing ' . $filename . ' ' . $i] = microtime(true);
    if (is_string($params)) {
      parse_str($params);
    }
    ob_start();
    include $filename;
    self::$times['executed ' . $filename . ' ' . $i] = microtime(true);
    return ob_get_clean();
  }

  private static function getContent($tag) {
    if ($tag['executable']) {
      return self::fileExecute($tag['filename'], $tag['params']);
    } else {
      return file_get_contents($tag['filename']);
    }
  }

  private static function error(&$tag, $text) {
    $tag['errors'][] = $text;
    self::$errors[$tag['index']][] = $text;
  }


  private static function getTagFilepath($tag) {
    $folder = ROOT . DS . self::$type_to_folder[$tag['type']];
    $path = $folder . DS . ($tag['name']) . '.php';
    return PinPIE::checkPathIsInFolder($path, $folder);
  }

  private static function processTag(&$tag, $priority) {
    $time_start = microtime(true);
    self::$totaltagsprocessed++;
    $content = '';
    $tag['depth'] = self::$depth;
    $tag['executable'] = self::$type_to_execute[$tag['type']];
    $tag['errors'] = null;
    $tag['filename'] = self::getTagFilepath($tag);
    self::$times['Tag #' . $tag['index'] . ' ' . $tag['path'] . ' started processing'] = microtime(true);
    try {
      ThrowOnFalse($tag['filename'], 'File does\'t exist in expected folder');
      ThrowOnFalse(file_exists($tag['filename']), 'File not found at ' . $tag['filename']);
      ThrowOnTrue(self::$depth > 100, 'Maximum recursion level achieved');
      ThrowOnTrue(self::$totaltagsprocessed > 9999, 'Over nine thousands tags processed. It\'s time to stop.');
      $tag['filetime'] = filemtime($tag['filename']);
      if (!$tag['cache'] OR $tag['cacheble parents']) {
        //стоит запрет кеширования или не нужно кешировать, если один из родителей будет закеширован
        $tag['action'] = 'processed nocache';
        $content = self::renderTag($tag, $priority);
      } else {
        $tag['hash'] = self::hash($tag);
        //кешируем или читаем из кеша
        $cached = Cache::Get($tag['hash']);
        //прочекать все файлы вдруг они изменились со времени $cached['time']
        //если вернулся фалс, значит либо нету, либо время у файла новее, либо отвалился кеш =)
        if (
          //не удалось прочесть из кеша или
          $cached === false
          OR
          //время кеширования истекло или есть файлы, которые изменились
          self::checkFiles($tag, $cached) === true
        ) {
          $tag['action'] = 'processed';
          $content = self::renderTag($tag, $priority);
          self::$times['Tag #' . $tag['index'] . ' ' . $tag['path'] . ' finished rendering'] = microtime(true);
          //у нас свежий контент, надо положить в кеш.
          //Глобал но кеш
          //почистим список файлов от пустых строк
          //'hash base' => $tag['hash base'],
          if (Cache::Set($tag['hash'], ['fulltag' => $tag['fulltag'], 'content' => $content, 'vars' => $tag['vars'], 'time' => time(), 'files' => array_filter(array_keys(self::collectFiles($tag)))])) {
            // echo "[SAVED]";
            $tag['action'] = 'processed and cached';
          } else {
            // echo '[CANT SAVE]';
            self::error($tag, 'can`t put content to cache');
          }
          self::$times['Tag #' . $tag['index'] . ' ' . $tag['path'] . ' finished caching'] = microtime(true);
        } else {
          //обновлять не надо, файл старый, берём из кеша и усё
          $tag['action'] = 'from cache';
          $tag['vars'] = $cached['vars'];
          $content = $cached['content'];
        }

      }
    } catch (Exception $e) {
      self::error($tag, $e->getMessage());
      PinPIE::logit("Tag processing failed
    url: {$_SERVER['REQUEST_URI']}
    tag: {$tag['fulltag']} in {$tag['path']}
    error: " . $e->getMessage());
    }
    $tag['content'] = $content;
    $tag['processing time'] = microtime(true) - $time_start;
    self::$times['Tag #' . $tag['index'] . ' ' . $tag['path'] . ' finished processing'] = microtime(true);
    return $content;
  }

  private static function checkFiles(&$tag, $cached) {
    if (!isset($cached['time'])) {
      return true;
    }
    if ($tag['cachetime'] > 0 AND $tag['cachetime'] < (time() - $cached['time'])) {
      return true;
    }
    //array_filter(array_keys(self::collectFiles($tag)))
    foreach ($cached['files'] as $file) {
      if (!file_exists($file)) {
        self::error($tag, 'Cache is unusable. One of files doesn\'t exist: ' . $file);
        return true;
      }
      if (filemtime($file) > $cached['time']) {
        return true;
      }
    }
    return false;
  }

  private static function renderTag(&$tag, $priority) {
    $content = self::parseTags(self::getContent($tag), $tag['index']);
    $content = self::parseConstants($content, $tag['index']);
    //Apply template to tag content
    if (!empty($tag['template'])) {
      $tag['vars'][0]['content'][] = $content;
      $content = self::applyTemplate($tag, $priority);
    }
    return $content;
  }

  private static function applyTemplate(&$tag, $priority = 1000) {
    self::$times['Tag #' . $tag['index'] . ' begin parsing template'] = microtime(true);
    $tag['template filename'] = self::checkPathIsInFolder(ROOT . '/templates/' . trim($tag['template'], '\\/') . '.php', ROOT . '/templates/');
    if ($tag['template filename'] === false) {
      self::error($tag, 'Template "' . $tag['template'] . '" not found');
      return false;
    }
    //$template = self::readFile($tag['template filename']); // non executable reading
    $template = self::fileExecute($tag['template filename']); // executable reading
    if ($template === false) {
      return false;
    }
    $template = self::parseConstants($template, $tag['index'], $priority);
    $template = self::parseTags($template, $tag['index'], $priority);
    if (CFG::$pinpie['template function']) {
      self::$times['Tag #' . $tag['index'] . ' calling external template function'] = microtime(true);
      $f = CFG::$pinpie['template function']; //can't remebmer how to call a func by name from an array & m too lazy to google it, sorry
      $template = $f($tag, $template);
      self::$times['Tag #' . $tag['index'] . ' finished external template function'] = microtime(true);
    } else {
      self::$times['Tag #' . $tag['index'] . ' begin processing template'] = microtime(true);
      //have to do this to use vars on the same tag it was created
      if (isset($tag['vars'][0]) AND isset($tag['vars'][0]['content']) AND strpos($template, '[[*content]]') !== false) {
        $template = str_replace('[[*content]]', implode('', $tag['vars'][0]['content']), $template);
        unset($tag['vars'][0]['content']);
      }
      $kill = [];
      $template = preg_replace_callback('/\[\[\*([^\[\]]+)\]\]/', function ($matches) use (&$tag, &$kill) {
        $r = '';
        foreach (['vars', 'parent vars'] as $storage) {
          ksort($tag[$storage]);
          foreach ($tag[$storage] as $priority => $vars) {
            if (isset($vars[$matches[1]])) {
              $r .= implode('', $vars[$matches[1]]);
              if (CFG::$pinpie['template clear vars after use']) {
                $kill[$storage][$priority][] = $matches[1];
              }
            }
          }
        }
        return $r;
      }, $template); //заменить на ту которая возвращает и убирать из тага переменные по мере их потребления
      //var_dump('kill of tag ' . $tag['index'], $kill);
      foreach ($kill as $storage => $priorities) {
        foreach ($priorities as $priority => $vars) {
          foreach ($vars as $var) {
            unset ($tag[$storage][$priority][$var]);
          }
        }
      }
      self::$times['Tag #' . $tag['index'] . ' finished processing template'] = microtime(true);
    }
    self::$times['Tag #' . $tag['index'] . ' finished parsing template'] = microtime(true);
    return $template;
    //return self::parseStatic($template, $tag);
  }

  private static function parseStatic($content) {
    self::$times['begin parsing statics'] = microtime(true);
    $matches = array();
    preg_match_all('/\[\[(!*)%([^\]]+)\]([^\[\]]*)\]/U', $content, $matches);
    $tags = [];
    $replaces = [];
    $fulltags = [];
    foreach ($matches[2] as $k => $v) {
      if (!isset($tags[$v])) { //check if we already parsed this static file before
        $tag = ['type' => '', 'onlyPath' => ($matches[1][$k] == '!' ? true : false), 'path' => '', 'query' => '', 'fulltag' => $matches[0][$k], 'content' => $matches[0][$k]];
        $s = explode('=', $v, 2);
        if (isset($s[1])) {
          $pu = parse_url($s[1]);
          $tag['path'] = (isset($pu['path']) ? $pu['path'] : '');
          $tag['query'] = (isset($pu['query']) ? $pu['query'] : '');
        }
        $tag['type'] = $s[0];
        $tags[$v] = $tag;
        $fulltags[] = $matches[0][$k];
      }
    }


    foreach ($tags as $tag) {
      $path = StatiCon::getStaticPath($tag['path'], $tag['type']);
      if ($path !== false) {
        $path .= (!empty($tag['query']) ? '?' . $tag['query'] : '');
        if (!$tag['onlyPath']) {
          switch ($tag['type']) {
            case 'js':
              $path = "<script type='text/javascript' src='$path'></script>";
              break;
            case 'css':
              $path = "<link rel='stylesheet' type='text/css' href='$path'>";
              break;
            case 'img':
              $path = "<img src='$path'>";
              break;
          }
        }
        $tag['content'] = $path;
      }
      $replaces[] = $tag['content'];
      self::$statics[] = $tag;
    }
    self::$times['finished parsing statics'] = microtime(true);
    return str_replace($fulltags, $replaces, $content);
  }

  private static function processCommand($tag) {
    $r = false;
    switch ($tag['name']) {
      case 'template':
        PinPIE::$template = $tag['value'];
        $r = 'Template set to ' . PinPIE::$template;
        break;
      default :
        PinPIE::logit('Unknown command skipped. tag:' . $tag['fulltag'] . ' in ' . self::getTagPath($tag));
        $tag['skipped'] = true;
    }
    $tag['content'] = $r;
    self::$commands[] = $tag;
    return $r;
  }

  private static function getTagPath($tag) {
    return '/' . implode('/', array_merge(self::$path, [$tag['type'] . $tag['name']]));
  }

  public static function parseTags($content, $parent = false, $priority = 10000) {
    self::$depth++;
    if ($parent !== false AND isset(self::$tags[$parent])) {
      self::$path[] = self::$tags[$parent]['type'] . self::$tags[$parent]['name'];
    } else {
      $parent = self::$currentTag;
    }
    $s = preg_replace_callback('/\[\s*([^\[\]]*)\s*\[\s*(!*)(\d*)([@#$%]?)([^!\d@#$%*=][^\[\]]+)\s*\]\s*([^\[\]]*)\s*\](\r\n|\n\r|\r\n)*?/smuUS',
      function ($matches) use ($parent, $priority) {
        $matches += ['', '', '', '', '', '', '', '',]; //defaults =) to prevent warning on last *
        return self::createTag($matches, $parent, $priority);
      }
      , $content);
    array_pop(self::$path);
    self::$depth--;
    return $s;
  }

  /**
   * @param $matches
   * @param $parent
   * @param $priority
   * @return bool|mixed|string
   */
  private static function createTag($matches, $parent, $priority) {
    //return preg_replace_callback('/\[([\*{1,2}\w]*)\[(!*)(\d*)([@#$%*]?)([^!\d@#$%*=].+)\](\w*)\]([\n\r])/sU', function ($matches) use ($parent) {
    /*array (size=6)
      0 => string '[[!$ajaja]]' (length=11)
      1 => string '**heading'
      2 => string '!' (length=1)
      3 => string '' (length=0)
      4 => string '$' (length=1)
      5 => string 'ajaja' (length=5)
      6 => string 'minitemplate'
      7 => \n\r
    */
    //var_dump($matches);
    $params = null;
    $value = null;
    $name = explode('?', $matches[5], 2);
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
    $tag = [
      'fulltag' => $matches[0],
      'cache' => ($matches[2] == '!' ? false : true),
      'cachetime' => ($matches[3] == '' ? false : $matches[3]),
      'cacheble parents' => false,
      'depth' => self::$depth,
      'type' => $matches[4],
      'name' => $name,
      'params' => $params,
      'value' => $value,
      'delayed' => ($matches[1] == '' ? false : $matches[1]),
      'template' => ($matches[6] == '' ? false : $matches[6]),
      'template filename' => '',
      'filename' => '',
      'parent' => $parent,
      'parents' => [],
      'children' => [],
      'vars' => [],
      'parent vars' => [],
      'files' => [], //to prevent duplication, filenames are stored as KEYS
    ];
    $tag['index'] = count(self::$tags);
    self::$tags[] = $tag;
    self::$currentTag = $tag['index'];
    $tag =  &self::$tags[$tag['index']];
    $tag['parents'] = self::$tags[$tag['parent']]['parents'];
    $tag['parents'][] = $tag['parent'];
    self::$tags[$tag['parent']]['children'][] = $tag['index'];
    $tag['child index'] = count(self::$tags[$tag['parent']]['children']) - 1;
    $tag['path'] = self::getTagPath($tag);
    if (self::$tags[$tag['parent']]['cacheble parents'] OR self::$tags[$tag['parent']]['cache']) {
      $tag['cacheble parents'] = true;
    }
    if (!$tag['cacheble parents'] AND $tag['cache']) {
      $tag['parent vars'] = self::$tags[$tag['parent']]['vars'];
    } else {
      $tag['vars'] = self::$tags[$tag['parent']]['vars'];
    }
    self::$tags[$tag['parent']]['vars'] = [];
    ///////////
    $r = '';
    switch ($tag['type']) {
      case '':
      case '$':
        $r = self::processTag($tag, $priority);
        break;

      case '#':
        $r = self::processCommand($tag);
        break;

      case '@':
        self::processCommand($tag);
        break;

      case '%':
        if ($tag['delayed']) {
          //have to cut the template variable name
          $r = "[[{$tag['type']}{$tag['name']}={$tag['value']}" . ($tag['params'] ? '?' . $tag['params'] : '') . "]]";
        } else {
          $r = $tag['fulltag'];
        }
        break;

      default :
        PinPIE::logit('Unknown tag found. tag:' . $tag['fulltag'] . ' in ' . self::getTagPath($tag));
    }
    if ($r !== '') {
      $r .= $matches[7];
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
    self::transferVars($tag);
    self::transferFiles($tag);
    //возвращаемое пишется в текст содержиого тега или страницы.
    return $r;
  }

  private static function transferVars($tag) {
    $from = &self::$tags[$tag['index']];
    $to = &self::$tags[$tag['parent']];
    $to['vars'] = $from['vars'];
    foreach ($from['parent vars'] as $priority => $vars) {
      if (!isset($to['vars'][$priority])) {
        $to['vars'][$priority] = [];
      }
      $to['vars'][$priority] = array_merge_recursive($vars, $to['vars'][$priority]);
    }
    $from['vars'] = [];
    $from['parent vars'] = [];
  }

  private static function transferFiles($tag) {
    $from = &self::$tags[$tag['index']];
    $to = &self::$tags[$tag['parent']];
    $to['files'] = array_merge($to['files'], self::collectFiles($from));
    $from['files'] = [];
  }

  private static function collectFiles($tag) {
    return array_merge($tag['files'], [$tag['filename'] => true, $tag['template filename'] => true]);
  }

  /**
   * Set current page template. For plain text output PinPIE::setTemplate(false) or just PinPIE::setTemplate().
   * @param bool|string $template Template filename without extension, stored in /templates folder.
   */
  public static function setTemplate($template = false) {
    if (!$template) {
      $template = false;
    }
    static::$template = $template;
  }

  /**
   * Get current page template. False will be returned for plain text output.
   * @return string|bool
   */
  public static function getTemplate() {
    return static::$template;
  }

  public static function report() {
    if (!empty(CFG::$pinpie['report password'])) {
      if (!isset($_GET['PINPIEREPORT']) OR $_GET['PINPIEREPORT'] !== CFG::$pinpie['report password']) {
        return false;
      }
    }
    echo '<hr>';
    echo '$times (ms):<br>';
    echo 'Total: ' . number_format((microtime(true) - PIN_TIME_START) * 1000, 2) . "ms<br>";
    $prev = PIN_TIME_START;
    foreach (self::$times as $key => $value) {
      echo number_format(($value - $prev) * 1000, 2) . " : " . $key . "<br>";
      $prev = $value;
    }
    echo '<br><br>';
    if (empty(self::$errors)) {
      echo "\n\n<br><br>NO ERRORS<br><br>\n\n";
    } else {
      echo 'Errors:<br>';
      var_dump(self::$errors);
    }
    echo '<br><br>';
    echo '$tags:<br>';
    echo '<pre>';
    foreach (self::$tags as $tag) {
      echo str_repeat('  ', $tag['depth']) . $tag['index'] . ' ' . trim($tag['fulltag'], " \n\r\t") . "\n";
    }
    echo '</pre><br>';
    foreach (self::$tags as &$tag) {
      ksort($tag);
      $tag = ['fulltag' => $tag['fulltag'], 'index' => $tag['index']] + $tag;
    }
    unset($tag);
    var_dump(self::$tags);
    echo '$statics:<br>';
    var_dump(self::$statics);
    echo '$commands:<br>';
    var_dump(self::$commands);
    echo '$constants:<br>';
    var_dump(self::$constants);
    return true;
  }

  private static function parseConstants($content, $parent, $priority = 10000) {
    return preg_replace_callback('/\[\s*([^\[\]]*)\s*\[=([^\[\]]*)\]\]/smuUS',
      function ($matches) use ($parent, $priority) {
        $matches += ['', '', '',]; //defaults =) to prevent warning on last *
        if ($matches[1] === '') {
          return $matches[2];
        }
        if (!isset(self::$tags[$parent]['vars'][$priority])) {
          self::$tags[$parent]['vars'][$priority] = [];
        }
        if (!isset(self::$tags[$parent]['vars'][$priority][$matches[1]])) {
          self::$tags[$parent]['vars'][$priority][$matches[1]] = [];
        }
        self::$tags[$parent]['vars'][$priority][$matches[1]][] = $matches[2];
        return '';
      }
      , $content);
  }


  public static function Postincludes() {
    self::$times['Postincludes'] = microtime(true);
    $content = ob_get_clean();
    self::$times['getting content after'] = microtime(true);
    if (self::$template === false) {
      //can be used for ajax output
      echo $content;
      exit();
    }
    //парсим содержимое страницы
    $content = self::parseTags($content, 0);
    self::$tags[0]['template'] = self::$template;
    //парсим константы, какие найдем
    $content = self::parseConstants($content, 0);
    //кладем в отложенный вывод
    //self::putDelayed('*content', $content);
    self::$tags[0]['vars'][0]['content'][] = $content; //zero for higher priority
    self::$times['page parsed'] = microtime(true);
    $content = self::applyTemplate(self::$tags[0]);
    //парсим статику всего что вышло
    $content = self::parseStatic($content);
    self::$times['parseStatic done'] = microtime(true);
    //выводим
    echo $content;
    self::$times['echo $content'] = microtime(true);
  }


}

PinPIE::Init();
