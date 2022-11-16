jQuery(document).ready(function(){
	jQuery(document).on("click",".dlvrtg11",function(){
		var value_type = jQuery(this).attr("aria-label");
		if(value_type=="Check")
			{
				var func_ref = jQuery(this);
				const myTimeout = setTimeout(function(){
					func_ref.siblings(".dlvrtg11").eq(0).trigger("click");
				}, 500);
				
				//jQuery(this).siblings(".dlvrtg11").eq(0).hide();
				//jQuery(this).hide();
				//jQuery(this).siblings(".dlvrtg11").eq(0).trigger("click");
			}
	});
});

window.DeliverrFastTags = {	
	appConfig:{
		cartMinimum:true,
		sellerId: DeliverrAPI.seller_id
	}
};

console.log( DeliverrAPI.seller_id);