<?php

$tekst = '
[align=center]Dzień 24[/align]
[b]Elo[/b]';
$subject = 'API post';

$tekst = mb_convert_encoding($tekst, 'HTML-ENTITIES', "UTF-8");

$url = 'http://forum.wikingowie.org/create-post-api.php';
$data = array(
	"user_id"=> "70",
	"username"=> "Los",
	"message"=> $tekst,
	"subject"=>  mb_convert_encoding($tekst, 'HTML-ENTITIES', "UTF-8"),
	"forum_id"=> "10",
	"topic_id"=> "20",
    "token" => ''
);

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data),
    ],
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
if ($result === false) {
    /* Handle error */
}

var_dump($result);
