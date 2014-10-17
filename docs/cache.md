# Cache
PinPIE provide clear and controllable automatic snippet caching. Chunks are stored in \*.php files, and are mostly cached by acceleration software like APC, XCache, eAccelerator, etc.

Each cache entry includes all file paths of used tags and their children. That means, all that files will be checked for existence and modification time. If any of them is changed after cache was created, then cache will be refreshed.

## Usage
Currently now caching have three options:
 * [[$snippet]] &mdash; By default, snippet output will be cached forever.
 * [[**!**$snippet]] &mdash; If you want disable caching, just put exclamation point. Snippet will be executed every time.
 * [[**3600**$snippet]] &mdash; You can set cache expiration time in seconds.
 
## Separate caching
 Snippets are cached separately one from each other. If you have snippet with code ```<?php echo rand(1, 100);```, and use it many times in a page, you will get different numbers for each use.
 
### *Example №1*
```
[[$snippet]]
[[$snippet]]
[[$snippet]]
```
 will produce next output:  
```
42
3
14
```
Actualy, with probability 1/1000000 it will do ```42 42 42```, so be not surprised.

The next example will make you better understand caching.

### *Example №2*
```
[[$snippet]]
[[5$snippet]]
[[!$snippet]]
```
When you will refresh a page, you will see, that first number will never change, next - every five seconds, and the last one will change each time.  


## Hash
Every cache is stored with id based on snippet and page parameters. That includes:
* snippet name
* snippet file modification date and time
* requested URL
* URL query params
* all parent tags names
* server name
* salt CFG::$random_stuff
* other params

If any of this parameters changes, that will produce different hash. PinPIE will not find snippet cache and will include snippet and execute it. That means, if snippet file was modified, it will be recached.

Algorithm used to produce hash can be set in config in ```CFG::$pinpie['cache hash algo']```. By default it is sha1.

## Cache storage

There are currently three cache storage options:
 * disabled &mdash; every snippet is forced to be executed each time
 * filecache &mdash; file-based storage (default)
 * memcached &mdash; memcached-based storage 

Cache storage can be set in config by ```CFG::$pinpie['cache type']```. Default value is *filecache*. This variable defines what file of ```Cache``` class will be included. To clearly understand that see the code: ```include PINDIR . DS . 'classes' . DS . 'cache.class.' . basename(CFG::$pinpie['cache type']) . '.php';``` and that's all.

## Cache class
Cache class files are located in /pinpie/classes/ folder. It that folder you can find three files named ```cache.class.*.php```. All that files contain the class named "Cache".  It has inside simple structure. Obligatory are only two static methods: ```get``` and ```set```.
  
### Disabled
This class contains two fake methods, that make PinPIE to think, that any cache writing completes successfully, and any reading can't find requested hash.
```
class Cache
{

  public static function get($hash) {
    return false;
  }

  public static function set($hash, $content) {
    return true;
  }

}
```

So any time PinPIE want to get cached data, it receives ```false```. This forces it to execute snipped anyway.

### Filecache
Filecache uses ```/filecache/``` folder to store cache in files named by its hash. It is the simple, but very fast. Until your OS have **free** unused memory, you will have extra-fast caching, even faster than memcached. The disadvantage is that you have to clean cache by your own, because PinPIE can't.

Every time PinPIE generates new hash for tag, it will create new file. That is not a problem, because for most of the time the size of cache will be stable, and will grow only by newly added or edited snippets. Because hash is based on file modification time, PinPIE can't find previous versions of cache files, and can't automatically delete them. 

The advantages of this mode are:
* Works everywhere. Only requirement is right to write to ```/filecache/``` folder.
* Very fast.

This type of cache is fast, because modern OS stores recent files content in free unused memory. All file access operations are highly optimized. So file cache performance is better than memcached at unix socket.

### Memcached
Memcached-based caching class uses Memcache object to store cache. Sure it have multiple servers support. Server pool is set in config var ```CFG::$pinpie['cache servers']``` as array or host and port pairs. Here is the code:
```
foreach (CFG::$pinpie['cache servers'] as $server) {
  self::$mc->addServer($server['host'], $server['port']);
}
```
Make sure you have set unique salt for every site in ```CFG::$random_stuff``` variable, because at shared hosting you may also have shared Memcached access.  
### *Attention*
Memcached cache class uses binary hashes, not hex-like as usual.

### Custom classes
You can create your own class to store cache at your favorite way. Just name it ```Cache```, put it into the file named by this model ```cache.class.YOURNAME.php```, put file to ```/pinpie/classes/``` folder and set in config  ```$pinpie['cache type'] = 'YOURNAME';```.

## Caching rules
PinPIE gives you more control of caching process. All 404 pages have different URL, and that can produce to much unwanted cache, mostly never be used again. Or there could be some GET-params that doesn't affect a page, so you don't need to use them in hash generation, because that will produce additional cache.

PinPIE allow you to ignore url or GET-params.

Caching rules could be set in config file by ```CFG::$pinpie['cache rules']```. Here are the default rules:
```
'cache rules' => [
  'default' => ['ignore url' => false, 'ignore query params' => false],
  200 => ['ignore url' => false, 'ignore query params' => false],
  404 => ['ignore url' => true, 'ignore query params' => true]
],
```
Caching rules are applied according current HTTP response code. For all general pages it is 200. For not found pages it will be 404. For all others will the ```default``` rule will be applied.

You can set your own rules for any other HTTP-code. 

### ignore url
This parameter allow you to ignore whole url in hash generation, that will make all pages with this rule have same cached output. In other words, if you will set it to true for 404 pages, snippet with random number generator will produce output only one time. This output will be cached and used for all 404 pages.
It could be set to false or true.

### ignore query params
Ignore query params allow you to ignore all or some GET-params when cache hash is generated. It could be set to false, true, or array of $_GET keys to ignore.