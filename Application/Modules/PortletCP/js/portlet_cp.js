$(document).ready(function(){
	$(".treeview").treeview({
		collapsed: true
	});
});

$("#template").live('change', function(){
	template = $(this).val();
	//Load combobox layout
	loadCmbLayout(template);
});

function loadCmbLayout(template, selectVal)
{
	if(!selectVal)
		selectVal = '';
	$.ajax({
		url: baseUrl + '/PortletCP/Portlet/getLayout',
		dataType: 'json',
		type: "POST",
		data: {template: template, selected: selectVal},
		success: function(res)
		{
			if(res.success)
			{
				$("#layout").replaceWith(res.html);
			}
			else
				alert(res.msg);
		}
	});
}