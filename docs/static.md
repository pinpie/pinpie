# Static content

Static file means file, that will be sent to user from server hard drive as is, without any modification or preprocessig. Usually, static files are images, client-side javascript files, css files, etc.

## Time hash
To automate refreshing of browser cache of loaded files each file linked through [static tags](tags.md#static-tags) will have ```time``` GET-parameter attached to URL. This is hash based on [salt](config.md#random_stuff), filename and file modification time. When file is modified, time changes, hash changes and browser receive different URL to load this file. So browser will load new version and you will never need to press ```Ctrl+F5``` to reload page without cache.

## List of static content servers
Modern browsers can download content of page from one site in about 5 simultaneous threads. If your page have some amount of images, js scripts and css files, browser will start downloading the first 5-6 files, and will not try download other files while downloading them.
If your page have plenty of files, even small files, it could take some seconds for browser to download them all.
 
There are two main strategies to speed up page loading time. First is to stick all images to one big image to download it in one thread, and use css background offset for html elements to show corresponding images at this elements. Or put css files all together to one file. This is the best practice.
If for some reason you cant do that, you have to increase the quantity of simultaneous download threads. The way to do that is to spread static content over more than one server, so the browser will have possibility to download in 5 threads from each server.

PinPIE realise this strategy in the simplest way. It chose server randomly from the list, considering all servers have all files. Actually, it's not random, it's based on filename and each user have offset cookie to distribute load across multiple servers. For each file for each user it will be always located at the same server. So browser caching will still work properly.

List of static content servers is to be set in config file by ```$static_servers``` variable and is accessible outside via ```CFG::$static_servers```. Default value is empty array.

Server is chosen by ```Staticon::getServer($file)``` method, where $file is path to file inside ```CFG::$pinpie['static folder']```. Currently there is no way to redefine this method in config file, but it have to be done one day to allow more control over server load and CDN balancing, http/https protocol switching (http is hardcoded now).


## Custom static files types
Static file type is defined in tag and can be used in pre-minification and pre-compressing process.
Static tag syntax: ```[[%type=path]]```.
Currently there are three static types: js, css and img. By default js and css are allowed to be compressed and minified. Type img is not allowed to be minified or gzipped by default.
You can use any custom types, to separate your minification options. Compression have only one option - compression level set in ```CFG::$pinpie['gzip static level']```. By default it is 9.


## Compression
You can [make your web-server to use pre-compressed files](https://www.google.ru/search?q=pre-compress+static+content) instead original ones. To enable pre-compression in PinPIE engine you have to set ```CFG::$pinpie['gzip static']``` to true. Also you can set compression level in ```CFG::$pinpie['gzip static level']```.
To be pre-compressed static file type have to be in array ```CFG::$pinpie['gzip static filetypes']``` which default value is ```['js', 'css']```. If compression is enabled and type is in array, than *filepath.gz* will be created.

## Minification
Minification is process of lowering script file size, without losing its functionality as is. It is achieved by removing all comments, spaces, newline chars, shortening functions and variables names, etc. PinPIE can check if minified version of requested file exist in directory, and translate static tag to corresponding link to minified file. If minified version is older than a original file and minification is enabled than PinPIE will start minifier.

To enable automatic minification you should set ```CFG::$pinpie['minify static files']``` to true *and* set your function name to ```CFG::$pinpie['minify static files function']``` to call it when some file have to be minified. You have to write this function by your own. In that function you have to call your favorite minifier or schedule this operation. Minifier call can be asynchronous. Anyway the newer file will be used in tag URL.

### Example
This example will process tag ```[[%css=/main.css]]``` and call [YUI Compressor](http://yui.github.io/yuicompressor/) external java executable to minify it.

```
function autominify($filepath, $minfilepath, $type) {
  if (!file_exists($filepath)) {
    return false;
  }
  if (in_array($type, CFG::$pinpie['minify static filetypes'])) {
    exec("java -jar /var/www/yuic.jar \"$filepath\" -o \"$minfilepath\" --type $type", $out, $err);
  }
  return true
}
```

Where:  

 * ```$filepath``` is the file path from tag. Its value is ```CFG::$pinpie['static folder']/main.css```
 * ```$minfilepath``` is the file path to minified version to check. Value: ```CFG::$pinpie['static folder']/min.main.css```
 * ```$type``` here is "css". *It is not taken from file extension. It is taken from %css*.
 * ```CFG::$pinpie['minify static filetypes']``` is array with default value ```['js', 'css']```. You can change it in config file.
 
 Remember that you can run minifier in background or just schedule this operation. The newest file will be used anyway.