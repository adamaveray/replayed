/*! Slide Down and Fade In */
(function(a){a.fn.slideDownFadeIn=function(b,c){b=a.extend({},{fadeSpeed:"normal",slideSpeed:"normal"},b);return this.each(function(){var d=a(this);if(d.is(":visible")){return}d.css("opacity",0).slideDown(b.slideSpeed,function(){d.fadeTo(b.fadeSpeed,1,c)})})};a.fn.fadeOutSlideUp=function(b,c){b=a.extend({},{fadeSpeed:"normal",slideSpeed:"normal"},b);return this.each(function(){var d=a(this);if(d.is(":hidden")){return}d.fadeTo(b.fadeSpeed,0,function(){d.slideUp(b.slideSpeed,c)})})}}(jQuery));

(function($){
	var $items	= $('.item')
	
	// Filtering
	var filters	= {},
		$allFilters,
		animationMax	= 25;

	$('.filter-list').each(function(){
		var $list	= $(this),
			type	= $list.data('type'),
			$inputs	= $list.find('input');

		filters[type]	= $inputs;
		$allFilters	= ($allFilters ? $allFilters.add($inputs) : $inputs);

		$inputs.change(function(){
			var showClasses	= [],
				hideClasses	= [];

			var $el	= $(this),
				$unchecked	= $inputs.not(':checked');

			if($unchecked.not($el).length === 0){
				// First item toggled
				$inputs.not($el).prop('checked', false);
				$el.prop('checked', true);

			} else if($unchecked.length === $inputs.length){
				// All unchecked
				$inputs.prop('checked', true);
			}

			// Collate all to hide
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
				$toHide	= (hideClasses.length ? $items.filter(hideClassList) : $()).filter(':visible'),
				$toShow	= (hideClasses.length ? $items.not(hideClassList) : $items).filter(':hidden');

			var speed	= {
				fadeSpeed:	'normal',
				slideSpeed:	'fast'
			};
			if($toHide.length > animationMax && 0){
				// Animations will stuggle
				$toHide.hide();
			} else {
				$toHide.fadeOutSlideUp(speed);
			}
			if($toShow.length > animationMax && 0){
				// Animations will struggle
				$toShow.show();
			} else {
				$toShow.slideDownFadeIn(speed);
			}
		});
	});
}(jQuery));
