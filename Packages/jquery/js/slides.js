$(document).ready(function() {
    
	$('.slideshow').cycle({ 
     fx:     '{slidesStyle}', 
     timeout: 6000,    
	 next:   '#next2', 
     prev:   '#prev2'	 
    });
});