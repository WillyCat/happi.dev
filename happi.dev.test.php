<?php

require_once '../tinyHttp/tinyHttp.class.php';
require_once 'happi.dev.class.php';

function
cache_func (string $action, string $url, ?string $body = null): string|bool|null
{
	switch ($action)
	{
	case 'valid' : // true if in cache, false otherwise
		return false;
	case 'get' : // return content from cache or null if invalid or not available
		return null;
	case 'set' : // set cache
		return true;
	}
}

echo 'tinyHttp version: ' . tinyHttp::getVersion() . "\n";

$api_key = trim(file_get_contents ('key'));

echo "------------------" . "\n";
echo "SEARCH" . "\n";
echo "------------------" . "\n";

try
{
	$s = new happi\HappiLyricsSearch(key: $api_key, artist: 'michel sardou', track: 'afrique adieu', cache: 'cache_func');
	$lyrics = $s -> getLyrics();
} catch (Exception $e) {
	echo 'ERROR' . PHP_EOL;
	echo $e -> getMessage() . PHP_EOL;
	die();
}
$credits = $s -> getCredits();

echo 'Credits: ' . $credits . PHP_EOL;
echo PHP_EOL;

$i = 1;
foreach ($lyrics as $r)
{
	echo '#' . ($i++) . "\n";
	echo 'Artist: ' . $r['artist'] . PHP_EOL;
	echo 'Track: ' . $r['track'] . PHP_EOL;
	echo 'Lyrics: ' . $r['lyrics'] . PHP_EOL;
	echo 'Written by: ' . $r['written_by'] . PHP_EOL;
	echo 'copyright: ' . $r['copyright'] . PHP_EOL;
	echo PHP_EOL;
}

?>
