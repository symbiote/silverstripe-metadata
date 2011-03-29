;(function($) {
	$(".ss-metdatasetfield").livequery(function() {
		$(this).accordion({
			active: false, collapsible: true
		});
		
		$(".ss-metadatasetfield-showreplacements", this).click(function() {
			var $field  = $(this).parents(".ss-metadatasetfield");
			var $dialog = $field.find(".ss-metadatasetfield-keywordreplacements");
			
			$dialog.clone().dialog({
				modal: true,
				resizable: false,
				draggable: false,
				width: 500,
				height: 400
			});
			
			return false;
		});
	});
	
	/**
	 * Hacky workaround to make the accordion height correct in a hidden tab.
	 */
	$("#tab-Root_Metadata").live("click", function() {
		setTimeout(function() {
			$("#Root_Metadata .ss-metdatasetfield")
				.accordion("destroy")
				.accordion({
					active: false, collapsible: true
				});
		}, 5);
	});
})(jQuery);