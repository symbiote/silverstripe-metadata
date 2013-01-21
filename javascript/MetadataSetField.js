;(function($) {

	$.entwine('ss', function($){
		
		$("#Form_EditForm .ss-metdatasetfield").entwine({
			onmatch: function() {
//				This is commented out for now as we don't really need the accordion control 
//				this.accordion({
//					collapsible: true,
//					active: false
//				});

				this._super();
			},
			onremove: function() {
//				this.accordion('destroy');
			},

			getTabSet: function() {
				return this.closest(".ss-tabset");
			},

			fromTabSet: {
				ontabsshow: function() {
					// this.accordion("resize");
				}
			}

		});

		$("#Form_EditForm .ss-metadatasetfield-showreplacements").entwine({
			onclick: function(){
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
			}
		});

	});
	
})(jQuery);