#!/usr/bin/php -q
<?php
set_time_limit(30);
error_reporting(E_ALL);

require('phpagi.php');                

// make announcement of all cases, also announcement of cases in russia, maybe parse the government site, announcement of all countries cases, maybe voice recognition to find countries, convert updated time, find a way to map russian and english country names.

$agi = new AGI();
$callernum=$agi->get_variable("CALLERID(number)"); 
$callernum=$callernum['data'];

$agi->stream_file(ttsSay("Добро пожаловать в сервис отслеживания новостей по корона вирусу!"));
processKey();

function processKey() {
global $agi;
$response = $agi->stream_file(ttsSay("Что бы узнать статистику во всем мире, нажмите 1. Для того что бы узнать статистику в россии, нажмите 2"), '123#');
$result = $response['result'];
switch ($result) {
case 49:
$resp = file_get_contents("https://corona.lmao.ninja/v2/all", false);
$json = json_decode($resp, true);
setlocale(LC_TIME, "ru_RU.utf-8");
$formattedDate = strftime('%A, %e %B %Y %H:%M:%S', floor($json['updated']/1000));
$cases = $json['cases'];
$recovered = $json['recovered'];
$deaths = $json['deaths'];
$active = $json['active'];
$affectedCountries = $json['affectedCountries'];
$ttsText = "По данным на $formattedDate - Всего зараженных: $cases. Выздоровевших: $recovered. Умерших: $deaths. Активных случаев: $active. Всего пострадавших стран: $affectedCountries";
$agi->stream_file(ttsSay($ttsText));
break;
case 50:
$resp = file_get_contents("https://corona.lmao.ninja/v2/countries/russia", false);
$json = json_decode($resp, true);
setlocale(LC_TIME, "ru_RU.utf-8");
$formattedDate = strftime('%A, %e %B %Y %H:%M:%S', floor($json['updated']/1000));
$cases = $json['cases'];
$todayCases = $json['todayCases'];
$recovered = $json['recovered'];
$deaths = $json['deaths'];
$todayDeaths = $json['todayDeaths'];
$active = $json['active'];
$critical = $json['critical'];
$ttsText = "По данным на $formattedDate - в россии Всего зараженных: $cases. Зараженных сегодня: $todayCases. Выздоровевших: $recovered. Умерших: $deaths. Умерших сегодня: $todayDeaths. Активных случаев: $active. Критических случаев: $critical.";
$agi->stream_file(ttsSay($ttsText));
break;
case 35:
exit;
break;
default:
break;
}
}

function ttsSay($text) {
$rootPath = '/var/lib/asterisk/sounds/custom/admin/';
$md5 = md5($text);
$dFile = $rootPath.$md5.'.wav';
if (!file_exists($dFile)) {
shell_exec("echo $text | RHVoice-test -p Anna+Slt -o - | sox - -c 1 -r 8000 $dFile");
}
$pathParts = pathinfo($dFile);



return $pathParts['dirname']."/".$pathParts['filename'];
}
?>