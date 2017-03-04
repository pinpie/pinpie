[![PinPIE](https://img.shields.io/badge/PHP-PinPIE-brightgreen.svg)](http://pinpie.ru/)
[![Latest Stable Version](https://img.shields.io/packagist/v/pinpie/pinpie.svg)](https://packagist.org/packages/pinpie/pinpie)
[![Total Downloads](https://img.shields.io/packagist/dt/pinpie/pinpie.svg)](https://packagist.org/packages/pinpie/pinpie)
[![codecov](https://codecov.io/gh/pinpie/pinpie/branch/stable/graph/badge.svg)](https://codecov.io/gh/pinpie/pinpie)
[![Build Status](http://img.shields.io/travis/pinpie/pinpie.svg)](https://travis-ci.org/pinpie/pinpie)
[![Code Climate](https://codeclimate.com/github/pinpie/pinpie/badges/gpa.svg)](https://codeclimate.com/github/pinpie/pinpie)
[![License](https://img.shields.io/packagist/l/pinpie/pinpie.svg)](https://packagist.org/packages/pinpie/pinpie)

#PinPIE - when PHP Is Enough

## About
PinPIE is lightweight php-based engine for small sites

Read more about PinPIE engine in [PinPIE docs](http://pinpie.ru)

## Overview

PinPIE is not a framework, nor it is a CMS. PinPIE is a site engine, designed to be quick and efficient even on cheap hostings.


PinPIE stores all contend in php-files.
If opcode cacher is used &mdash; it will cache this files.
That allows PinPIE to include pages, snippets and chunks in the blink of an eye.


Content stored in files allows you to edit your content using favorite IDE or text editor with all that highlighting, auto-formatting, auto-saving, auto-uploading features and familiar hotkeys. Also that allows to benefit from full debug support including exact line numbers and IDE code execution flow controls.
This approach is friendly to version control systems — you can have versions of all your content to be safe and protected against loosing something while editing anything. Deployment friendly. Backup friendly.


PinPIE have tag-based parser. Tag syntax is inspired by [ModX](https://modx.com/) tag system.
Basic tags are:

- Chunks — a pieces of plain text
- Snippets — a pieces of php code to execute

Read more in [tags readme](http://pinpie.ru/en/manual/tags).

PinPIE provide clear and controllable automatic snippet caching.
Caching can be enabled or disabled for each snippet tag separately.
Caching control is predictable and simple.

This is a snippet tag. Snippet is a piece of PHP code. This snippet is executed for every request. It is not cached.

```
[[$snippet]]
```

And here is the example of cached snippet syntax:

```
[[60$snippet]]
```

Look at this one. It is cached for one minute. If snippet file or one of files of its children is changed, PinPIE will execute and recache this snippet automatically.

To cache snippet forever, just use that syntax:

```
[[!$snippet]]
```

That snippet will be cached for PHP_INT_MAX seconds, which is a lot.

You don't need to purge cache yourself every time you change something important on the site. PinPIE will automatically recache only changed content. But anyway, you can purge the cache if you want.
Read more in [cache readme](http://pinpie.ru/en/manual/cache).

Read more about PinPIE engine in [PinPIE docs](http://pinpie.ru).



##Examples

Look at this example of some possible page:

```HTML
<!-- A title text. It goes to placeholder in the template -->
[title[=Hello]]

<p>Hi!</p>

<!-- A snippet of PHP code, it outputs some random number each time page is rendered. -->
<p>The answer is [[$rand]].</p>

<!-- A static tag. It will be rendered as <img... with width and height (optional), see below -->
[[%img=/images/cat.jpg]]

<p>Now visit <a href="/about">another page</a>.</p>

<!-- A chunk tag. Piece of plain text which can be used anywhere. -->
[[lorem/ipsum]]
```

After page is processed, its HTML code will look like that:

```HTML
...
<!-- Article and header are located in the template.
 Title was set in the template with a placeholder.
 You can find template code below. -->
<article>
  <header>
    <h1>Hello</h1>
  </header>
  
<!-- A title text. It is now above this line. -->

<p>Hi!</p>

<!-- A snippet of PHP code generated a number. -->
<p>The answer is 453.</p>

<!-- A static tag become an image with a hash preventing caching changed files.
  That hash will remain the same until file is changed. -->
<img src="//test.ru/images/cat-1.jpg?time=d9c8899d5833a0616ad2aef0bc2229cd" width="640" height="427">

<p>Now visit <a href="/about">another page</a>.</p>

<!-- A chunk tag. Piece of plain text which can be used anywhere. -->
<p>Lorem ipsum dolor sit amet...
```

Here the `[title[=Hello]]` is a constant with some text. In this example it goes into the placeholder `[[*title]]`. That placeholder is used in the `<h1>` tag, and in the same time let's assume it is used also in the `<title>` tag in the template. In that way, this text will appear on the page as a heading and in the head of the page in title.

The snippet `[[$rand]]` will run PHP code from file `/snippets/rand` and will output a random number.

The static tag `[[%img=/images/someimage.jpg]]` is very convenient way to use images, css and js files on the page. It will automatically output something like:
```HTML
<img src="//test.ru/images/someimage.jpg?time=4134cb552b4782d97e3450bfa42eb049" width="640" height="427">
```
You can see a `time` hash to make changed static files updated in browser.  
Also PinPIE add the image width and height automatically. 
This behavior can be changed in config.

Chunk `[[lorem/ipsum]]` is just a piece of text in a `/chunks/lorem/ipsum` folder.

You can find more examples in [other PinPIE repos](https://github.com/pinpie) or at [PinPIE site](http://pinpie.ru/en/examples).

## Start using PinPIE
You can install PinPIE with composer:

```
composer require "pinpie/pinpie"
composer install
```

Or download code from GitHub and use standalone autoloader file `/pinpie/src/autoload.php`.

You can find more detailed instructions in [start unsing PinPIE](http://pinpie.ru/en/manual/start) docs.



## Credits
Author and maintainer of PinPIE is Igor Data ([@igordata](https://github.com/igordata))
