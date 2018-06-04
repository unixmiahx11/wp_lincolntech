(function($){
	
	$.validator.addMethod("uspszip", function(value, element){ //this will validate the zipcode and drop a cookie for vlaid zip code that can look up for city and state

		if(!isNaN(value)){
			var uspsUserID = "881JELLY3194"; //Dont forget to change this to userID registerd for LT site
			var uspsapi = 'http://production.shippingapis.com/ShippingAPITest.dll?API=CityStateLookup&XML=<CityStateLookupRequest USERID="'+uspsUserID+'"><ZipCode ID= "0"><Zip5>'+value+'</Zip5></ZipCode></CityStateLookupRequest>';
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
	    
	}, "Invalid zip code. Please enter valid 5 digit zip code."); 

	$('.wpcf7-form').validate({
		rules:{
			program: {
				required: true
			},
			campus: {
				required: true
			},
			fname: {
				required: true,
				maxlength: 5
			},
			lname: {
				required: true
			},
			email:{
				required: true,
				email: true
			},
			phone:{
				required: true
			},
			zip:{
				uspszip: true,
				required: true
			},
			graduation_year:{
				required: true
			},
			is_cell:{
				required: true
			}
		},

		messages:{
			program: "This field is required.",
			campus: "This field is required.",
			fname: {
				required: "Please specify",
				maxlength: "asdfasdf"
			},
			lname: "Please specify",
			email: "Please provide a valid email addresss.",
			phone: {
					required: "Please provide a valid phone number."
					
			},
			//zip: "Please provide a valid zip code.",
			is_cell: "Please specify",
			graduation_year: "This field is required"
		},

		submitHandler: function(form) {
    		form.submit();
    		
  		}
  		


	});



})(jQuery);