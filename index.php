<?php
$sourceDir	= __DIR__.'/games';
$ext		= 'md';

include('libs/smartypants.php');
include('libs/markdown.php');


function loadFile($file){
	global $sourceDir, $ext, $allTags, $allPlatforms;

	$file	= $sourceDir.'/'.$file.'.'.$ext;
	$content	= file_get_contents($file);
	$lines	= explode("\n", $content);

	if(isset($lines[1]) && preg_match('~^\={4,}~', $lines[1])){
		// Has title
		$title	= $lines[0];
		$lines	= array_slice($lines, 2);
	} else {
		// No title - use filename
		$title	= ucwords(preg_replace('~\d+(\.|\-) *(.+?)$~', '$2', pathinfo($file, \PATHINFO_FILENAME)));
	}

	// Remove blank lines
	while(isset($lines[0]) && trim($lines[0]) === ''){
		array_shift($lines);
	}

	$output		= '';
	$noLines	= count($lines);
	$buffer		= '';
	$inItem		= false;
	$flush		= false;

	$items	= array();

	// Process lines
	for($i = 0; $i < $noLines; $i++){
		$line	= rtrim($lines[$i]);

		if($line[0] === '-' || $line[0] === '*'){
			// Start of item
			$inItem	= true;
		}

		if(!isset($lines[$i+1]) || $lines[$i+1][0] === '-' || $lines[$i+1][0] === '*'){
			// End of section
			$flush	= true;

		} else if($line === ''){
			// Blank line
			if(rtrim($lines[$i+1]) != ''){
				// End of section
				$flush	= true;
			}
		}

		$buffer	.= $line."\n";

		if($flush){
			// Save buffer
			if($inItem){
				// Entry
				$item		= processEntry($buffer);
				$items[]	= $item;
				$allTags		= array_unique(array_merge($allTags, $item['tags']));
				foreach($item['platforms'] as $platform){
					$r = $platform;
					$platform	= findBracketedWord($platform, '[', ']', true);
					if($platform === ''){
						echo '?? - ';print_r($item);exit;
					}
					if(!in_array($platform, $allPlatforms)){
						$allPlatforms[]	= $platform;
					}
				}

			} else {
				// Plain content
				$output	.= render($buffer);
			}

			$buffer	= '';
			$flush	= false;
		}
	}

	if($buffer != ''){
		$output	.= render($buffer);
	}

	return array(
		'title'	=> $title,
		'items'	=> $items,
		'extra'	=> $output
	);
}

function processEntry($str){
	// Title
	$title	= null;
	$str	= preg_replace_callback('~^[-\*]\s+(.*?)\s+(\(|\z)~', function($matches) use(&$title){
		$title	= $matches[1];
		return $matches[2];
	}, $str);

	// Tags
	preg_match_all('~#(\S+)~', $str, $matches);
	$tags	= $matches[1];

	// Platform
	$originalPlatforms	= findBracketedWord($str);
	$platforms	= array();
	if($originalPlatforms){
		// Separately-linked platforms
		$separator	= '/';
		$platforms	= preg_split('~(?<=[\]\)])'.$separator.'(?=\[)~', $originalPlatforms);
	}

	if(count($platforms) === 1){
		$platformName	= findBracketedWord($originalPlatforms, '[', ']');
		if(isset($platformName)){
			// Group-linked - convert to separately linked
			$platformNames	= explode($separator, $platformName);
			$link	= findBracketedWord($originalPlatforms);
			$platforms	= array();
			foreach($platformNames as $platform){
				$platforms[]	= '['.$platform.']('.$link.')';
			}

		} else {
			// Unlinked
			$platforms	= explode($separator, $originalPlatforms);
		}
	}
	$str	= str_replace('('.$originalPlatforms.')', '', $str);

	// Notes
	$notes	= findBracketedWord($str);

	return array(
		'title'		=> $title,
		'platforms'	=> $platforms,
		'notes'	=> $notes,
		'tags'	=> $tags
	);
}


// Utilities
function render($string, $markdown = true, $inline = false){
	if(trim($string) === ''){
		// Empty string
		return $string;
	}

	$string	= smartypants($string);
	if($markdown){
		$string	= markdown($string);

		if($inline){
			// Remove wrapping paragraph
			$string	= preg_replace('~^<p>(.*?)</p>$~', '$1', trim($string));
		}
	}

	return $string;
}

function slugify($str){
	$str	= strtolower(preg_replace('~[^\w]+~', '', $str));
	return $str;
}

function findBracketedWord($string, $open = '(', $close = ')', $defaultToValue = false){
	$string	= ltrim($string);
	$len	= strlen($string);
	$start = 0;

	// Move to first bracket
	while($start < $len && $string[$start] != $open){
		$start++;
	}
	if($start === $len){
		// No brackets found
		return ($defaultToValue ? $string : null);
	}

	$depth	= 1;

	for($i = $start+1; $i < $len; $i++){
		$char	= $string[$i];
		switch($char){
			case $open:
				$depth++;
				break;

			case $close:
				$depth--;
				if($depth === 0){
					break 2;
				}
				break;
		}
	}

	// Select bracketed part
	return substr($string, $start+1, $i-$start-1);
}


// Load files
$files	= glob($sourceDir.'/*.'.$ext);
$lists	= array();

$allTags		= array();
$allPlatforms	= array();

foreach($files as $file){
	$file	= pathinfo($file, \PATHINFO_FILENAME);

	if(is_dir($file) || $file[0] == '_'){
		// Hidden
		continue;
	}

	$file	= loadFile($file);
	$lists[$file['title']]	= $file;
}

include(__DIR__.'/template.php');
