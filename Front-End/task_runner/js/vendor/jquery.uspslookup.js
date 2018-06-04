(function ($) {
   /* $.fn.extend({ 
        cityStateLookup : function(options) {

        	var defaults = {
				uspsUserID	 : "881JELLY3194"
			};
			var settings = $.extend({}, defaults, options);

            
            //this.each(function () {

            	//$(this).on('keyup', function(){
            		//console.log("asdfasdf'");
            		
            		//if(!isNaN($(this).val()) && ($(this).val().length == 5)){

            			var zipCode = $(this).val();
						var uspsapi = 'http://production.shippingapis.com/ShippingAPITest.dll?API=CityStateLookup&XML=<CityStateLookupRequest USERID="'+settings.uspsUserID+'"><ZipCode ID= "0"><Zip5>'+zipCode+'</Zip5></ZipCode></CityStateLookupRequest>';
						$.ajax({
							url			: uspsapi,
							type 		: 'GET',
						    success		: function(xml){

							   	if($(xml).find("Error").length){
							   		console.log("ret1");
							   		$(this).data('zip',false);

							   	}else{
							   		
							   		document.cookie = "ltlpZip="+zipCode+"; path=/";
							   		document.cookie = "ltlpCity="+$(xml).find("City").text()+"; path=/";
							   		document.cookie = "ltlpState="+$(xml).find("State").text()+"; path=/";
							   		console.log("ret2");
							   		$(this).data('zip',true);
							   	}
						    	
						    }
						});

            		//}

            	//});

                
            //});
            
        }
    });*/


    $.fn.cityStateLookup = function( options ) {
	
	var defaults = {
		uspsUserID	 : "881JELLY3194", //register in usps and userid will be emailed
		//onlyValidate : false,
		//dropCookie   : true
		//cityElement  : "city",  //id of element where you want to display city
		//stateElement : "state", //id of element where you want to display state
		//msgElement   : "zipmsg"	
	};

	var settings = $.extend({}, defaults, options);

	//return this.each(function(){
	
		$(this).on('keyup', function(){

			//console.log($(this).val());
			
			if(!isNaN(value)){
				var uspsapi = 'http://production.shippingapis.com/ShippingAPITest.dll?API=CityStateLookup&XML=<CityStateLookupRequest USERID="881JELLY3194"><ZipCode ID= "0"><Zip5>'+value+'</Zip5></ZipCode></CityStateLookupRequest>';
				var ret = false;
				$.ajax({
					url			: uspsapi,
					async		: false,
					type 		: 'GET',
				    success		: function(xml){


					   	if($(xml).find("Error").length){
					   		ret = false;
					   		
					   	}else{
					   		document.cookie = "ltlpZip="+value+"; path=/";
					   		document.cookie = "ltlpCity="+$(xml).find("City").text()+"; path=/";
					   		document.cookie = "ltlpState="+$(xml).find("State").text()+"; path=/";
					   		ret = true;
					   	}
				    	
				    }
				});

				return ret;
			}
		});

	//});
	
};


})(jQuery);

/*


*/
