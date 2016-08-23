

(function($,w,d){
	"use strict";
	$(d).ready(function(){
		$(w).load(function(){
			fixColHeights();
		}).resize(function(){
			fixColHeights();
		});
	});
	
	var fixColHeights = function(){
		var winWidth=$(w).width();
		$("#cb-area .cb-section").each(function(){
			var chldrn=$(this).find(">div.cb-col");
			if ($(this).hasClass('col-8')){
				// 8 column
				if(winWidth<550){
					$(chldrn).css('height','auto');
				}else if(winWidth<900){
					$(chunkSet(chldrn,4)).each(function(){
						matchHeights(this);
					});
				}else if(winWidth<1300){
					$(chunkSet(chldrn,4)).each(function(){
						matchHeights(this);
					});
				}else{
					matchHeights(chldrn);
				}
			}else if ($(this).hasClass('col-7')){
				// 7 column
				if(winWidth<550){
					$(chldrn).css('height','auto');
				}else if(winWidth<800){
					$(chunkSet(chldrn,2)).each(function(){
						matchHeights(this);
					});
				}else if(winWidth<1100){
					$(chunkSet(chldrn,4)).each(function(){
						matchHeights(this);
					});
				}else{
					matchHeights(chldrn);
				}
			}else if ($(this).hasClass('col-6')){
				// 6 column
				if(winWidth<600){
					$(chldrn).css('height','auto');
				}else if(winWidth<800){
					$(chunkSet(chldrn,2)).each(function(){
						matchHeights(this);
					});
				}else if(winWidth<1100){
					$(chunkSet(chldrn,3)).each(function(){
						matchHeights(this);
					});
				}else{
					matchHeights(chldrn);
				}
			}else if ($(this).hasClass('col-5')){
				// 5 column
				if(winWidth<550){
					$(chldrn).css('height','auto');
				}else if(winWidth<900){
					$(chunkSet(chldrn,3)).each(function(){
						matchHeights(this);
					});
				}else{
					matchHeights(chldrn);
				}
			}else if ($(this).hasClass('col-4')){
				// 4 column
				if(winWidth<550){
					$(chldrn).css('height','auto');
				}else if(winWidth<900){
					$(chunkSet(chldrn,2)).each(function(){
						matchHeights(this);
					});
				}else{
					matchHeights(chldrn);
				}
			}else if ($(this).hasClass('col-3')){
				// 3 column
				if(winWidth<550){
					$(chldrn).css('height','auto');
				}else if(winWidth<700){
					$(chunkSet(chldrn,2)).each(function(){
						matchHeights(this);
					});
				}else{
					matchHeights(chldrn);
				}
			}else if ($(this).hasClass('col-2')){
				// 2 column
				if(winWidth<600){
					$(chldrn).css('height','auto');
				}else{
					matchHeights(chldrn);
				}
			}
			
		});	
	};
	var matchHeights = function(set){
		var maxH=0;
		$(set).css('height','auto').each(function(){
			maxH=Math.max(maxH,$(this).outerHeight(false));
		}).each(function(){
			$(this).height(maxH - parseInt($(this).css('padding-top')) - parseInt($(this).css('padding-bottom')));
		});
	};
	var chunkSet = function(set,sizeEach){
		if (set.length<=sizeEach){ return set; }
		var count=0;
		var newSet=[];
		var subSet=[];
		$(set).each(function(){
			count++;
			if (count<sizeEach){
				subSet.push(this);
			}else{
				subSet.push(this);
				newSet.push(subSet);
				subSet=[];
				count=0;
			}
		});
		if (subSet.length){ newSet.push(subSet); }
		return newSet;
	};

}(jQuery,window,document));