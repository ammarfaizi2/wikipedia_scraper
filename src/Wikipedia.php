<?php

namespace Wikipedia;

class Wikipedia
{
	const LANGURL = [
		"en" => "https://en.wikipedia.org/w/index.php?search={{:query}}&title=Special%3ASearch&go=Go"
	];

	private $lang;

	private $query;

	private $cookieFile;

	public function __construct($query, $lang = "en")
	{
		$this->query = $query;
		$this->lang  = $lang;
		if (! isset(self::LANGURL[$this->lang])) {
			throw new Exception("Invalid language code [{$this->lang}]");
		}
		if (is_dir("/tmp") && is_writable("/tmp")) {
			$this->cookieFile = "/tmp/wikipedia_cookie";
		} else {
			$this->cookieFile = getcwd()."/wikipedia_cookie";
		}
	}

	private function curl($url, $opt = [])
	{
		$ch = curl_init($url);
		$defOpt = [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_COOKIEJAR => $this->cookieFile,
				CURLOPT_COOKIEFILE => $this->cookieFile,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:56.0) Gecko/20100101 Firefox/56.0"
			];
		foreach ($opt as $key => $val) {
			$defOpt[$key] = $val;
		}
		curl_setopt_array($ch, $defOpt);
		$out = curl_exec($ch);
		if ($ern = curl_errno($ch)) {
			throw new Exception("Curl Error ({$ern}): ".curl_error($ch));
		}
		$info = curl_getinfo($ch);
		return [
			"out" => $out,
			"info" => $info
		];
	}

	public function search()
	{
		$ch = $this->curl(str_replace("{{:query}}", urlencode($this->query), self::LANGURL[$this->lang]));
		
		//file_put_contents("a.tmp", $ch['out']);
		//$ch["info"]["url"] = "https://en.wikipedia.org/wiki/Jack_the_Ripper";
		//$ch["out"] = file_get_contents("a.tmp");

		$result = [
			"title" => "",
			"url"	=> $ch["info"]["url"],
			"photos" => [],
			"prologue" => ""
		];
		if (preg_match("/<title>(.*)<\/title>/U", $ch['out'], $matches)) {
			$result["title"] = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
		}

		if (preg_match("/<\/table>.+<p>(.*)<\/p>/Us", $ch["out"], $matches)) {
			$result["prologue"] = $matches[1];
		}

		if (preg_match_all("/<div class=\"thumbinner\".+<img.+src=\"(.*\.jpg)\".+>.+<\/div>/Usi", $ch["out"], $matches)) {
			foreach ($matches[1] as $url) {
				// $result["photos"][] = "https:".$url;
			}
		}
		
		return $result;
	}
}
