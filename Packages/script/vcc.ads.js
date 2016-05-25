var VccAds = new function(){
	this.show = function(zoneId){
        document.write('<div id="vccAds-box-' + zoneId + '"></div>');
		//get html
		$.ajax({
			url: baseUrl + '/advertise/Advertise/getAds',
			type: "post",
			dataType: "json",
			data: { zoneId: zoneId},
			success: function(res)
			{
				//$("#VccAds-" + zoneId).html(res.html);
                if(res.html != '')
                    $("#vccAds-box-" + zoneId).replaceWith(res.html);
			}
		});

	};
};

$(document).ready(function(){
    var zIds = '';
   $("VccAds").each(function(){
       if($(this).html())
       {
            $(this).attr('id', 'vccAds-box-' + $(this).html());
            zIds += $(this).html() + ',';
       }
   });
    $.ajax({
        url: baseUrl + '/advertise/Advertise/getAds',
        type: "post",
        dataType: "json",
        data: { zoneIds: zIds},
        success: function(res)
        {
            $.each(res.data, function(k, v){
                $("#vccAds-box-" + k).replaceWith(v);
            });
        }
    });
});
