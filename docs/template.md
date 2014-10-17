# Template

PinPIE allow to use different templates for pages and tags. Templates are allocated in ```/templates/``` folders. Templates have to be in ```*.php``` files. Template can have a php code inside. It will be executed. Templates have no caching. If you have heavy PHP code need to be cached &mdash; put it to cacheble snippet.

PinPIE templates provides very primitive options and are not a replacement for template engines.


## Page templates
Default template have to be named as ```/templates/default.php```. This template is used if no template directive fount in page output. If template file is not found, default template will **not** be used and empty page will be produced.

Template usage is simple. You have to use template [command](tags.md#command) tag: ```[[@template=name]]``` where name is filename of your template without ```.php``` extension.

Inside template code have to be a [placeholder](tags.md#variable-placeholder) ```[[*content]]```. It will be replaced by page output.

To use raw pain text output you can create a template file with only one tag ```[[*content]]``` inside and select this template.

From the PHP code it is more convenient way to force really raw output is to set current template to ```false```. You can call ```PinPIE::setTemplate(false);``` or just ```PinPIE::setTemplate();``` because by defalt 

### Example №1
Create default template with file name ```default.php``` in /templates folder and this code inside the file:
```
<html>
  <head>
    <title>This is sample default temple</title>
  </head>
  <body>
    [[*content]]
  </body>
</html>
```
This template will be used by default if no template selected in page. Any page output will be placed between \<body\> and \</body\>. 

### Example №2
Create a template with a different file name e.g. ```wide.php``` in /templates folder and this code inside the file:
```
<html>
  <head>
    <title>This is another temple</title>
  </head>
  <body>
    [[*content]]
  </body>
</html>
```

Create a page with code:
```
[[@template=wide]]
```
This code will tell PinPIE to use ```/templates/wide.php``` as template for this page.

### Example №3
This example demonstrates template variables usage. Lets make possible to set custom title right on the page.

Create a page with code:
```
[title[=Custom title]]
```
This will put text "Custom title" to a variable named "title". And a template have to have a variable placeholder ```[[*title]]```.
Template code:
```
<html>
  <head>
    <title>[[*title]]</title>
  </head>
  <body>
    [[*content]]
  </body>
</html>
```
 
PinPIE will replace ```[[*title]]``` tag in template with a content of variable.

## Tag templates
PinPIE allow to apply template to a tag output as well. The template for tag will require ```[[*tagcontent]]``` placeholder. Tag template have to be located in ```/templates/``` folder.

To apply template to chunk or snippet output you need just put its name before second closing bracket: ```[[$snippet]template]```

### Example
To wrap snippet output to a ```div``` you need create template named e.g. "wrap" with code: ```<div>[[*tagcontent]]</div>```. And now you can apply this template to a snippet like this: ```[[$snippet]wrap]``` with code e.g. ```<?php echo rand(1,100);``` will output ```<div>42</div>```.
