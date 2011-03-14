;(function($) {
	function toggleText() {
		$("#EmptyText").toggle($("input[name='EmptyMode']:checked").val() == "text");
	}
	
	$(function() {
		toggleText();
	});
	
	$("input[name='EmptyMode']").change(function() {
		toggleText();
	});
})(jQuery);