<?php

namespace happi;

/*
Version   Date        Change
--------  ----------  -----------------------------------------------------
0.1       2021-05-26  Initial version
0.2       2022-07-23  bug fix in error handling
*/

// a very simple implementation of Happi.dev music API
//
// Usage:

class HappiException extends \Exception
{
}

class baseclass
{
	protected string $api_key;
	protected string $baseUrl;

	public function
	__construct(string $api_key)
	{
		$this -> baseUrl = 'https://api.happi.dev';
		$this -> api_key = $api_key;
	}
}

class HappiRequest extends baseclass
{
	protected array $args;
	public array $result;
	public \tinyUrl $u;
	public ?float $credits; // null=unknown
	public string $body;
	public ?string $cache;

	public function
	__construct (string $key, ?string $cache = null)
	{
		parent::__construct($key);
		$this -> credits = null; // unknown
		$this -> cache = $cache;
	}

	public function
	query (string $endpoint, array $args = [ ]): void
	{
		$this -> u = new \tinyUrl ();
		$this -> args = $args;

		$this -> u -> setUrl ($this->baseUrl . $endpoint);
		$this -> u -> setQuery($this -> args);

		if (!is_null ($this -> cache))
		{
			$body = call_user_func ($this -> cache, 'get', $this -> u -> getUrl());
			if (!is_null ($body))
			{
				$this -> body = $body;
				$this -> credits = null;
				return;
			}
		}

		$http = new \tinyHttp ();
		$http -> setHeader ('x-happi-token', $this -> api_key);
		$http -> setHeader ('accept', 'application/json');
		$http -> setUrl ($this -> u);

		$r = $http->send();

		switch ($r -> getStatus() )
		{
		case 200: // ok
			$this -> body = $r -> getBody();
			if (!is_null ($this -> cache))
				call_user_func ($this -> cache, 'set', $this -> u -> getUrl(), $this -> body);
			break;

		case 400 :
			$this -> body = $r -> getBody();
			$a = json_decode ($this -> body, true);
			if (array_key_exists ('message', $a))
				throw new HappiException ('Happi error: ' . $a['message']);
			else
				throw new HappiException ('Happi error with no message');
			break;

		default :
			throw new HappiException ('HTTP Error ' . $r -> getStatus() );
			break;
		}
		//-------------
		// Headers
		//-------------
		$headers = $r -> getHeaders();
		// "x-happi-credits": 24.950
		// "x-happi-users": 515
		$this -> credits = $headers['x-happi-credits-remaining'];
	}

	public function
	getCredits(): ?float // null means unknown
	{
		return $this -> credits;
	}
}

class HappiLyricsSearch extends HappiRequest
{
	public array $results;

	public function
	__construct (string $key, string $artist, string $track, ?string $cache = null)
	{
		parent::__construct (key: $key, cache: $cache);
		$this -> query ('/v1/lyrics', [ 'artist' => $artist, 'track' => $track ]);
		$a = json_decode ($this -> body, true);
		$this -> results = $a['result'];
	}

	public function
	getLyrics(): array
	{
		return $this -> results;
	}
}

?>
