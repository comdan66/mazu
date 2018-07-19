<?php
$connection = ssh2_connect('kerker.tw', 22);

$stream = ssh2_exec($connection, 'ls');
echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
var_dump ($stream);
exit ();