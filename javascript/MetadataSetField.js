;(function($) {
	$(".ss-metdatasetfield").livequery(function() {
		$(this).accordion({
			active: false,
			autoHeight: true,
			collapsible: true
		});
		
		$(".ss-metadatasetfield-showreplacements", this).click(function() {
			var $field  = $(this).parents(".ss-metadatasetfield");
			var $dialog = $field.find(".ss-metadatasetfield-keywordreplacements");
			
			$dialog.dialog({
				modal: true,
				resizable: false,
				draggable: false,
				width: 500,
				height: 400
			});
			
			return false;
		});
	});
})(jQuery);