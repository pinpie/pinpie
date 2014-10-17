PinPIE
======

PinPIE &mdash; when PHP is enough.

# About
PinPIE is lightweight php-based engine for small sites. All pages and URL handlers are stored in ```*.php``` files. Caching also included, and cache control is simple and clear. Just include your favorite classes, functions, ORM, and start to write the funky code. 

# Disadvantages
 - Not an end-user product

# Advantages
 - Lightweight
 - Tags: chunks, snippets, constants and static files
 - File-based content storage
    - Edit your content using your favorite IDE or text editor with all that highlighting, autoformating, autosaving, autouploading features and familiar hotkeys
    - Full debug support including exact line numbers and IDE code execution controls
    - Accelerators support provides lightspeed response time 
    - Version control systems friendly &mdash; you can have versions of all your content to be safe and protected against loosing something while editing
    - Deployment friendly
    - Backup friendly
 - Clear URL routing
 - Server name based config &mdash; easy to develop with local config, and deploy to production with another one 
 - Controllable snippets caching: forever (default), for exact time in seconds, and never cache
 - Automatic cache refreshing of exact tag cached content if one of its files have been changed
 - HTTP-code based caching rules to handle separately 200, 404 and any other situations
 - Template support and plain text output support
 - Easy optional template engines integration like Twig, Smarty, Mustache, etc
 - Optional static content cookie-free servers support
 - Optional automatic pre-minification for static content files (images, css, js, etc.)
 - Optional automatic gz pre-compression for static content files (images, css, js, etc.)
 - Not an end-user product ;)


# Quick overview
PinPIE is designed to handle about 100-150 pages per second at cheap $5 VPS/VDS hosting. It can be used at shared hosting as well. 

PinPIE stores content in \*.php files, located in /pages folder. This files are included before template is applied. So all page options could be set in the page code. Subdirectories are allowed. Pages can handle URLs longer, than a path to a file. See URL handling section.

PinPIE uses tags. Tags have flexible caching mechanics, automatically refreshing expired tags, if its files or files of its children were changed.

# Starting
Folder /sources of this project contains required files and folders to make PinPIE work properly. Copy its contents to your project.

## Entry point
PinPIE require to be included in main entry point of your project, and all requests have to be rerouted to that file. Generally, the main entry point for site code is ```ROOT/index.php```. This file require to have ```include 'pinpie/pinpie.php';``` line to start PinPIE working.

This, in fact, are the only things you have to do to start using PinPIE.

## About preinclude.php and postinclude.php
For every request there are to files, that will be included if they exist. Before processing a page file ```ROOT/preinclude.php``` will be included. And ```ROOT/postinclude.php``` will be included after page is processed and assembled. If you will upgrade PinPIE with new version, this files will not be overwritten, because this files doesn't exist in PinPIE project. So feel free to modify them corresponding your needs. 
 
# File-based content storage
All content is stored in files. Pages are located at /pages folder, code snippets at /snippets folder, text chunks at /chunks, templates in /templates folder. Nested folders are allowed and could be used in tags and templates names.

# Tags
PinPIE have tag-based parser. Tag syntax is inspired by ModX tag system.

Basic tags are:
* Chunks &mdash; a pieces of plain text
* Snippets &mdash; a pieces of php code to execute

Read more in [tags readme](docs/tags.md).


# Cache
PinPIE provide clear and controllable automatic snippet caching.

Read more in [cache readme](docs/cache.md).

# Some PinPIE constants
## DS
It's just short version of ```DIRECTORY_SEPARATOR```. Here is the code: ```define('DS', DIRECTORY_SEPARATOR);```
## ROOT
This constant is expected to be root folder for PinPIE files and subfolders. It is set in ```/pinpie/pinpie.php``` and based on ```$_SERVER["SCRIPT_FILENAME"]```  value. Here is the code: ```define('ROOT', rtrim(str_replace('\\', '/', dirname($_SERVER["SCRIPT_FILENAME"])), DS));```

## PIN_TIME_START
This constant is defined just when ```/pinpie/pinpie.php``` is included. Code: ```define('PIN_TIME_START', microtime(true));```

## PIN_MEMORY_START
Defined just after PIN_TIME_START. Code: ```define('PIN_MEMORY_START', memory_get_usage());```

# URL handling
PinPIE require all requests to be routed to index.php, where PinPIE entry point is included, and pages or URL hanlers to be located inside ```CFG::$pinpie['pages folder']```. By default its value is ```ROOT/pages``` (see [constants](#root)). The default page is located at path /pages/index.php.

URL processing is quite simple. If requested URL is /about, then PinPIE will try to include ```/pages/about.php``` file. If it doesn't exist, path ```/pages/about/index.php``` will be checked.

If none of this found, then ```CFG::$pinpie['page not found']``` will be included, and HTTP response code will be set to 404. Default value of ```CFG::$pinpie['page not found']``` is ```index.php```, and it's strongly recommended to create a special page to handle 'not found' requests. Requested URL is not changed when 404 request is handled. The 'not found' page will be shown at requested URL. 

 If option ```CFG::$pinpie['route to parent']``` is defined in config and is greater than zero, then PinPIE will try to find some file, according to requested path. That means, if for URL ```/very/long/url``` there will be not found both ```/pages/very/long/url.php``` and ```/pages/very/long/url/index.php```, then searching path will be shortened for one step to ```/pages/very/long.php``` and ```/pages/very/long/index.php```.
 This operation will be repeated for maximum ```CFG::$pinpie['route to parent']``` times, and if no existing file will be found - the requested URL will be considered as "not found". If the first part of request URL ```/very``` is not found, request will not be routed to ```/pages/index.php```. It will be also considered as "not found". 
 
 This mechanics allow you to handle requests like ```/news/42``` or ```/news/42/edit``` in one file located at ```/pages/news.php``` or ```/pages/news/index.php```. Sure you can have ```/pages/news/index.php``` news listing and ```/pages/news/edit.php``` to edit one. 

# PinPIE file structure
Current project file structure:
```
/
├── chunks/                              folder is used to store text chunks
├── config/                              configuration files
├── filecache/                           used only if caching is file-based
├── pages/                               folder is used to store pages and URL handlers
├── pinpie/                              PinPIE files are located here
│   ├── classes/                         PinPIE entry point
│   │   ├── cache.class.*.php            caching class, selected by config option
│   │   ├── cfg.class.php                config loader, default values can be found here
│   │   ├── pinpie.class.php             main PinPIE code
│   │   └── staticon.class.php           static content methods here 
│   ├── pinpie.php                       PinPIE entry point
│   └── throw.php                        some functions used in PinPIE code
├── snippets/                            folder to store php-executable code pieces
└── templates/                           templates folder. Don't forget to create default.php template here.
```
All empty folders have empty 'dummy' file to make sure the folder will be created.


