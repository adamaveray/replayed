/*! Slide Down and Fade In */
(function(a){a.fn.slideDownFadeIn=function(b,c){b=a.extend({},{fadeSpeed:"normal",slideSpeed:"normal"},b);return this.each(function(){var d=a(this);if(d.is(":visible")){return}d.css("opacity",0).slideDown(b.slideSpeed,function(){d.fadeTo(b.fadeSpeed,1,c)})})};a.fn.fadeOutSlideUp=function(b,c){b=a.extend({},{fadeSpeed:"normal",slideSpeed:"normal"},b);return this.each(function(){var d=a(this);if(d.is(":hidden")){return}d.fadeTo(b.fadeSpeed,0,function(){d.slideUp(b.slideSpeed,c)})})}}(jQuery));

(function($){
	var $items	= $('.item')
	
	// Filtering
	var filters	= {},
		$allFilters,
		animationMax	= 35;

	var showFilterClear	= function($list){
			var $clear	= $list.data('clear-button');
			if(!$clear){
				// Create button
				$clear	= $('<button class="clear-button" type="button" />').text('Clear');
				$list.data('clear-button', $clear);
				$clear.hide()
					  .appendTo($list)
					  .click(function(){
					// Clear all
					var $inputs	= $list.find('input');
					$inputs.prop('checked', true);
					$inputs.eq(0).change();
				});
			}

			if($clear.is(':hidden')){
				$clear.show();
			}
		},
		hideFilterClear	= function($filter){
			var $clear	= $filter.data('clear-button');
			if(!$clear){
				// Nothing to remove
				return;
			}

			if($clear.is(':visible')){
				$clear.hide();
			}
		};

	$('.filter-container').each(function(){
		var $container	= $(this),
			$list	= $container.children('.filter-list'),
			type	= $list.data('type'),
			$inputs	= $list.find('input');

		filters[type]	= $inputs;
		$allFilters	= ($allFilters ? $allFilters.add($inputs) : $inputs);

		$inputs.change(function(){
			var $el	= $(this),
				$unchecked	= $inputs.not(':checked');

			if($unchecked.not($el).length === 0 && !$el.prop('checked')){
				// First item toggled
				$inputs.not($el).prop('checked', false);
				$el.prop('checked', true);

			} else if($unchecked.length === $inputs.length){
				// All unchecked
				$inputs.prop('checked', true);
			}

			if($inputs.not(':checked').length){
				showFilterClear($container);
			} else {
				hideFilterClear($container);
			}

			// Collate all to hide
			var $toShow	= $items,
				$toHide;
			for(var type in filters){
				var $filters	= filters[type],
					showClasses	= [],
					hideClasses	= [];

				if(!$filters.not(':checked').length){
					continue;
				}

				$filters.each(function(){
					var $filter		= $(this),
						className	= type+'-'+$filter.attr('value');

					if($filter.is(':checked')){
						showClasses.push(className);
					} else {
						hideClasses.push(className);
					}
				});

				$toShow	= $toShow.filter('.'+showClasses.join(',.'));
			}

			$toHide	= $items.not($toShow);

			// Toggle elements
			var speed	= {
				fadeSpeed:	'normal',
				slideSpeed:	'fast'
			};

			$toHide	= $toHide.filter(':visible');
			if($toHide.length > animationMax){
				// Animations will stuggle
				$toHide.hide();
			} else {
				$toHide.fadeOutSlideUp(speed);
			}

			$toShow	= $toShow.filter(':hidden');
			if($toShow.length > animationMax){
				// Animations will struggle
				$toShow.show();
			} else {
				$toShow.slideDownFadeIn(speed);
			}
		});
	});
}(jQuery));
