;(function($) {
	function showHideEmpty() {
		$("#EmptyMode").toggle($("#Type input:checked").val() == "dropdown");
	}
	
	function toggleText() {
		$("#EmptyText").toggle($("input[name='EmptyMode']:checked").val() == "text");
	}
	
	$(showHideEmpty);
	$(toggleText);
	
	$("#Type input").change(showHideEmpty);
	$("#EmptyMode input").change(toggleText);
})(jQuery);