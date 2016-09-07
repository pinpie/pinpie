<?php

namespace igordata\PinPIE;

use igordata\PinPIE\Tags\Tag;


class PP {
  public $root = '';
  /** @var CFG | null */
  public $conf = null;
  public
    $url = ['path' => '/', 'query' => ''],
    $document = null,
    $template = 'default';
  private $tags = [];
  public $depth = 0, $totaltagsprocessed = 0;
  private $tagPath = [];
  public $times = [],
    $errors = [];

  public $vars = [];
  /** @var Tag | null */
  public $page = null;

  /** @var Cache | null */
  public $cache = null;

  public $startTime = 0,
    $startMemory = 0;

  /* one pinpie instance cache for other classes */
  public $inCa = [];

  public function __construct($config = false) {
    $this->startTime = microtime(true);
    $this->startMemory = memory_get_peak_usage();
    $this->root = rtrim(str_replace('\\', '/', dirname($_SERVER["SCRIPT_FILENAME"])), DIRECTORY_SEPARATOR);
    if ($config === false) {
      $config = $this->root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . basename($_SERVER['SERVER_NAME']) . '.php';
    }
    $this->initConfig($config);
    $this->Init();
    if (!empty($this->conf->pinpie['preinclude']) AND file_exists($this->conf->pinpie['preinclude'])) {
      include $this->conf->pinpie['preinclude'];
    }
    $this->render();
    if (!empty($this->conf->pinpie['postinclude']) AND file_exists($this->conf->pinpie['postinclude'])) {
      include $this->conf->pinpie['postinclude'];
    }
    if ($this->conf->showtime) {
      echo number_format((microtime(true) - $this->startTime) * 1000, 3, '.', '') . "ms";
    }
  }

  private function initConfig($config) {
    $this->conf = new CFG($this);
    $this->conf->readConf($config);
  }


  private $initDone = false;

  public function Init() {
    if ($this->initDone) {
      return true;
    }
    $this->initDone = true;
    $this->url = parse_url($_SERVER['REQUEST_URI']);
    $this->document = $this->getDocument($this->url['path']);
    if ($this->document === false) {
      //requested url not found
      http_response_code(404);
      $this->document = trim($this->conf->pinpie['page not found'], DIRECTORY_SEPARATOR);
    }
    if (!empty($this->conf->tags['PAGE']['class'])) {
      $pageclass = $this->conf->tags['PAGE']['class'];
      $page = new $pageclass($this, $this->conf->tags['PAGE'], 'PAGE', 'PAGE', '', '', 0, '', null, 10000, 0);
    }
    $page->filename = rtrim($this->conf->pinpie['pages folder'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($this->document, DIRECTORY_SEPARATOR);
    $page->template = 'default';
    $this->tags[] = $page;
    $this->page = $page;
    if (!empty($this->conf->pinpie['cache class'])) {
      $this->cache = new $this->conf->pinpie['cache class']($this, $this->conf->cache);
    } else {
      $this->cache = new \igordata\PinPIE\Cachers\Disabled($this, $this->conf->cache);
    }
    $this->times['PinPIE Init done'] = microtime(true);
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
      $surl = implode(DIRECTORY_SEPARATOR, $url);
    } else {
      $url = trim((string)$url, '/');
      $surl = $url;
      $url = explode('/', $url);
    }
    //if $surl is "ololo/ajaja":
    //First step. Look for "/pages/ololo/ajaja.php".
    $path = $this->conf->pinpie['pages folder'] . DIRECTORY_SEPARATOR . $surl . '.php';
    if (file_exists($path)) {
      /* file found */
      if ($this->conf->pinpie['pages realpath check'] AND !$this->checkPathIsInFolder($path, $this->conf->pinpie['pages folder'])) {
        /* if file was found, but had to check realpath and check failed (file is not in dir where it have to be) */
        return false;
      }
      $doc = $surl . '.php';
    } else {
      //Second step. If it is directory, look for "/pages/ololo/ajaja/index.php".
      $path = $this->conf->pinpie['pages folder'] . DIRECTORY_SEPARATOR . $surl;
      if (is_dir($path) AND file_exists($path . DIRECTORY_SEPARATOR . 'index.php')) {
        if ($this->conf->pinpie['pages realpath check'] AND !$this->checkPathIsInFolder($path, $this->conf->pinpie['pages folder'])) {
          return false;
        }
        $doc = $surl . DIRECTORY_SEPARATOR . 'index.php';
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
    $folderRealpath = rtrim($folderRealpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (strlen($pathRealpath) < $folderRealpath) {
      return false;
    }
    if (substr(rtrim($pathRealpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, 0, strlen($folderRealpath)) !== $folderRealpath) {
      return false;
    }
    return $path;
  }

  public function render() {
    echo $this->page->getOutput();
  }

  public function parseTags($content, $parent = null) {
    $this->tagPath[] = $parent->type . $parent->name;
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
      function ($matches) use ($parent) {
        /* defaults =) to prevent warning on last (enter)* detector */
        $matches += ['', '', '', '', '', '', ''];
        /* creating tag from array of matches */
        $tag = $this->createTag($matches, $parent);
        $tag->index = count($this->tags);
        $this->tags[] = $tag;

        /* render output */
        $tag->output = $tag->getOutput();
        if (!empty($tag->output)) {
          /* if tag output is not empty - add line endings to make tags with line endings just after tag
          have its output have new line chars. And tags without new line chars after tags will be
          replaced only with its output. */
          $tag->output .= $matches[6];
        }

        /* set time for debug */
        $tag->time['end'] = microtime(true);
        $tag->time['total'] = $tag->time['end'] - $tag->time['start'];
        /* return tag output so it will replace tag in the text with its output */
        return $tag->output;
      }
      , $content);
    array_pop($this->tagPath);
    return $content;
  }

  private function createTag($matches, $parent) {
    /* $matches
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
    $fulltag = $matches[0];
    $type = $matches[3];
    $placeholder = ($matches[1] == '' ? false : $matches[1]);
    $template = ($matches[5] == '' ? false : $matches[5]);
    if ($matches[2] === '!') {
      $cachetime = $this->conf->pinpie['cache forever time'];
    } else {
      $cachetime = ($matches[2] == '' ? 0 : (int)$matches[2]);
    }
    $fullname = $matches[4];

    $tagClass = '\igordata\PinPIE\Tags\Tag';
    if (isset($this->conf->tags[$type])) {
      $tagClass = $this->conf->tags[$type]['class'];
    }

    $tagSettings = [];
    if (!empty($this->conf->tags[$type])) {
      $tagSettings = $this->conf->tags[$type];
    }

    $tag = new $tagClass($this, $tagSettings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $parent, $parent->priority, $parent->depth + 1);

    return $tag;
  }


  public function report() {

    if (!$this->conf->debug) {
      return false;
    } else {
      if (!empty($this->conf->pinpie['report password']) AND (!isset($_GET['PINPIEREPORT']) OR $_GET['PINPIEREPORT'] !== $this->conf->pinpie['report password'])) {
        return false;
      }
    }
    echo '<hr>';
    echo '$times (ms):<br>';
    echo 'Total: ' . number_format((microtime(true) - $this->startTime) * 1000, 2) . "ms<br>";
    $prev = $this->startTime;
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
      if (empty($tag->time)) {
        $tag->time = ['total' => 0];
      }
      echo str_repeat('  ', $tag->depth) . $tag->index . ' ' . number_format(round($tag->time['total'] * 1000, 2), 2) . 'ms ' . trim($tag->fulltag, " \n\r\t") . "\n";
    }
    echo '</pre><br>';


    $ignore = ['pinpie',];
    foreach ($this->tags as $tag) {
      /**
       * @var $tag Tag
       */

      echo '<h3>#' . $tag->index . ' ' . $tag->fulltag . '</h3>';
      echo '<table>';
      foreach ($tag as $name => $value) {
        if (in_array($name, $ignore)) {
          continue;
        }
        echo '<tr>';
        switch ($name) {
          case 'parent':
            echo '<td>' . $name . '</td><td>' . ($value ? $value->index : 'NONE') . '</td>';
            break;
          case 'hash':
            echo '<td>' . $name . '</td><td>' . $value . '</td>';
            break;
          case 'time':
            echo '<td>' . $name . '</td><td>total: ' . number_format(round($tag->time['total'] * 1000, 2), 2) . 'ms, processing: ' . number_format(round($tag->time['processing'] * 1000, 2), 2) . 'ms</td>';
            break;
          case 'children':
          case 'parents':
            $s = [];
            foreach ($value as $child) {
              $s[] = $child->index;
            }
            echo '<td>' . $name . '</td><td>' . implode(',', $s) . '</td>';
            break;
          default:
            if (is_scalar($value)) {
              echo '<td>' . $name . '</td><td>' . htmlspecialchars($value) . '</td>';
              continue;
            }
            echo '<td>' . $name . '</td><td>' . htmlspecialchars(var_export($value, true)) . '</td>';
        }

        echo "</tr>\n";
      }
      echo '</table>';
    }
    return true;
  }

  public function logit($str = '') {
    if (empty($this->conf->pinpie['log'])) {
      return false;
    }
    if ($this->conf->pinpie['log']['show']) {
      echo $str . "<br>\n";
    }
    return file_put_contents($this->conf->pinpie['log']['path'], date('Y.m.d H:i:s') . ' - ' . $str . "\n", FILE_APPEND);
  }


  private $hashURL = false;

  public function getHashURL() {
    if ($this->hashURL !== false) {
      return $this->hashURL;
    }
    $code = http_response_code();
    $defaults = ['ignore url' => false, 'ignore query params' => false];
    $rules = [];
    $ruleID = 'default';
    if (isset($this->conf->pinpie['cache rules'][$code])) {
      $ruleID = $code;
    }
    if (is_array($this->conf->pinpie['cache rules'][$ruleID])) {
      $rules = array_merge($defaults, $this->conf->pinpie['cache rules'][$ruleID]);
    }
    $url = $this->url;
    //Check, if we have to use 'path' part of url, so caching could be done separately for each page
    if ($rules['ignore url']) {
      $url['path'] = '';
    }
    $url = array_merge(['query' => ''], $url);
    //Should we ignore all (true) or some (array) of get-params of url, or make it separately. Mean cache of "?page=3" differs from "?page=100".
    if ($rules['ignore query params'] === true) {
      $url['query'] = '';
    } else {
      parse_str($url['query'], $url['query']);
      foreach ($rules['ignore query params'] as $p) {
        if (isset($url['query'][$p])) {
          unset($url['query'][$p]);
        }
      }
      $url['query'] = http_build_query($url['query']);
    }
    $this->hashURL['url path'] = $url['path'];
    $this->hashURL['url query'] = $url['query'];
    return $this->hashURL;
  }

  private $filemtimes = [];

  public function filemtime($file) {
    if (!isset($this->filemtimes[$file])) {
      /* file_exists() prevents warning */
      if (file_exists($file)) {
        $this->filemtimes[$file] = filemtime($file);
      } else {
        $this->filemtimes[$file] = false;
      }
    }
    return $this->filemtimes[$file];
  }

  private $is_file = [];

  public function is_file($file) {
    if (!isset($this->is_file[$file])) {
      $this->is_file[$file] = is_file($file);
    }
    return $this->is_file[$file];
  }

}