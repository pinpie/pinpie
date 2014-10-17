# Configuration

## Config files
All config files are located in ```/config/``` folder. For every request config is chosen automatically, based on sever name. Yes, just ```basename($_SERVER['SERVER_NAME']) . '.php'```. So you can store multiple configurations in one folder.
To create configuration file you have to create file inside ```/config/``` folder with the name of your server and ```.php``` extension. Except $random_stuff there is no obligatory settings to start working with PinPIE.

In config file you can set this variables:

* $pinpie &mdash; array to store PinPIE settings
* $conf &mdash; array for custom settings, you can store here any settings you need, and access it by ```CFG::$conf``` anywhere
* $databases &mdash; store here settings to connect to your databases
* $static_servers &mdash; array of static servers (see [static readme](static.md))
* $random_stuff &mdash; string, used as salt. Have to be full of really random symbols   

They will be accessible globally through static class CFG.

## PinPIE settings
There are not so many settings PinPIE need to work. Here are the defaults from ```/pinpie/classes/cfg.class.php```:
```
$pinpie = [
  'gzip static' => false,
  'gzip static level' => 9,
  'gzip static filetypes' => ['js', 'css'],
  'cache type' => 'filecache',
  'cache rules' => [
    'default' => ['ignore url' => false, 'ignore query params' => false],
    200 => ['ignore url' => false, 'ignore query params' => false],
    404 => ['ignore url' => true, 'ignore query params' => true]
  ],
  'cache hash algo' => 'sha1',
  'code page' => 'utf-8',
  'log' => [
    'path' => 'pin.log',
    'show' => false,
  ],
  'minify static files' => false,
  'minify static filetypes' => ['js', 'css'],
  'minify static files function' => false,
  'pages folder' => ROOT . '/pages',
  'static folder' => ROOT,
  'page not found' => 'index.php',
  'route to parent' => 1,
  'site url' => $_SERVER['SERVER_NAME'],
  'template function' => false,
  'template clear vars after use' => true,
];
```
### cache
Caching could be controlled with this parameters:

* cache type &mdash; cache provider, can be filecache, memcached or disabled. Custom class can be used. Default value is ```filecache```
* cache hash algo &mdash; algorithm to use for generation of cache hashes. By default is used ```sha1```
* cache rules &mdash; used to fight enormous cache growing in cases of big amount of unique requests

It's highly recommended to read more about caching mechanics in [cache readme](cache.md).

### codepage
PinPIE recommend you to store the code page of your project in ```$pinpie['utf-8']``` for you could easily access and use it in your scripts. Default value is ```utf-8```.

### gzip
Gzip pre-compression allow to lower size of some static files. This pack of settings allow you to control automatic pre-compression of static files:

* gzip static &mdash; enables automatic pre-compression of static files. By default it is disabled, value is ```false```
* gzip static level &mdash; level of compression, by default it is ```9```
* gzip static filetypes &mdash; array to store static files tags types of files which have to be compressed. By default is array ```['js', 'css']```

It's highly recommended to read more about static files tags in [static readme](static.md).

### log
PinPIE will log some errors like nonexistent tag file to ```pin.log``` file by default. You can set the other path in ```$pinpie['log']['path']```. Also you can enable output of error log just to your page, which is disabled by default.

### minify
Minification can be made automatically by PinPIE. With this settings you can set up the process:

* minify static files &mdash; enables automatic minification. Defalt value is ```false```
* minify static filetypes &mdash; list of static files tags types to minify files. Defalt is array ```['js', 'css']```
* minify static files function &mdash; function to call when file have to be minified. In that function you have to call your minifier. Value is ```false``` by default

It's highly recommended to read more about static files tags in [static readme](static.md).

### page not found
Set the page to handle all 404 not found requests. By default it is ```index.php```, but it is recommended to use special page and create something like ```/pages/notfound.php```. 

Read more in main readme, section [URL handling](../readme.md#url-handling).

### pages folder
Default location to store pages. Default value is ```ROOT . '/pages'```. Read more [constants](../readme.md#some-pinpie-constants).

### route to parent
This variable is used in URL handling mechanics. Read more in main readme, section [URL handling](../readme.md#url-handling).

### site url
You don't have to change or set it in config, but if you want - you can. This variable is used in automatic URL creation for static files tags in case if list of static servers is empty.

### static folder
In most cases static files like js, css or images are located in the same folder as php-scripts do. Default value is ```ROOT```. But you can keep separately all static content. Set ```$pinpie['static folder']``` in config to your static files location and use convenient static tags in your project. 

It's highly recommended to read more about static files tags in [static readme](static.md).

## Other settings

### Custom configuration
You can store any other settings in ```$conf``` array, if you want. It will be available globally in ```CFG::$conf```.   

### Databases array
Array ```$databases``` created specially to store database settings. You can use it to provide settings to your database classes.

### Static servers
List of static content settings can be set in ```$static_servers``` variable. Please, read more in [static readme](static.md).

### Salt
For security reason you have to set ```$random_stuff``` variable to random string, unique to every your site. This variable is used in [cache](cache.md) hash generation, and can be used anywhere in your code via ```CFG::$random_string```.