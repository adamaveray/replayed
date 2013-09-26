<?php
define('EMPTY_CLASS', '__unset__');

function showList($items){
	$output	= '';
	foreach($items as $item){
		$output	.= showEntry($item);
	}
	return $output;
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

	// Platforms
	if($platforms){
		foreach($platforms as $platform){
			$slug	= slugify(findBracketedWord($platform, '[', ']', true));
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
	} else {
		$classes[]	= 'platform-'.EMPTY_CLASS;
	}

	// Tags
	if($tags){
		$tagElements	= array();
		foreach($tags as $tag){
			$classes[]	= 'tag-'.slugify($tag);
			$tagElements[]	= '<li class="tag">'.render($tag, false).'</li>';
		}
		$output	.= '<ul class="tags">'.implode('', $tagElements).'</li></ul>';
	} else {
		$classes[]	= 'tag-'.EMPTY_CLASS;
	}
	if(isset($notes)){
		$output	.= preg_replace('~^<p>~', '<p class="notes">', render($notes));
	}

	$output	= '<li class="item'.($classes ? ' '.implode(' ', $classes) : '').'">'.$output.'</li>';
	return $output;
}

function showFilter($items, $type, $class){
	$output	= '';
	if(!$items){
		return $output;
	}

	$pluralClass	= $class.'s';
	ob_start();
	?>
	<div class="filter-container">
	<h3><?=$type;?></h3>
	<ul class="filter-list filter-<?=$pluralClass;?>" data-type="<?=$class;?>">
		<?php foreach($items as $item){?>
		<li class="filter-item filter-<?=$class;?>">
			<input type="checkbox" name="<?=$class;?>[]" value="<?=slugify($item);?>" id="filter-<?=$class;?>-<?=slugify($item);?>" checked />
			<label for="filter-<?=$class;?>-<?=slugify($item);?>"><?=$item;?></label>
		</li>
		<?php }?>
		<li class="filter-item filter-<?=$class;?>">
			<input type="checkbox" name="<?=$class;?>[]" value="<?=EMPTY_CLASS;?>" id="filter-<?=$class;?>-<?=EMPTY_CLASS;?>" checked />
			<label for="filter-<?=$class;?>-<?=EMPTY_CLASS?>">No <?=strtolower($type);?></label>
		</li>
	</ul>
	</div>
	<?php
	return ob_get_clean();
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
<?php foreach($lists as $title => $list){ ?>
	<div class="items-group">
		<h2><?=$title;?></h2>
		<?=render($list['output']);?>
		<?=showList($list['items']);?>
	</div>
<?php }?>
</div>

<?php if($allPlatforms || $allTags){?>
<aside class="filters">
	<h2>Filters</h2>
	<?=showFilter($allPlatforms, 'Platform', 'platform');?>
	<?=showFilter($allTags, 'Tags', 'tag');?>
</aside>
<?php }?>

<footer role="contentinfo">
	Powered by <a href="https://github.com/adamaveray/replayed">Replayed</a>
</footer>

<!--[if lt IE 9]><script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script><![endif]-->
<!--[if gte IE 9]><!--><script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script><!--<![endif]-->
<script src="script.js"></script>
</body>
</html>