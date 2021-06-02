<?php

require_once '../tinyHttp/tinyHttp.class.php';
require_once 'happi.dev.class.php';

$api_key = trim(file_get_contents ('key'));

echo "------------------" . "\n";
echo "SEARCH" . "\n";
echo "------------------" . "\n";

$s = new happi\search($api_key, [
	'q' => 'michel sardou afrique adieu',
	'type' => 'track'
]);

echo 'URL: ' . $s -> u . "\n";
$i = 1;
foreach ($s -> result as $r)
{
	echo '#' . ($i++) . "\n";
	echo 'Artist: ' . $r['artist'] . ' (id: '.$r['id_artist'] . ')' . "\n";
	echo 'Album: ' . $r['album'] . ' (id: '.$r['id_album'] . ')' . "\n";
	echo 'Track: ' . $r['track'] . ' (id: '.$r['id_track'] . ')' . "\n";
	echo 'Cover: ' . $r['cover'] . "\n";
	echo 'Has lyrics: ' . ($r['haslyrics']?'yes':'no') . "\n";
	echo 'api_lyrics: ' . ($r['api_lyrics']) . "\n";
	echo "\n";

	if ($r ['haslyrics'])
		$api_lyrics = $r['api_lyrics'];
}
echo 'Credits: ' . $s -> credits . "\n";

echo "------------------" . "\n";
echo "LYRICS" . "\n";
echo "------------------" . "\n";

$l = new happi\lyrics($api_key, $api_lyrics);
echo $l -> getLyrics() . "\n";
echo 'Credits: ' . $s -> credits . "\n";

// https://api.happi.dev/v1/music/artists/2806/albums/425667/tracks/6440082/lyrics
// &apikey=8c10a65xBarfkAz4Bg6kMqJ9rJ52tEA4qBnjAyFiRCqYNPzp5I6FC5nM

?>
