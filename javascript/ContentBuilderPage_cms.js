(function($){
	"use strict";
	$.entwine('ss',function($){
		$("input.colorPicker").spectrum({
			preferredFormat: "hex",
			showInput: true,
			allowEmpty:true,
			showAlpha: true
		});
	});
}(jQuery));