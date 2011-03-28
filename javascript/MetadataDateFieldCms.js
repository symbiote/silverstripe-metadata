;(function($) {
	function toggleDefault() {
		$("#Default").toggle($("#DefaultType input:checked").val() == "specific");
	}
	
	$(toggleDefault);
	$("#DefaultType input").change(toggleDefault);
})(jQuery);