$(document).ready(function(){
	//$(".jhtmlarea").htmlarea();
	//defaults
    $.fn.editable.defaults.url = '/post';

    //editables
    $('.editable').editable({
        type: 'text',
        pk: $("#pKey").val(),
        url: '/ConfigCP/ConfigCP/changeConfig',
        send: 'always'
    });
});


