<?php
$sourceDir	= __DIR__.'/games';
$ext		= 'md';

include('libs/smartypants.php');
include('libs/markdown.php');

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
	$separator	= '/';
	// Separately-linked platforms
	$platforms	= preg_split('~(?<=[\]\)])'.$separator.'(?=\[)~', $originalPlatforms);

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

function render($string, $markdown = true, $inline = false){
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

function showEntry($entry){
	// Manual unpack
	$title		= $entry['title'];
	$platforms	= $entry['platforms'];
	$notes		= $entry['notes'];
	$tags		= $entry['tags'];

	// Build HTML
	$classes	= array();
	$output	=	'<h3 class="title">'
					.'<a class="reference-link" target="_blank" href="http://www.google.com/search?q='.urlencode($title.' Game '.findBracketedWord(current($platforms), '[', ']', true)).'&btnI">'
						.render($title, true, true)
					.'</a>'
				.'</h3>';
	if(isset($platforms)){
		foreach($platforms as $platform){
			$slug	= strtolower(preg_replace('~[^\w]+~', '', findBracketedWord($platform, '[', ']', true)));
			$classes[]	= 'platform-'.$slug;
		}
		$output	.= ' <span class="platform">';
			if(isset($platformLink)){
				$output	.= '<a href="'.$platformLink.'">';
			}
			$output	.= render(implode('/', $platforms), true, true);
			if(isset($platformLink)){
				$output	.= '</a>';
			}
		$output	.= '</span>';
	}
	if($tags){
		$tagElements	= array();
		foreach($tags as $tag){
			$classes[]	= 'tag-'.$tag;
			$tagElements[]	= '<li class="tag">'.render($tag, false).'</li>';
		}
		$output	.= '<ul class="tags">'.implode('', $tagElements).'</li></ul>';
	}
	if(isset($notes)){
		$output	.= preg_replace('~^<p>~', '<p class="notes">', render($notes));
	}

	$output	= '<li class="item'.($classes ? ' '.implode(' ', $classes) : '').'">'.$output.'</li>';
	return $output;
}


function loadFile($file){
	global $sourceDir, $ext;

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

	$output		= '<h2>'.$title.'</h2>';
	$noLines	= count($lines);
	$buffer		= '';
	$inItem		= false;
	$flush		= false;

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
				$entry	= processEntry($buffer);
				$output	.= showEntry($entry);
				$inItem	= false;
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

	return $output;
}


// Load files
$files	= glob($sourceDir.'/*.'.$ext);
$lists	= array();

foreach($files as $file){
	$file	= pathinfo($file, \PATHINFO_FILENAME);

	if(is_dir($file) || $file[0] == '_'){
		// Hidden
		continue;
	}

	$name	= ucwords(preg_replace('~[-_]+~', ' ', preg_replace('~^\d+(?:-|\.\s*)~', '', $file)));
	
	$lists[$name]	= $file;
}

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Replayed</title>
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<meta name="robots" content="noindex, nofollow" />

	<link rel="stylesheet" href="style.css" />
</head>
<body>
	
<div role="main" data-count="<?=count($lists);?>">
<?php foreach($lists as $name => $file){ ?>
	<div class="items-group">
	<?=loadFile($file);?>
	</div>
<?php }?>
</div>

<footer role="contentinfo">
	Powered by <a href="https://github.com/adamaveray/replayed">Replayed</a>
</footer>
</body>
</html>