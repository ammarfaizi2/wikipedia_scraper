<?php

namespace Wikipedia;

class Wikipedia
{
	const LANGURL = [
		"en" => "https://en.wikipedia.org/wiki/Main_Page"
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
			$this->cookieFile = "/tmp/wikipedia";
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

	private function getMainPage()
	{

	}

	public function search()
	{
		$this->getMainPage();
	}
}
