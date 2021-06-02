<?php

namespace happi;

/*
Version   Date        Change
--------  ----------  -----------------------------------------------------
0.1       2021-05-26  Initial version
*/

// a very simple implementation of Happi.dev music API
//
// Usage:

class Exception extends \Exception
{
}

class baseclass
{
	protected $api_key;

	public function
	__construct(string $api_key)
	{
		$this -> api_key = $api_key;
	}
}

class api extends baseclass
{
	protected array $args;
	public array $result;
	public \tinyUrl $u;
	public int $credits; // remaining (new credits every day)

	public function
	__construct (string $api_key, $argsDictionnary, ?array $argsValues = null)
	{
		parent::__construct($api_key);

		$this -> u = new \tinyUrl ();
		$this -> args = [ ];

		if (gettype ($argsDictionnary) == 'string')
		{
			$entrypoint = $argsDictionnary;
		}

		if (gettype ($argsDictionnary) == 'array')
		{
			if (is_null ($argsValues))
				throw new Exception ('argsValue cannot be null');

			$entrypoint = $this -> entrypoint;

			foreach ($argsDictionnary as $arg)
				if (array_key_exists ($arg, $argsValues))
					$this -> args[$arg] = $argsValues[$arg];
		}

		$this -> args['apikey'] = $api_key;
		$this -> u -> setUrl ($entrypoint);
		$this -> u -> setQuery($this -> args);

		$http = new \tinyHttp ();
		$http -> setUrl ($this -> u);

		$r = $http->send();

		//-------------
		// Headers
		//-------------
		$headers = $r -> getHeaders();
		// "x-server-time": 1622057391,
		// "x-ratelimit-remaining": 299,
		// "x-credits-free": 7999,
		// "x-ratelimit-reset": 1622057451,
		// "x-ratelimit-limit": 300,
		// "x-credits-premium": 0
		$credits = 0;
		if (array_key_exists ('x-credits-free', $headers))
			$credits += $headers['x-credits-free'];
		if (array_key_exists ('x-credits-premium', $headers))
			$credits += $headers['x-credits-premium'];
		$this -> credits = $credits;

		//------------
		// Body
		//------------
		// If failed
		//------------
		// "success": boolean,
		// "error": text
		//------------
		// If Success
		//------------

		$str = $r -> getBody();
		$a = json_decode ($str, true);
		if (!$a)
			throw new Exception ('could not decode reply');
		if (!array_key_exists ('success', $a))
			throw new Exception ('reply does not include success node');
		if ($a['success'] == false)
			throw new Exception ($a['error']);

		switch ($r -> getStatus() )
		{
		case 200: // ok
			$this -> result = $a['result'];
			break;

		case 404 : // entry point not found
		case 522 : // timed out
			throw new Exception ('HTTP Error ' . $r -> getStatus() );
			break;
		}
	}
}

class music extends api
{
	protected $entrypoint = 'https://api.happi.dev/v1/music';

	public function
	__construct (string $api_key, $argsDictionnary, ?array $argsValues = null)
	{
		parent::__construct($api_key, $argsDictionnary, $argsValues);
	}
}

class search extends music
{
	// 'https://api.happi.dev/v1/music?q=test&limit=10&apikey=(key)&type=track&lyrics=0';

	public function
	__construct (string $api_key, array $argsValues)
	{
		$argsDictionnary = [
			'q',
			'limit',
			'lyrics',
			'type'
		];

		parent::__construct ($api_key, $argsDictionnary, $argsValues);
	}
}

class lyrics extends music
{
	public function
	__construct (string $api_key, string $api_lyrics)
	{
		parent::__construct ($api_key, $api_lyrics);
	}

	public function
	getLyrics(): string
	{
		return $this -> result['lyrics'];
	}
}

?>
