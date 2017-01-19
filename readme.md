[![PinPIE](https://img.shields.io/badge/PHP-PinPIE-brightgreen.svg?style=flat-square)](http://pinpie.ru/)
[![Build Status](http://img.shields.io/travis/pinpie/pinpie.svg?style=flat-square)](https://travis-ci.org/pinpie/pinpie)
[![Latest Stable Version](https://img.shields.io/packagist/v/pinpie/pinpie.svg?style=flat-square)](https://packagist.org/packages/pinpie/pinpie)
[![Total Downloads](https://img.shields.io/packagist/dt/pinpie/pinpie.svg?style=flat-square)](https://packagist.org/packages/pinpie/pinpie)
[![License](https://img.shields.io/packagist/l/pinpie/pinpie.svg?style=flat-square)](https://packagist.org/packages/pinpie/pinpie)
[![codecov](https://codecov.io/gh/pinpie/pinpie/branch/stable/graph/badge.svg?style=flat-square)](https://codecov.io/gh/pinpie/pinpie)

#PinPIE - when PHP Is Enough

## About
PinPIE is lightweight php-based engine for small sites

Read more about PinPIE engine in [PinPIE docs](http://pinpie.ru).


## Overview
<p>
PinPIE is not a framework, nor it is a CMS. PinPIE is a site engine, designed to be quick and efficient even on cheap hostings.
</p>
<p>
PinPIE stores all contend in php-files.
If opcode cacher is used &mdash; it will cache this files.
That allows PinPIE to include pages, snippets and chunks in the blink of an eye.
</p>
<p>
Content stored in files allows you to edit your content using favorite IDE or text editor with all that highlighting, auto-formatting, auto-saving, auto-uploading features and familiar hotkeys. Also that allows to benefit from full debug support including exact line numbers and IDE code execution flow controls.
This approach is friendly to version control systems — you can have versions of all your content to be safe and protected against loosing something while editing anything. Deployment friendly. Backup friendly.
</p>
<p>
PinPIE have tag-based parser. Tag syntax is inspired by <a href="https://modx.com/">ModX</a> tag system.
Basic tags are:</p>
<ul>
<li>Chunks — a pieces of plain text</li>
<li>Snippets — a pieces of php code to execute</li>
</ul>
<p>Read more in <a href="/en/manual/tags">tags readme</a>.</p>
<p>
PinPIE provide clear and controllable automatic snippet caching.
Caching can be enabled or disabled for each snippet tag separately.
Caching control is predictable and simple.

This is a snippet tag. Snippet is a piece of PHP code. This snippet is executed for every request. It is not cached.

    [[$snippet]]

And here is the example of cached snippet syntax:

    [[60$snippet]]
    
Look at this one. It is cached for one minute. If snippet file or one of files of its children is changed, PinPIE will execute and recache this snippet automatically.

To cache snippet forever, just use that syntax:

    [[!$snippet]]
    
That snippet will be cached for PHP_INT_MAX seconds, which is a lot.

You don't need to purge cache yourself every time you change something important on the site. PinPIE will automatically recache only changed content. But anyway, you can purge the cache if you want.
Read more in <a href="/en/manual/cache">cache readme</a>.
</p>

Read more about PinPIE engine in [PinPIE docs](http://pinpie.ru).

## Start using PinPIE
You can install PinPIE with composer or download code from GitHub. You can find more detailed instructions in [start unsing PinPIE](http://pinpie.ru/en/manual/start) docs.

Read more about PinPIE engine in [PinPIE docs](http://pinpie.ru).

## Credits
Author and maintainer of PinPIE is Igor Data ([@igordata](https://github.com/igordata))
