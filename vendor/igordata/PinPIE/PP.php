<?php

namespace igordata\PinPIE;


class PP {
  private static $pinpie = null;
  public static $c = null;
  public $conf = null;
  public $document = null,
    $template = 'default';
  private $tags = [];
  public $currentTag = null;
  private $depth = 0, $totaltagsprocessed = 0;
  private $path = [];
  public $times = [],
    $errors = [];

  public function __construct($config = false) {
    static::$pinpie = $this;
    define(__NAMESPACE__ . '\PIN_TIME_START', microtime(true));
    define(__NAMESPACE__ . '\PIN_MEMORY_START', memory_get_peak_usage());
    define(__NAMESPACE__ . '\DS', DIRECTORY_SEPARATOR);
    define(__NAMESPACE__ . '\ROOT', rtrim(str_replace('\\', '/', dirname($_SERVER["SCRIPT_FILENAME"])), DS));
    if ($config === false) {
      $config = ROOT . DS . 'config' . DS . basename($_SERVER['SERVER_NAME']) . '.php';
    }

    $this->initConfig($config);


    $this->times['PinPIE classes are loaded'] = microtime(true);
    $this->Init();

    if (!empty($this->conf->pinpie['preinclude']) AND file_exists($this->conf->pinpie['preinclude'])) {
      include $this->conf->pinpie['preinclude'];
    }

    $path = rtrim($this->conf->pinpie['pages folder'], DS) . DS . trim($this->document, DS);
    if ($this->conf->pinpie['pages realpath check']) {
      $path = $this->checkPathIsInFolder($path, $this->conf->pinpie['pages folder']);
    }

    if ($path !== false AND file_exists($path)) {
      include $path;
    }
    $this->render();

    if (!empty($this->conf->pinpie['postinclude']) AND file_exists($this->conf->pinpie['postinclude'])) {
      include $this->conf->pinpie['postinclude'];
    }

    if ($this->conf->showtime) {
      echo number_format((microtime(true) - PIN_TIME_START) * 1000, 3, '.', '') . "ms";
    }

  }

  private function initConfig($config) {
    static::$c = $this->conf = new CFG($config);
    //$this->conf->databases='test';
    //$this->conf->static_servers = 'test too';
  }


  private $initDone = false;

  public function Init() {
    if ($this->initDone) {
      return true;
    }
    $this->initDone = true;
    $url = parse_url($_SERVER['REQUEST_URI']);
    $this->document = $this->getDocument($url['path']);
    if ($this->document === false) {
      //requested url not found
      http_response_code(404);
      $this->document = trim($this->conf->pinpie['page not found'], DS);
    }
    $this->tags = [
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
    $this->currentTag = 0;
    $this->cache = new Cache($this->conf->pinpie['cache type']);
    ob_start();
  }

  private $getDocumentRecur = 0;

  private function getDocument($url) {
    $this->getDocumentRecur++;
    if ($this->getDocumentRecur > $this->conf->pinpie['route to parent']) {
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
    $path = $this->conf->pinpie['pages folder'] . DS . $surl . '.php';

    if (file_exists($path)) {
      /* file found */
      if ($this->conf->pinpie['pages realpath check'] AND !$this->checkPathIsInFolder($path, $this->conf->pinpie['pages folder'])) {
        /* if file was found, but had to check realpath and check failed (file is not in dir where it have to be) */
        return false;
      }
      $doc = $surl . '.php';
    } else {
      //Second step. If it is directory, look for "/pages/ololo/ajaja/index.php".
      $path = $this->conf->pinpie['pages folder'] . DS . $surl;
      if (is_dir($path) AND file_exists($path . DS . 'index.php')) {
        if ($this->conf->pinpie['pages realpath check'] AND !$this->checkPathIsInFolder($path, $this->conf->pinpie['pages folder'])) {
          return false;
        }
        $doc = $surl . DS . 'index.php';
      } else {
        //Third step. If $this->conf->route_to_parent is set greater than zero, will look for nearest parent. Mean "/pages/ololo/ajaja/index.php" if not exist, goes to"/pages/ololo.php" or "/pages/ololo/index.php". (BUT NOT "/pages/index.php" anyway)
        if ($this->conf->pinpie['route to parent'] > 0) {
          unset($url[count($url) - 1]);
          $doc = $this->getDocument($url);
        }
      }
    }
    return $doc;
  }


  /**
   * Check that path is really inside that folder, and return path if yes, and FALSE if not.
   * @param String $path Path to check
   * @param String $folder Path to folder, where $path have to be in
   * @return Mixed False on fail, or $path on success
   *
   */
  public function checkPathIsInFolder($path, $folder) {
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

  public function render() {
    $this->times['Page code executed'] = microtime(true);
    $content = ob_get_clean();
    if ($this->template === false) {
      //can be used for ajax output
      echo $content;
      return true;
    }
    //парсим содержимое страницы
    $content = $this->parseTags($content, 0, 10000);
    $this->tags[0]['template'] = $this->template;
    $this->tags[0]['vars'][0]['content'][] = $content; //zero for higher priority
    $this->times['page parsed'] = microtime(true);
    // switching priority to make template vars render first
    $this->tags[0]['priority'] = 1000;
    $content = $this->applyTemplate($this->tags[0]);

    $this->tags[0]['time']['end'] = microtime(true);
    $this->tags[0]['time']['total'] = $this->tags[0]['time']['end'] - $this->tags[0]['time']['start'];
    $this->times['template applied'] = microtime(true);
    //выводим
    echo $content;
    $this->times['echo $content'] = microtime(true);
    return true;
  }

  private function applyTemplate(&$tag) {
    return 'olili';
  }

  private function parseTags($content, $parent = false, $priority = 10000) {
    $this->depth++;
    if ($parent !== false AND isset($this->tags[$parent])) {
      $this->path[] = $this->tags[$parent]['type'] . $this->tags[$parent]['name'];
    } else {
      $parent = $this->currentTag;
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
        return 'whoohoo';
        /* defaults =) to prevent warning on last (enter)* detector */
        $matches += ['', '', '', '', '', '', ''];
        /* creating tag from array of matches */
        $tag = static::tagCreate($matches, $parent, $priority);
        /* put tag to all tags list and set currentTag integer value to current tag index */
        $tag['index'] = count($this->tags);
        $this->tags[] = &$tag; /* !!! &&& BY REF HERE &&& !!! */
        $this->currentTag = $tag['index'];
        /* Transfer vars and files from parent to this tag */
        static::tagTakeFromParent($tag);
        /* render output */
        $tag['output'] = static::tagGetContent($tag);
        if (!empty($tag['output'])) {
          /* if tag output is not empty - add line endings to make tags with line endings just after tag
          have its output have new line chars. And tags without new line chars after tags will be
          replaced only with its output. */
          $tag['output'] .= $matches[6];
        }
        /* check if the output have to go to placeholder and put if yes */
        $tag = static::tagProcessDelayed($tag);
        /* Transfer all vars and files back to parent */
        static::tagReturnToParent($tag);
        /* set time for debug */
        $tag['time']['end'] = microtime(true);
        $tag['time']['total'] = $tag['time']['end'] - $tag['time']['start'];
        /* return tag output so it will replace tag in the text with its output */
        return $tag['output'];
      }
      , $content);
    array_pop($this->path);
    $this->depth--;
    return $content;
  }


  public static function report() {
    static::$pinpie->rep();
  }

  private function rep() {

    if (!$this->conf->debug) {
      return false;
    } else {
      if (!empty($this->conf->pinpie['report password']) AND (!isset($_GET['PINPIEREPORT']) OR $_GET['PINPIEREPORT'] !== $this->conf->pinpie['report password'])) {
        return false;
      }
    }
    echo '<hr>';
    echo '$times (ms):<br>';
    echo 'Total: ' . number_format((microtime(true) - PIN_TIME_START) * 1000, 2) . "ms<br>";
    $prev = PIN_TIME_START;
    foreach ($this->times as $key => $value) {
      echo number_format(($value - $prev) * 1000, 2) . " : " . $key . "<br>";
      $prev = $value;
    }
    echo '<br><br>';
    if (empty($this->errors)) {
      echo "\n\n<br><br>NO ERRORS<br><br>\n\n";
    } else {
      echo 'Errors:<br>';
      var_dump($this->errors);
    }
    echo '<br><br>';
    echo '$tags:<br>';
    echo '<pre>';
    foreach ($this->tags as $tag) {
      if (empty($tag['time'])) {
        $tag['time'] = ['total' => 0];
      }
      echo str_repeat('  ', $tag['depth']) . $tag['index'] . ' ' . number_format(round($tag['time']['total'] * 1000, 2), 2) . 'ms ' . trim($tag['fulltag'], " \n\r\t") . "\n";
    }
    echo '</pre><br>';
    foreach ($this->tags as &$tag) {
      ksort($tag);
      $tag = ['fulltag' => $tag['fulltag'], 'index' => $tag['index']] + $tag;
    }
    unset($tag);
    echo '<pre>';
    var_dump($this->tags);
    echo '</pre>';
    return true;
  }

}