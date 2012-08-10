;(function($) {
	$(function(){

		$.entwine('ss', function($){
			$("#Type input").entwine({
				onmatch : function(){
					$('#EmptyMode').showHideEmpty();
				},

				onchange : function(){
					$('#EmptyMode').showHideEmpty();
				}
			});

			$("#EmptyMode input").entwine({
				onmatch : function(){
					$('#EmptyText').toggleText();
				},

				onchange : function(){
					$('#EmptyText').toggleText();
				}
			});

			$("#EmptyMode").entwine({
				showHideEmpty : function(){
					//$(this).toggle($("#Type input:checked").val() == "dropdown") not working...f
					$("#Type input:checked").val() == "dropdown" ? $(this).show() : $(this).hide();
				}
			});

			$("#EmptyText").entwine({
				toggleText : function(){
					//$(this).toggle($("input[name='EmptyMode']:checked").val() == "text");
					$("input[name='EmptyMode']:checked").val() == "text" ? $(this).show() : $(this).hide();
				}
			});
		});
		
	});
})(jQuery);