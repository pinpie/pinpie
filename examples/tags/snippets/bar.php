<?php
$greeting = ['Hi!', 'Hello!', 'Bonjour!', 'Guten tag!', 'Hola!', 'Ola!', 'Saluton!', 'Hei!', 'Γειά!', 'Jó napot kívánok!', 'Ave!', '今日は!', 'السلام عليكم', 'God dag!', 'สวัสดี!', 'Ciao!', 'Sawubona!',];
echo $greeting[rand(0, count($greeting) - 1)];