<?php

$url = "http://api.openweathermap.org/data/2.5/weather";
$options = array(
"q" => "Moscow",
"APPID" => "fcd8c69769c16263fda143737d57f5a5",
"units"=> "metric",
"lang" => "en",
);

$ch = curl_init();
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch, CURLOPT_URL, $url.'?'.http_build_query($options));

$response = curl_exec ($ch);
$data = json_decode ($response, true);
curl_close ($ch);

echo '‹pre>'; 
print_r($data);