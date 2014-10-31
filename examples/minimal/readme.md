# Minimal

Here are minimal files and config options to start.

Assuming site name is "example.com", so config have to be named "example.com.php".
 
## index.php
 In index.php you should include PinPIE. All request have to go to this file.
 
## Config
 There is no really obligatory config parameters, but remember to set $random_stuff to any random string. Otherwise you may have security and cache problems.
 
 *Read more about configuration in [config readme](../../docs/config.md).* 
 
## Template
 Template is required to show the general page output. Default template have to be named "default.php". Inside template is required to have ```[[*content]]``` tag.
 
 *Read more about templates in [template readme](../../docs/template.md).*