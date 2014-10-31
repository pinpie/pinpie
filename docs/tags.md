
# Tags
PinPIE have tag-based parser. Tag syntax is inspired by ModX tag system. There are some different tags described below. 

## Chunk
### *Syntax: [[chunk]]*
Chunk is plain text located in file in /chunks folder. This code ```[[some_chunk]]``` will make PinPIE locate file /chunks/somechunk.php and load its content as plain text. It will be parsed by PinPIE engine to find other tags inside, but no php code will be executed.

Chunks could be located inside subfolders to keep it all more organised: ```[[some/chunk]]``` or ```[[some/long/path/chunk]]```.



## Snippet
### *Syntax: [[$snippet]]*
Snippet is php file, that will be included, executed and parsed for other tags. Snippet tag starts with $ symbol: ```[[$some_snippet]]```.

Snippet allow to transfer GET-like parameters inside its code. Just like in URL you can add variables to a snippet name: ```[[$snippet?foo=bar&cat=dog]]```. Inside snippet they will be available as variables ```$foo``` and ```$cat```. If variable or its value are changed, snippet is forced to be recached. So you don't have to worry about cache while in development.


## Snippet caching
Snippet output is cached forever by default. But you can prevent caching using exclamation point, or set cache expiration time (in seconds).

### Examples:  
 - ```[[$some_snippet]]``` &mdash; cached forever
 - ```[[!$some_snippet]]``` &mdash; caching disabled, snippet will be executed every time
 - ```[[3600$some_snippet]]``` &mdash; snippet is cached for one hour
 
For further info please read [cache](cache.md) readme.
 
## Tag templates
To chunk and to snippet tags a template can be applied. Please read more in [template readme](template.md).

## Variable placeholder
### *Syntax: [[\*var]]*
Every chunk, snippet or constant output can be put to the variable. This variable can be used in the page, in tags, or certainly in the template. Variable placeholder starts with asterisk. Here is syntax example: ```[[*var]]```

This variables could be used in external template engine with help or your custom function. They are passed to function as associative array. See external template section.

Variables are cached within its parents cache, so you don't have to worry about setting vars inside cacheable chunks and snippets. They will be cached and used outside this tags. See cache section. 

Unused placeholders will be removed from output.

There are reserved placeholders: ```[[*content]]``` for page output in template, and ```[[*tagcontent]]``` for tag templates. 

### Example №1:  
To put a page title to a template having \<title\> tag, you can put the page title in variable using constant ```[var[=About]]``` in your page, and this code ```<title>[[*var]]</title>``` in your template.

### Example №2:  
You can use var even before it is set, because placeholders are replaced by variable content after page or tag is processed.

This code, based constant tag
```
<span>[[*var]]</span>
[var[=pinpie]]
```
or this
```
[var[=pinpie]]
<span>[[*var]]</span>
```
will provide you this HTML code:  
```
<span>pinpie</span>
```

### Example №3:  
You can use variables with snippets or chunks.
```
[var[some_chunk]]
<span>[[*var]]</span>
```
having /chunks/some_chunk.php file with code ```pinpie```   
or
```
[var[$some_snippet]]
<span>[[*var]]</span>
```
having /snippets/some_snippet.php file with code ```pinpie``` or ```<?php echo 'pinpie';?>```  
will provide you the same HTML code:  
```
<span>pinpie</span>
```   
   



## Constant
### *Syntax: [[=constant]]*
Constant is just a line of text, that will go to output. It have no use without using variable placeholder. Because all pages are stored in files, constant is convenient way to put some small text from a page file to the template. Please see variable placeholder section.

Constant tag starts with equals sign. Here is a constant example:  
```[[=simple constant text]]```

Constant text can be multiline:  
```
[[=some  
multiline  
text]]
```



## Command
### *Syntax: [[@template=main]]*
To provide control over some PinPIE engine functions inside page or tag file, use commands. Command tag starts with @ to suppress command output, or with \# to show the return value. Currently only one command is supported, and it's better to use it like this: ```[[@template=wide]]```.  


## Static tags
### *Syntax: [[%type=path]]*
Static tags are used to provide automatic using of static files features, such as minification, gzipping, and static-content server support. Static tag starts with percent sign and static content type, which is not the file extension or type. Syntax example: ```[[%js=/javascript/jquery.js]]```

Currently three types of static content are supported:    
 - js &mdash; for javascript files
 - css &mdash; for cascading style sheets
 - img &mdash; for any images

This tags are replaced by corresponding HTML tags, where URL is leading to some static server (if enabled) from a list, and have GET-parameter ```time``` with salted hash of the name and the modification time of a file. This provides automatic browser cache refreshing. So you can use the same file name, and no one will have to hit ```Ctrl+F5``` browsing your site.

Static files could be located outside the site root folder. Set ```CFG::$pinpie['static folder']``` to static files root folder. Default value is ```ROOT``` (see [constants](#root)).

For more detailed information about static tags, pre-minification and gzip pre-compression, please see [static readme](static.md).


## Examples
You can see some examples of tags usage in [tag examples](../examples/tags/readme.md).