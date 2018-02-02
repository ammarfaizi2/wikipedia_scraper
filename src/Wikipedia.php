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
	 * @const
	 *
	 * 2592000 = 30 days
	 */
	const CACHE_EXPIRED = 2592000;

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
	private $cacheDir;

	/**
	 * @var string
	 */
	private $cacheFile;

	/**
	 * @var string
	 */
	private $cookieFile;

	/**
	 * @var string
	 */
	private $checkSumFile;

	/**
	 * @var array
	 */
	private $cacheResult = [];

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
		$this->lang  = strtolower($lang);
		if (! isset(self::LANGLIST[$this->lang])) {
			throw new Exception("Invalid language code [{$this->lang}]");
		}
		if (defined("WIKIPEDIA_DATA_DIR")) {
			is_dir(WIKIPEDIA_DATA_DIR) or mkdir(WIKIPEDIA_DATA_DIR);
			$this->cacheDir	= WIKIPEDIA_DATA_DIR."/wikipedia_cache";
			$this->cookieFile = WIKIPEDIA_DATA_DIR."/wikipedia_cookie";
		} elseif (is_dir("/tmp") && is_writable("/tmp")) {
			$this->cacheDir	= "/tmp/wikipedia_cache";
			$this->cookieFile = "/tmp/wikipedia_cookie";
		} else {
			$cwd = getcwd();
			$this->cacheDir	= $cwd."/wikipedia_cache";
			$this->cookieFile = $cwd."/wikipedia_cookie";
		}
		is_dir($this->cacheDir) or mkdir($this->cacheDir);
		$this->hash = sha1($this->query);
		$this->cacheDir	.= "/".$this->hash;
		$this->cacheFile = $this->cacheDir."/".$this->lang;
		$this->checkSumFile = $this->cacheDir."/"."/checksum";
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
	 * @return bool
	 */
	private function hasCachedData()
	{
		if (is_dir($this->cacheDir) && file_exists($this->cacheFile) && file_exists($this->checkSumFile)) {
			$checkSum = json_decode(file_get_contents($this->checkSumFile), true);
			if (isset($checkSum[$this->lang])) {
				$this->cacheResult = json_decode(file_get_contents($this->cacheFile), true);
				return is_array($this->cacheResult) && isset($this->cacheResult["created_at"], $this->cacheResult["data"]) && (strtotime($this->cacheResult["created_at"]) + self::CACHE_EXPIRED) > time() && sha1_file($this->cacheFile) === $checkSum[$this->lang];
			}
		}
	
		return false;
	}

	/**
	 * @return array
	 */
	private function getCachedData()
	{
		return $this->cacheResult["data"];
	}

	/**
	 * @param array $result
	 * @return void
	 */
	private function writeCache($result)
	{
		is_dir($this->cacheDir) or mkdir($this->cacheDir);
		$handle = fopen($this->cacheFile, "w");
		flock($handle, LOCK_EX);
		fwrite($handle, 
			json_encode(
				[
					"created_at" => date("Y-m-d H:i:s"),
					"data" => $result
				]
			)
		);
		fclose($handle);
		$checkSum = json_decode(file_get_contents($this->checkSumFile), true);
		if (! is_array($checkSum)) {
			$checkSum = [];
		}
		$checkSum[$this->lang] = sha1_file($this->cacheFile);
		$handle = fopen($this->checkSumFile, "w");
		flock($handle, LOCK_EX);
		fwrite($handle, json_encode($checkSum));
		fclose($handle);
	}

	/**
	 * @return array
	 */
	public function search()
	{
		if ($this->hasCachedData()) {
			return $this->getCachedData();
		}

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
					$result["photos"][] = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
				}
			}
		}
		$this->writeCache($result);
		return $result;
	}
}
