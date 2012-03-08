<?php

/*

The Prometheus movie website has a blinking star that's showing Morse code as a hint to more teaser content.
 Let's decode that blinking using computers, since I'm too lazy to learn Morse code but not so lazy as to write
 a program to do it for me. It's not a perfect signal (looks like it was digitized from a source with a different
 input frame rate as the animated GIF so we'll have to do some quantizing and massaging.

References:
https://www.weylandindustries.com/ (About Us)
http://forums.unfiction.com/forums/viewtopic.php?t=34138&start=120
http://www.prometheusnews.net/movie/viral-mystery-1-solved/
http://www.reddit.com/r/movies/comments/qlu6u/new_prometheus_image_star_map/

Spoiler: 
 This code produces the output 'EDIR6EQUJ5E' which is a hint to the path 
 https://www.weylandindustries.com/6EQUJ5 which shows a nice concept painting of an Orrery, hopefully from the
 upcoming movie. Only makes sense if you get the reference to http://en.wikipedia.org/wiki/Wow%21_signal

Author:
 Nathan Schmidt - github.com/hinathan

License:
 MIT
*/

define('kMaxDotHint',4);
define('kSpaceHint',4);

$morse = array('.-'=>'A','-...'=>'B','-.-.'=>'C','-..'=>'D','.'=>'E','..-.'=>'F','--.'=>'G','....'=>'H','..'=>'I','.---'=>'J','.-.'=>'R','.-..'=>'L','--'=>'M','-.'=>'N','---'=>'O','.--.'=>'P','--.-'=>'Q','...'=>'S','-'=>'T','..-'=>'U','...-'=>'V','.--'=>'W','-..-'=>'X','-.--'=>'Y','--..'=>'Z','.----'=>'1','..---'=>'2','...--'=>'3','....-'=>'4','.....'=>'5','-....'=>'6','--...'=>'7','---..'=>'8','----.'=>'9','-----'=>'0');

if(false) {
	`wget https://www.weylandindustries.com/img/findme.gif`;
	// use ImageMagic's convert and identify tools.
	// must coalesce or we'll get alpha-channel frames that don't register right here.
	`convert findme.gif -coalesce tmpFrame%03d.png`;
	// greyscale image, ok to just take the R channel as a proxy for brightness.
	$colorlist = `identify -verbose tmpFrame*.png |grep -A1 Histogram|grep 1:|cut -d'(' -f2|cut -d',' -f1`;
	`rm tmpFrame*.png`;
	$input = explode("\n",$colorlist);
} else {
	// pre-extracted for speed, 237 frames.
	$input = array(255,255,220,125,125,125,125,255,255,255,255,255,255,255,255,125,235,255,125,125,125,255,255,125,125,125,125,125,125,125,125,255,216,125,125,255,252,209,125,125,125,125,125,255,255,255,255,125,255,255,255,255,255,255,255,255,125,142,255,255,172,125,125,125,125,125,125,255,255,255,255,255,255,255,255,125,255,255,125,125,125,255,255,125,125,186,255,255,125,125,195,255,255,125,125,125,125,125,125,125,254,255,125,125,125,125,125,125,255,255,255,255,255,255,255,242,125,255,255,255,255,255,255,255,255,125,240,255,255,255,125,242,255,254,255,255,255,255,125,125,125,125,125,125,125,255,255,202,125,125,255,255,125,255,255,255,255,255,255,255,255,255,125,125,125,125,125,153,255,255,255,125,255,255,255,255,255,255,255,255,255,145,172,255,255,255,255,255,255,255,255,125,255,255,255,255,255,255,255,255,125,125,125,125,125,125,255,255,125,125,125,254,251,125,125,229,255,242,125,125,255,255,125,125,125,255,255,125,125,125,125,125,125,125,125,125,125);
}

$stringmap = "";
foreach($input as $in) {
	//strong mark signal
	if($in == 255) {
		$stringmap .= "X";
	// wavering mark signal
	} else if($in > 140) {
		$stringmap .= "x";
	// blank/space
	} else {
		$stringmap .= " ";
	}
}

print "Raw stream:\n\n$stringmap\n\n";

// split into words based on space length heuristic
$stringmap = preg_replace('/\s{' . kSpaceHint . ',}/',"\n",$stringmap);
// compact spaces
$stringmap = preg_replace('/ +/',' ',$stringmap);
// several diminished = waver = a space
$stringmap = preg_replace('/Xxx+X/','X X',$stringmap);
// single diminished = keep as a run
$stringmap = preg_replace('/XxX/','XXX',$stringmap);
$chunks = explode("\n",$stringmap);

foreach($chunks as $i=>$word) {
	$chunks[$i] = quantize($word);
}
$stringmap = implode(" ",$chunks);

print "Quantized stream:\n\n$stringmap\n\n";

$result = "";
print "Decoded result:\n\n";
foreach(explode(" ",$stringmap) as $i=>$word) {
	print isset($morse[$word])?$morse[$word]:"???";
}
print "\n\n";


function quantize($str) {
	$out = "";
	$chunks = explode(' ',$str);
	$lens = array();
	foreach($chunks as $chunk) {
		$lens[] = strlen($chunk);
	}
	foreach($chunks as $chunk) {
		$len = strlen($chunk);
		if($len <= kMaxDotHint) {
			$out .= ".";
		} else if(($len < (min($lens)*1.2)) || ($len <= (max($lens)*.5))) {
			$out .= ".";
		} else {
			$out .= "-";
		}
	}
	return $out;
}


