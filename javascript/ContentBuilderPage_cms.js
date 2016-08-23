

(function($){
	"use strict";
	$.entwine('ss',function($){
		$(".action.content-builder-delete-with-confirm").entwine({
			onclick: function(e){
				if (!confirm('Are you sure you want to delete this? You CANNOT restore once deleted!')){
					e.preventDefault();
					return false;
				}else{
					this._super(e);
				}
			}
		});
	});
}(jQuery));