$(document).ready(function(){
    if($(".jhtmlarea").length > 0)
        $(".jhtmlarea").htmlarea();

    //$(".treeview").treeview();

    if($('.nestable').length > 0)
    {
        $('.nestable').nestable();
        $('.nestable').change(function(){
            updatePositionMenu();
        });
    }
});

$("#add-menu-btn").click(function(){
    createLink = baseUrl + '/MenuCP/Menu/create?type=' + $("#type_id").val();
    $.ajax({
        url: createLink,
        type: "GET",
        async: false,
        success: function(res){
            showModal('Thêm mới menu', res, 'large');
        }
    });
});

$("#btn-addMenuType").click(function(){
    createLink = baseUrl + '/MenuCP/MenuType/create';
    $.ajax({
        url: createLink,
        type: "GET",
        async: false,
        success: function(res){
            showModal('Thêm mới loại menu', res, 'large');
        }
    });
});

$("#template").live('change', function(){
	template = $(this).val();
	//Load combobox layout
	loadCmbLayout(template);
});

function updatePositionMenu()
{
    $.ajax({
        url: baseUrl + '/MenuCP/Menu/updatePosition',
        dataType: 'json',
        data: {data: $('.nestable').nestable('serialize')},
        success: function(res)
        {

        }
    });
}

function loadCmbLayout(template, selectVal)
{
	if(!selectVal)
		selectVal = '';
	$.ajax({
		url: baseUrl + '/MenuCP/Menu/getLayout',
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
            {
                showNotification('Thông báo hệ thống', res.msg);
                $("#layout").replaceWith(res.html);
            }
		}
	});
}

$("#link_type").live('change', function(){
    loadLinkType();
});

function loadLinkType()
{
    $.ajax({
        url: baseUrl + '/MenuCP/Menu/getLinkTypeValue',
        dataType: "json",
        data: { typeCode: $("#link_type").val(), value: $("#link_value").val()},
        type: "POST",
        success: function(res)
        {
            if(res.success)
            {
                $("#link-value").html(res.html);
                $('.chosen-select').each(function(){
                    var el = $(this);
                    var search = (el.attr("data-nosearch") === "true") ? true : false,
                        opt = {};
                    if(search) opt.disable_search_threshold = 9999999;
                    el.chosen(opt);
                });
            }
        }
    });
}

function openFileBrowser(id)
{
    fileBrowserlink = baseUrl + "/Packages/pdw_file_browser/index.php?editor=standalone&returnID=" + id;
    window.open(fileBrowserlink,'pdwfilebrowser', 'width=900,height=650,scrollbars=no,toolbar=no,location=no');
}

//reload sidebar menu type
function reloadSidebarMenuType()
{
    $.get('/MenuCP/Menu/sidebarMenuType', function(res){
        $("#sidebarMenuType-content").html(res);
    });
}