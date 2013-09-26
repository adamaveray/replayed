/*! Slide Down and Fade In */
(function(a){a.fn.slideDownFadeIn=function(b,c){b=a.extend({},{fadeSpeed:"normal",slideSpeed:"normal"},b);return this.each(function(){var d=a(this);if(d.is(":visible")){return}d.css("opacity",0).slideDown(b.slideSpeed,function(){d.fadeTo(b.fadeSpeed,1,c)})})};a.fn.fadeOutSlideUp=function(b,c){b=a.extend({},{fadeSpeed:"normal",slideSpeed:"normal"},b);return this.each(function(){var d=a(this);if(d.is(":hidden")){return}d.fadeTo(b.fadeSpeed,0,function(){d.slideUp(b.slideSpeed,c)})})}}(jQuery));

(function($){
	// Filtering
	var $items	= $('.item'),
		filters	= {},
		$allFilters;

	$('.filter-list').each(function(){
		var $list	= $(this),
			type	= $list.data('type'),
			$inputs	= $list.find('input');

		filters[type]	= $inputs;
		$allFilters	= ($allFilters ? $allFilters.add($inputs) : $inputs);
	});

	$allFilters.change(function(){
		var showClasses	= [],
			hideClasses	= [];
		for(var type in filters){
			var $filters	= filters[type];
			$filters.each(function(){
				var $filter		= $(this),
					className	= type+'-'+$filter.attr('value');

				if($filter.is(':checked')){
					showClasses.push(className);
				} else {
					hideClasses.push(className);
				}
			});
		}

		// Toggle elements
		var hideClassList	= '.'+hideClasses.join(',.'),
			$toHide	= (hideClasses.length ? $items.filter(hideClassList) : $()),
			$toShow	= (hideClasses.length ? $items.not(hideClassList) : $items);

		var speed	= {
			fadeSpeed:	'normal',
			slideSpeed:	'fast'
		};
		$toHide.fadeOutSlideUp(speed);
		$toShow.slideDownFadeIn(speed);
	});
}(jQuery));
