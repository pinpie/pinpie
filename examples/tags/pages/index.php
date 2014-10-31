Hi! This are tags usage examples.<br>
<a href="../../docs/tags.md">Read more about tags</a><br>
<br>
<hr><br>
<h2>Chunk</h2>
<a href="../../docs/tags.md#chunk">Chunks</a> are non executable pieces of text located in *.php files inside /chunks folder. Please, open that folder and see the code inside files.<br>
Here goes &#91;&#91;foo&#93;&#93; chunk:<br>
[[foo]]<br>

<br>
<hr><br>
<h2>Snippet</h2>
<a href="../../docs/tags.md#snippet">Snippets</a> are executable pieces of php-code located in *.php files inside /snippets folder. Please, open that folder and see the code inside files.<br>
Here goes &#91;&#91;$bar&#93;&#93; snippet:<br>
[[$bar]]<br>
Snippet $bar is to produce random greeting from ['Hi!', 'Hello!', 'Bonjour!', 'Guten tag!', ...etc] array. If "/filecache" folder is writable, &#91;&#91;$bar&#93;&#93; output will be cached and the number will never change.<br>

<br>
<hr><br>
<h2>Caching</h2>
<a href="../../docs/tags.md#snippet-caching">Caching allow you to lower load to your server.</a><br>
Here goes some caching examples of &#91;&#91;$rand&#93;&#93; snippet.<br>
&#91;&#91;$rand&#93;&#93; : [[$rand]] &mdash; will be cached forever an will be always the same<br>
&#91;&#91;<b>5</b>$rand&#93;&#93; : [[5$rand]] &mdash; will be cached for every five seconds<br>
&#91;&#91;<b>!</b>$rand&#93;&#93; : [[!$rand]] &mdash; will be never cached<br>
<br>
Please now keep refreshing the page, and see the snippet caching working.<br>

<br>
<hr><br>
<h2>Tag templates</h2>
Now meet usually unused possibility to apply template to tags. <a href="../../template.md">Read more about templates</a><br>

&#91;&#91;foo&#93;colorand&#93; : [[foo]colorand]<br>
&#91;&#91;5foo&#93;colorand&#93; : [[5foo]colorand]<br>
&#91;&#91;3foo&#93;colorand&#93; : [[3foo]colorand]<br>
&#91;&#91;!foo&#93;colorand&#93; : [[!foo]colorand]<br>


<br>
&#91;&#91;$bar&#93;colorand&#93; : [[$bar]colorand]<br>
&#91;&#91;5$bar&#93;colorand&#93; : [[5$bar]colorand]<br>
&#91;&#91;3$bar&#93;colorand&#93; : [[3$bar]colorand]<br>
&#91;&#91;!$bar&#93;colorand&#93; : [[!$bar]colorand]<br>
<br>
You know what to do. Press F5 and do not release it for a while.<br>

<br>
<hr><br>
<h2>Variable placeholder</h2>
<pre class="code">&#91;greeting-girl&#91;!$bar&#93;&#93; &#91;greeting-boy&#91;!$bar&#93;&#93; Little girl said "&#91;&#91;*greeting-girl&#93;&#93; &lt;br&gt; And boy said "&#91;&#91;*greeting-boy&#93;&#93;"
</pre><br>
This code will produce:<br>
<span class="output">
[greeting-girl[!$bar]] [greeting-boy[!$bar]] Little girl said "[[*greeting-girl]]"<br>
And boy said "[[*greeting-boy]]"<br>
</span>
<br>
Please, press F5 some times again.<br>
<br>


<br>
<hr><br>
<h2>Constant</h2>
Constant is just a line of text, that will go to output. It is usefull to send some small text frome page to template.<br>
<h3>Example №1</h3>
In page:<br>
<pre class="code">&#91;heading&#91;=An example&#93;&#93;</pre><br>
In template:<br>
<pre class="code">&lt;b&gt;&#91;&#91;*heading&#93;&#93;&lt;/b&gt;</pre><br>
This code will produce:<br>
<span class="output">
[heading[=An example]] <b>[[*heading]]</b><br>
</span>
<br>
<br>
<h3>Example №2</h3>
You can use placeholder for many times at the same page.<br>
<pre class="code">&lt;?php $username = "Alice"; echo "&#91;username&#91;=$username&#93;&#93;"; </pre><br>
Will create constant with user name in it:<br>
<pre class="code">&#91;username&#91;=Alice&#93;&#93;</pre><br>
And it can be used multiple times:<br>
<pre class="code">Hi &#91;&#91;*username&#93;&#93;! I love you &#91;&#91;*username&#93;&#93;!</pre><br>
Output:<br>
<?php $username = "Alice";
echo "[username[=$username]]"; ?>
<span class="output">
  Hi [[*username]]! I love you [[*username]]!
</span>
