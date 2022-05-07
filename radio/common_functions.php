<?php
function ttsSay($text) {
$rootPath = '/var/lib/asterisk/sounds/custom/admin/';
$md5 = md5($text);
$pFile = $rootPath.$md5;
$dFile = $pFile.'.wav';
if (!file_exists($dFile)) {
shell_exec("echo $text | RHVoice-test -p Anna -o - | sox - -c 1 -r 8000 $dFile");
}
return $pFile;
}
?>
