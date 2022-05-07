#!/usr/bin/php -q
<?php
include('connect.php');
set_time_limit(30);
error_reporting(E_ALL);

require('phpagi.php');
require('common_functions.php');

$agi = new AGI();
$callernum=$agi->get_variable("CALLERID(number)"); 
$callernum=$callernum['data'];

$perms=mysqli_query($con, "SELECT admin FROM `phonebook` WHERE `number` = '$callernum' LIMIT 1");
$a_perms=mysqli_fetch_array($perms);
$admin=$a_perms['admin'];
$row=mysqli_query($con, "SELECT station FROM radio WHERE channel = '$callernum' LIMIT 1");
$a_row=mysqli_fetch_array($row);
$station_number_from_base=$a_row['station'];

$rows=mysqli_query($con, "SELECT COUNT(*) FROM bit_musiconhold WHERE number < 1000");
$b_row=mysqli_fetch_array($rows);
$r_number=$b_row[0];
if (mysqli_num_rows($row) > 0) {
$station_number = $station_number_from_base;
} else {
$station_number = 1;
$a_sql=mysqli_query($con, "INSERT INTO radio (channel, station) VALUES('$callernum','$station_number')");
}
if ($argv[1] == 'back') {
switchRadio(--$station_number);
} else if ($argv[1] == 'next') {
switchRadio(++$station_number);
} else if ($argv[1] == 'last') {
$agi->stream_file(ttsSay("В нашем радио приёмнике " . $r_number . "станций"));
switchRadio($station_number);
} else if ($argv[1] == 'search') {
search();
}

function switchRadio($index) {
global $agi, $callernum, $con, $r_name, $r_number;
if ($index < 1) {
$index = $r_number;
} else if ($index > $r_number) {
if ($index != 2323) {
$index = 1;
}
//$index = 1;
}
$n_row=mysqli_query($con, "SELECT name, full_name FROM bit_musiconhold WHERE number = '$index' LIMIT 1");
$st_row=mysqli_fetch_array($n_row);
$r_name=$st_row['name'];
$r_fname=$st_row['full_name'];	
$response = $agi->stream_file(ttsSay($index . ' - ' . $r_fname), "123");
$result = $response['result'];
switch ($result) {
case 49:
switchRadio(--$index);
break;
case 50:
search();
break;
case 51:
switchRadio(++$index);
break;
}
$c_sql=mysqli_query($con, "UPDATE radio SET station='$index' WHERE channel='$callernum'");

mysqli_close($con);
}

function search() {
global $admin, $agi, $callernum, $con, $r_number, $station_number;
$response = $agi->get_data(ttsSay("наберите номер желаемой станции и нажмите решетку"), 1000000, 10);
// make a mysql check if this id exists or make cancelation on pressing star.
$result = $response['result'];
$result = preg_replace('/\D/', '', $result);
if ($result > $r_number) {
if ($result == 2323 && $admin == 'yes') {
$agi->stream_file(ttsSay("Специальная радиостанция"));
$station_number = $result;
} else {
$agi->stream_file(ttsSay("Станции с таким номером не существует"));
}
} else {
$station_number = $result;
}
switchRadio($station_number);
}

$agi->set_music(false);
$agi->set_variable("CHANNEL(musicclass)", $r_name);
$agi->set_music(true,$r_name);
?>
