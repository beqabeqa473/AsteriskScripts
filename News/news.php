#!/usr/bin/php -q
<?php
set_time_limit(30);
error_reporting(E_ALL);

require('phpagi.php');                
require('common_functions.php');
require_once 'Feed.php';

$agi = new AGI();
$callernum=$agi->get_variable("CALLERID(number)"); 
$callernum=$callernum['data'];
$curIndex = 0;
//$urls = array('https://news.yandex.ru/world.rss', 'https://news.yandex.ru/daily.rss', 'https://news.yandex.ru/index.rss');
$urls = array("https://news.yandex.ru/index.rss");
$news = array();

foreach ($urls as $url) {
try {
$rss = Feed::loadRss($url);
} catch (FeedException $e) {}
foreach ($rss->item as $item) {
$testarr = array();
$testarr['title'] = $item->title;
$testarr['description'] = $item->description;
$news[] = $testarr;
}
}
//shuffle($news);
$count = count($news);
$agi->stream_file(ttsSay("Добро пожаловать в сервис прослушивания новостей"));
$agi->stream_file(ttsSay("Всего новостей {$count}"));
$agi->stream_file(ttsSay("Для перемещения по новостям используйте клавиши 1-3. Для выхода в главное меню, нажмите решетку"));
switchNews();

function switchNews() {
global $agi, $count, $curIndex, $news;
if ($curIndex < 0) {
$curIndex = $count;
} else if ($curIndex > $count) {
$curIndex = 0;
}
$ttsText = $news[$curIndex]['title'] . ". " . $news[$curIndex]['description'];
$response = $agi->stream_file(ttsSay($ttsText), '13#');
$result = $response['result'];
switch ($result) {
case 49:
--$curIndex;
switchNews();
break;
case 51:
++$curIndex;
switchNews();
break;
case 35:
exit;
break;
default:
++$curIndex;
switchNews();
break;
}
}
?>
