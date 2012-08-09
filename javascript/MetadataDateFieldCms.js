;(function($) {
	$.entwine('ss', function($){
		
		$("#DefaultType input").entwine({
			onmatch : function(){
				$('#Default').toggleDefault();
			},

			onchange : function(){
				$('#Default').toggleDefault();
			}
		});

		$("#Default").entwine({
			toggleDefault : function(){
				$("#DefaultType input:checked").val() == "specific" ? $(this).show() : $(this).hide();
			}
		});
		
	});
})(jQuery);