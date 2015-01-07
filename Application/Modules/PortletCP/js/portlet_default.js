$(document).ready(function(){

});

$("#template").live('change', function(){
	template = $(this).val();
	//Load combobox layout
	loadCmbLayout(template);	
});

$("#layout").live('change', function(){
	template = $('#template').val();
	layout = $(this).val();
	//Load combobox region
	loadCmbRegion(template, layout);
});

function loadCmbLayout(template, selectVal)
{
	if(!selectVal)
		selectVal = '';
	$.ajax({
		url: baseUrl + '/portlet/PortletDefaultCP/getLayout',
		dataType: 'json',
		type: "POST",
		data: {template: template, selected: selectVal},
		success: function(res)
		{
			if(res.success)
			{
				$("#layout").replaceWith(res.html);
				layout=$('#layout').val();
				loadCmbRegion(template, layout);
			}
			else
				alert(res.msg);
		}
	});
}


function loadCmbRegion(template, layout)
{
	$.ajax({
		url: baseUrl + '/portlet/PortletDefaultCP/getRegion',
		dataType: 'json',
		type: "POST",
		data: {template:template, layout:layout},
		success: function(res)
		{
			if(res.success)
			{
				$("#region").replaceWith(res.html);
			}
			else
				alert(res.msg);
		}
	});
}
function loadPage(obj)
{
	$("#page").val($(obj).attr("accessKey"));
	document.frmPortletDf.submit();
}
function deleteAction()
{
	var listId = "";
	$('input[name="key[]"]:checked').each(function(){
		if(listId == "")
			listId = $(this).val();
		else
			listId += "," + $(this).val();
		count =+ 1;
	});
	if (listId != '')
	{
		$.ajax({
			url: baseUrl + "/portlet/PortletDefaultCP/delete",
			type: "POST",
			dataType: "json",
			data: {listid: listId},
			success: function(res){
				if(res.success)
				{
					$('input[name="key[]"]:checked').each(function(){
						$(this).parent().parent().remove();
					});
				}
				else
					alert(res.msg);
			}
		});
	}
}

$("#template").live('change', function(){
	template = $(this).val();
	//Load combobox layout
	loadCmbLayout(template);
});