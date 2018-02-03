# Wikipedia Scraper

### Usage: 

```php
<?php

require __DIR__."/src/Wikipedia.php";
// require __DIR__."/vendor/autoload.php";

define("WIKIPEDIA_DATA_DIR", __DIR__."/data");

$query = "jack the ripper";
$wiki = new Wikipedia\Wikipedia($query, "en");
$wiki = $wiki->search();

var_dump($wiki);
```


### Output:
```
array(4) {
  ["title"]=>
  string(27) "Jack the Ripper - Wikipedia"
  ["url"]=>
  string(45) "https://en.wikipedia.org/wiki/Jack_the_ripper"
  ["photos"]=>
  array(9) {
    [0]=>
    string(141) "https://upload.wikimedia.org/wikipedia/commons/thumb/3/39/Whitechapel_Spitalfields_7_murders.JPG/300px-Whitechapel_Spitalfields_7_murders.JPG"
    [1]=>
    string(121) "https://upload.wikimedia.org/wikipedia/commons/thumb/4/49/MaryJaneKelly_Ripper_100.jpg/220px-MaryJaneKelly_Ripper_100.jpg"
    [2]=>
    string(145) "https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/Whitehall_murder_school_illustration.jpg/220px-Whitehall_murder_school_illustration.jpg"
    [3]=>
    string(99) "https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/F.G.Abberline.jpg/170px-F.G.Abberline.jpg"
    [4]=>
    string(113) "https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Ripper_cartoon_punch.jpg/220px-Ripper_cartoon_punch.jpg"
    [5]=>
    string(107) "https://upload.wikimedia.org/wikipedia/commons/thumb/8/88/JacktheRipperPuck.jpg/170px-JacktheRipperPuck.jpg"
    [6]=>
    string(101) "https://upload.wikimedia.org/wikipedia/commons/thumb/a/aa/FromHellLetter.jpg/170px-FromHellLetter.jpg"
    [7]=>
    string(99) "https://upload.wikimedia.org/wikipedia/commons/thumb/5/55/Wanted_poster.jpg/170px-Wanted_poster.jpg"
    [8]=>
    string(243) "https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Jack-the-Ripper-The-Nemesis-of-Neglect-Punch-London-Charivari-cartoon-poem-1888-09-29.jpg/220px-Jack-the-Ripper-The-Nemesis-of-Neglect-Punch-London-Charivari-cartoon-poem-1888-09-29.jpg"
  }
  ["prologue"]=>
  string(880) "<b>Jack the Ripper</b> is the best-known name for an unidentified <a href="/wiki/Serial_killer" title="Serial killer">serial killer</a> generally believed to have been active in the largely impoverished areas in and around the <a href="/wiki/Whitechapel" title="Whitechapel">Whitechapel</a> district of <a href="/wiki/London" title="London">London</a> in 1888. The name "Jack the Ripper" originated in a <a href="/wiki/Dear_Boss_letter" title="Dear Boss letter">letter</a> written by someone claiming to be the murderer that was disseminated in the media. The letter is widely believed to have been a hoax and may have been written by journalists in an attempt to heighten interest in the story and increase their newspapers' circulation. In both the criminal case files and contemporary journalistic accounts, the killer was called "the Whitechapel Murderer" and "Leather Apron"."
}
```