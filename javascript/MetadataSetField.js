;(function($) {
	$(".MetadataSetField").livequery(function() {
		$(this).accordion({
			active: false,
			autoHeight: true,
			collapsible: true
		});
	});
})(jQuery);