<?php

namespace Wikipedia;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package Wikipedia
 */
class Wikipedia
{
	/**
	 * @const array
	 */
	const LANGLIST = [
		"ar" => "العربية",
		"az" => "Azərbaycanca",
		"bg" => "Български",
		"nan" => "Bân-lâm-gú / Hō-ló-oē",
		"be" => "Беларуская (Акадэмічная)",
		"ca" => "Català",
		"cs" => "Čeština",
		"da" => "Dansk",
		"de" => "Deutsch",
		"et" => "Eesti",
		"el" => "Ελληνικά",
		"en" => "English",
		"es" => "Español",
		"eo" => "Esperanto",
		"eu" => "Euskara",
		"fa" => "فارسی",
		"fr" => "Français",
		"gl" => "Galego",
		"ko" => "한국어",
		"hy" => "Հայերեն",
		"hi" => "हिन्दी",
		"hr" => "Hrvatski",
		"id" => "Bahasa Indonesia",
		"it" => "Italiano",
		"he" => "עברית",
		"ka" => "ქართული",
		"la" => "Latina",
		"lt" => "Lietuvių",
		"hu" => "Magyar",
		"ms" => "Bahasa Melayu",
		"min" => "Bahaso Minangkabau",
		"nl" => "Nederlands",
		"ja" => "日本語",
		"no" => "Norsk (Bokmål)",
		"nn" => "Norsk (Nynorsk)",
		"ce" => "Нохчийн",
		"uz" => "Oʻzbekcha / Ўзбекча",
		"pl" => "Polski",
		"pt" => "Português",
		"kk" => "Қазақша / Qazaqşa / قازاقشا",
		"ro" => "Română",
		"ru" => "Русский",
		"simple" => "Simple English",
		"ceb" => "Sinugboanong Binisaya",
		"sk" => "Slovenčina",
		"sl" => "Slovenščina",
		"sr" => "Српски / Srpski",
		"sh" => "Srpskohrvatski / Српскохрватски",
		"fi" => "Suomi",
		"sv" => "Svenska",
		"ta" => "தமிழ்",
		"th" => "ภาษาไทย",
		"tr" => "Türkçe",
		"uk" => "Українська",
		"ur" => "اردو",
		"vi" => "Tiếng Việt",
		"vo" => "Volapük",
		"war" => "Winaray",
		"zh" => "中文"
	];

	/**
	 * @var string
	 */
	private $lang;

	/**
	 * @var string
	 */
	private $query;

	/**
	 * @var string
	 */
	private $cookieFile;

	/**
	 * Constructor.
	 *
	 * @param string $query
	 * @param string $lang
	 * @return void
	 */
	public function __construct($query, $lang = "en")
	{
		$this->query = strtolower(trim($query));
		if ($this->query === "") {
			throw new Exception("Empty query", 1);
		}
		$this->lang  = $lang;
		if (! isset(self::LANGLIST[$this->lang])) {
			throw new Exception("Invalid language code [{$this->lang}]");
		}
		if (is_dir("/tmp") && is_writable("/tmp")) {
			$this->cookieFile = "/tmp/wikipedia_cookie";
		} else {
			$this->cookieFile = getcwd()."/wikipedia_cookie";
		}
	}

	/**
	 * @throws \Exception
	 * @param string $url
	 * @param array  $opt
	 * @return array
	 */
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

	/**
	 * @return array
	 */
	public function search()
	{
		$ch = $this->curl("https://{$this->lang}.wikipedia.org/w/index.php?search=".urlencode($this->query)."&title=Special%3ASearch&go=Go");
		
		// debug only
		// file_put_contents("a.tmp", $ch['out']);
		// $ch["info"]["url"] = "https://en.wikipedia.org/wiki/Jack_the_Ripper";
		// $ch["out"] = file_get_contents("a.tmp");

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

		if (preg_match_all("/<div class=\"thumbinner\".+<img.+src=\"(.*)\".+>.+<\/div>/Usi", $ch["out"], $matches)) {
			foreach ($matches[1] as $url) {
				$url = "https:".$url;
				if (filter_var($url, FILTER_VALIDATE_URL)) {
					$result["photos"][] = $url;
				}
			}
		}
		
		return $result;
	}
}
