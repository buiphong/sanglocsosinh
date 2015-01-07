var parentRelative;
var nextElement;
var hiddenParent = new Array();
$(document).ready(function(){
	loadCPLayout();
    //router params checkbox
    $(".router-param-input").on('change', function(){
        cIndex = $(this).attr("data-num");
        if($(this).attr('checked'))
        {
            //check all prev param
            $(".router-param-input").each(function(){
                if($(this).attr('data-num') < cIndex)
                {
                    $(this).attr('checked',true);
                }
            })
        }
        else
        {
            //uncheck all prev param
            $(".router-param-input").each(function(){
                if($(this).attr('data-num') > cIndex)
                {
                    $(this).attr('checked',false);
                }
            })
        }
    });
    if(document.getElementsByTagName("object").length > 0)
    {
        document.getElementById('btn-hideFlash').style.display= 'inline-block';
    }
});

$("input[name='editLayout']").change(function(){
    val = 0;
    if($(this).is(':checked'))
        val = 1;
    $.ajax({
        url: baseUrl + '/LayoutCP/Render/enEditLayout',
        dataType: 'json',
        data: {val: val},
        type: 'POST',
        success: function(res)
        {
            window.location.reload();
        }
    });
});

function loadCPLayout()
{
    $('.renderCP-region-border').each(function(){
        //Get parent width, height
        parent = $(this).parent();
        parent.addClass($(this).attr('id'));
        parentOffset = parent.offset();
        if(parent.height() == 0)
            parent.css('height', '30');
        offsetLeft = parentOffset.left + parent.width()/2.2;
        offsetTop = parentOffset.top + parent.innerHeight() - 23;
        getParentRelative($(this));
        v = parentRelative;
        if(!v.is('body'))
        {
            vOffset = v.offset();
            offsetLeft = offsetLeft - vOffset.left;
            offsetTop = offsetTop - vOffset.top;
        }
        $(this).css('width', parent.width());
        $(this).css('height', (parent.height()));
        //$(this).children(".bottom-btn").css('padding-top', parent.height());
        $(this).next().eq(0).css('left', offsetLeft);
        $(this).next().eq(0).css('top', offsetTop + 30);
    });

    $('.renderCP-portlet-border.untreated').each(function(){
        $(this).removeClass('untreated');
        offsetLeft = 0; offsetTop = 0;
        getNextElement($(this));
        element = nextElement;
        if(element)
        {
            getParentRelative(element);
            element.addClass('portlet-content-border');
            element.attr('accessKey', $(this).attr('accessKey'));
            offset = element.offset();
            offsetLeft = offset.left;
            offsetTop = offset.top;
            //put inside portlet
            $('.portlet-content-border[accesskey=' + $(this).attr('accessKey') + ']').eq(0).prepend($(this).clone());
            $(this).remove();
        }
    });
}

var cmEditor;
var notAccept = ["SCRIPT","LINK"];
//Lấy phần tử tiếp theo, không chấp nhận thẻ script..
function getNextElement(obj)
{
    if(obj.prop('tagName'))
    {
        element = obj.next();
        if(element.prop("tagName"))
        {
            if($.inArray(element.prop("tagName"), notAccept) >= 0)
            {
                getNextElement(element);
            }
            else
                nextElement = element;
        }
    }
}

//Load param portlet
$("body").on('change', '#paramsType', function(){
	$.ajax({
		url: baseUrl + '/PortletCP/Portlet/loadDetailParams',
		dataType: "json",
		type: "POST",
		data: { type: $(this).val()},
		success: function(res)
		{
			if(res.success)
			{
				$("#portlet_params_value").html(res.html);
				updatePortletParam();
			}
		}
	});
});

//Portlet params
$("body").on('change', '#portletParams select, :text, textarea', function(){
	//update portletparam
	updatePortletParam();
});

function updatePortletParam()
{
	value = '';
	$("#portletParams select, #portletParams :text, #portletParams textarea").each(function(){
		val = $(this).val();
		if(val != '')
		{
			if(value != '')
				value += '&';
			value += $(this).attr("name") + '=' + val;
		}
	});
	$("#table-dialog-editPortlet #params").val(value);
}

$("body").on('click', '.cp-region-btn.addPortlet', function(){
	if($(this).attr("data-menuid") != '')
		addPortlet($(this).attr('data-region'), $(this).attr('data-menuid'), 'menu');
	else
		addPortlet($(this).attr('data-region'), $(this).attr('data-router'), 'router',$(this).attr('data-router-id'));
});

$("body").on('click','.cp-region-btn.edit', function(){
	editPortlet($(this).attr('data-id'), $(this).attr('data-type'));
});

$("body").on('click', '.cp-region-btn.remove', function(){
	removePortlet($(this).attr('data-id'), $(this).attr('data-type'));
});

$("body").on('click', '.cp-region-btn.moveup', function(){
	moveUpPortlet($(this).attr('data-id'), $(this).attr('data-type'));
});

$("body").on('click', '.cp-region-btn.movedown',function(){
	moveDownPortlet($(this).attr('data-id'), $(this).attr('data-type'));
});

$("body").on('click', '.cp-region-btn.edit-view-file', function(){
	editViewFile($(this).attr('data-id'), $(this).attr('data-type'));
});

//Edit view file
function editViewFile(portletId, type)
{
	$.ajax({
		url: url = baseUrl + '/PortletCP/Portlet/editViewFile',
		dataType: "json",
		type: "POST",
		data: {menuid: portletId, type: type},
		success: function(res)
		{
			if(res.success)
			{
				//Show dialog edit file view
				showModal('Edit view', res.html, 'large');
			}
			else
				alert(res.msg);
		}
	});
}

function updateViewFile()
{
	$.ajax({
		url: baseUrl + '/PortletCP/Portlet/updateViewContent',
		dataType: "json",
		type: "POST",
		data: {viewFile: $("#viewFilePath").val(), content: cmEditor.getValue()},
		success: function(res)
		{
			if(res.success)
			{
				window.location.reload();
			}
			else
				alert(res.msg);
		}
	});
}

//add portlet
function addPortlet(region, itemId, type, routerId)
{
    $("#layout-render-loading").show();
	url = baseUrl + '/PortletCP/Portlet/addPortlet';
    if(!routerId)
        routerId = '';
	$.ajax({
		url: url,
		dataType: "json",
		data: { region: region, itemid: itemId, type: type, routerid: routerId},
		type: "POST",
		success: function(res)
		{
            $("#layout-render-loading").hide();
			if(res.success)
			{
				showModal('Add portlet: ' + region,res.html, 'large');
			}
		}
	});
}

function addPortletPost(obj,region, itemid, type)
{
    $("#layout-render-loading").show();
	$.ajax({
		url: baseUrl + '/PortletCP/Portlet/addPortletPost',
		dataType: "json",
		type: "POST",
		data: {region: '{' + region + '}', itemid: itemid, module: $(obj).attr('data-module'), 
			controller: $(obj).attr('data-controller'), action: $(obj).attr("data-action"),
			name: $(obj).attr('data-name'), portletid: $(obj).attr("data-id"), type: type,
            routerid: $(obj).attr('data-router-id'), template: $("#CP-cmbTemplate").val(), layout: $('#CP-cmbLayout').val()},
		success: function(res)
		{
            $("#layout-render-loading").hide();
			if(res.success)
			{
				$('.close').click();
				//window.location.reload();
                //Add portlet to region
                el = $(res.html);
                $('.region-' + region).eq(0).append(res.html);
                //reload button for prev portlet
                if($('.region-' + region + ' .portlet-content-border').length > 0)
                {
                    pos = $('.region-' + region + ' .portlet-content-border').length - 1;
                    updateCPBTN($('.region-' + region + ' .portlet-content-border').eq(pos), 1);
                }
                loadCPLayout();
			}
			else
				alert(res.msg);
		}
	});
}

//remove portlet
function removePortlet(portletId, type)
{
	if(confirm("Bạn muốn gỡ portlet này?"))
	{
        $("#layout-render-loading").show();
		$.ajax({
			url: baseUrl + '/PortletCP/Portlet/removePortlet',
			dataType: "json",
			data: {portletId: portletId, type: type},
			type: "POST",
			success: function(res)
			{
                $("#layout-render-loading").hide();
				if(res.success)
                {
                    obj = $(".portlet-content-border[accesskey=" + portletId + "]").next('.portlet-content-border').eq(0);

                    if(obj.length <= 0)
                        obj =  $(".portlet-content-border[accesskey=" + portletId + "]").prev('.portlet-content-border').eq(0);
                    //remove cp button and portlet
                    $(".portlet-content-border[accesskey=" + portletId + "]").eq(0).remove();
                    if(obj.length > 0)
                        updateCPBTN(obj, 0);
                    loadCPLayout();
                }
			}
		});
	}
}

//edit portlet
function editPortlet(portletId, type)
{
    $("#layout-render-loading").show();
    $.ajax({
        url: baseUrl + '/PortletCP/Portlet/editPortlet',
        dataType: "json",
        type: "POST",
        data: { portletId: portletId, type: type},
        success: function(res)
        {
            $("#layout-render-loading").hide();
            if(res.success)
            {
                showModal('Sửa portlet', res.html, 'large');
            }
            else
                alert(res.msg);
        }
    });
}

function editPortletPost()
{
	data = {params: $("#params").val(),
        portletId: $("#portletId").val(),
        title: $("#portletTitle").val(),
	    /*values: cmEditor.getValue(), */
        skin: $("#skin").val(), cache_time: $("#cache_time").val(), type: $("#portletType").val()};

    $("#layout-render-loading").show();
	$.ajax({
		url: baseUrl + '/PortletCP/Portlet/editPortletPost',
		dataType: "json",
		type: "POST",
		data: data,
		success: function(res)
		{
            $("#layout-render-loading").hide();
			if(res.success)
			{
                //reload portlet
                $(".portlet-content-border[accesskey=" + $("#portletId").val() + ']').eq(0).replaceWith(res.html);
				$('.close').click();
                loadCPLayout();
			}
			else
				alert(res.msg);
		}
	});
}

//moveup portlet
function moveUpPortlet(portletId, type)
{
    $("#layout-render-loading").show();
	$.ajax({
		url: baseUrl + '/PortletCP/Portlet/moveUpPortlet',
		dataType: "json",
		type: "POST",
		data: {portletid: portletId, type: type},
		success: function(res)
		{
            $("#layout-render-loading").hide();
			if(res.success)
			{
				//window.location.reload();
                //change position portlet
                currPortlet = $(".portlet-content-border[accesskey=" + portletId + "]").eq(0);
                interchangeDownPortlet(currPortlet.prev(), currPortlet);
                loadCPLayout();
			}
			else
				alert(res.msg);
		}
	});
}

//movedown portlet
function moveDownPortlet(portletId, type)
{
    $("#layout-render-loading").show();
	$.ajax({
		url: baseUrl + '/PortletCP/Portlet/moveDownPortlet',
		dataType: "json",
		type: "POST",
		data: {portletid: portletId, type: type},
		success: function(res)
		{
            $("#layout-render-loading").hide();
			if(res.success)
			{
                //change position portlet
                currPortlet = $(".portlet-content-border[accesskey=" + portletId + "]").eq(0);
                interchangeDownPortlet(currPortlet, currPortlet.next());
                loadCPLayout();
                //window.location.reload();
			}
			else
				alert(res.msg);
		}
	});
}

var currPortletGroup;
//Lọc danh sách portlet theo nhóm
function loadListPortlet(obj)
{
	$(".list-portlet-dialog .treeview a").removeClass('current');
	$(obj).addClass('current');
	group = $(obj).attr("accessKey");
	currPortletGroup = obj;
	if(group == 'all')
	{
		$(".portlet-list-dialog .portlet-item").show();
	}
	else
	{
		//Show all portlet in this group
		$(".portlet-list-dialog .portlet-item").hide();
		$(".portlet-list-dialog .portlet-item").each(function(){
			if($(this).attr("data-group") == group)
			{
				$(this).show();
			}
		});
	}
}

function getParentRelative(obj)
{
    if((!obj.is('body') && obj.css('position') != 'relative') || obj.is(':hidden'))
    {
        if(obj.prop("tagName"))
        {
            parentRelative = obj;
            rParent = obj.parent();
            if(rParent.prop("tagName"))
            {
                getParentRelative(rParent);
            }
        }
    }
}

function ToggleFlash()
{
    $("object").toggle();
}

//Load layout for main cmbLayout
$("body").on('change', '#CP-cmbTemplate', function(){
    $.ajax({
        url: '/LayoutCP/Render/getLayout',
        dataType: 'json',
        data: { template: $("#back-controlPanel #CP-cmbTemplate").val()},
        type:"POST",
        success: function(res)
        {
            if(res.success)
                $("#back-controlPanel #CP-cmbLayout").replaceWith(res.html);
        }
    });
});

$("body").on('click', '#CP-btnChangeLayout', function(){
    //update template, layout
    $.ajax({
        url: '/LayoutCP/Render/updateLayout',
        dataType: "json",
        type: "POST",
        data: $("#CP-frmUpdateLayout").serialize(),
        success: function(res)
        {
            if(res.success)
               window.location.reload();
        }
    });
    return false;
});

//Change id, accesskey button cp portlet
function changeDataPortletButton(from, to)
{
    item1 = $(".renderCP-portlet-border[accesskey=" + from + "]").eq(0);
    item2 = $(".renderCP-portlet-border[accesskey=" + to + "]").eq(0);
    //Change for buttons
    $(".renderCP-portlet-border[accesskey=" + from + "] .cp-region-btn").each(function(){
        $(this).attr('data-id', to);
    });
    $(".renderCP-portlet-border[accesskey=" + to + "] .cp-region-btn").each(function(){
        $(this).attr('data-id', from);
    });
    //Change for border button
    item1.attr('accesskey', to);
    item2.attr('accesskey', from);
}

//Update button for portlet
//relEL: -1: has prev element, 0: has no prev or next element, 1: has next element.
function updateCPBTN(obj, relEL)
{
    //Check for prev portlet and next portlet
    if(obj.next('.portlet-content-border').length > 0 || relEL == 1)
    {
        //append btn move down
        if(obj.find('.cp-region-btn.movedown').eq(0).length <= 0)
        {
            btnEdit = obj.find('.cp-region-btn.edit').eq(0);
            obj.find('.cp-region-btn.edit').eq(0).before('<a class="cp-region-btn movedown" title="Move down" ' +
                'data-id="'+obj.attr('accessKey')+'" data-type="'+btnEdit.attr('data-type')+'">&nbsp;</a>');
        }
    }
    else if(obj.find('.cp-region-btn.movedown').eq(0).length > 0)
    {
        //remove btn move down
        obj.find('.cp-region-btn.movedown').remove();
    }
    if(obj.prev('.portlet-content-border').length > 0 || relEL == -1)
    {
        //Append btn move up
        if(obj.find('.cp-region-btn.moveup').eq(0).length <= 0)
        {
            btnEdit = obj.find('.cp-region-btn.edit').eq(0);
            obj.find('.top-btn').eq(0).before('<a class="cp-region-btn moveup" title="Move up" ' +
                'data-id="'+obj.attr('accessKey')+'" data-type="'+btnEdit.attr('data-type')+'">&nbsp;</a>');
        }
    }
    else if(obj.find('.cp-region-btn.moveup').eq(0).length > 0)
    {
        //remove btn move up
        obj.find('.cp-region-btn.moveup').remove();
    }
}

//Change postion two portlet
function interchangeDownPortlet(pOne, pTwo)
{
    //Update for button move up
    if(pOne.find('.cp-region-btn.moveup').length > 0)
    {
        //update button for pTwo
        if(pTwo.find('.cp-region-btn.moveup').length <= 0)
        {
            pTwo.find('.top-btn').eq(0).prepend(pOne.children('.cp-region-btn.moveup').eq(0).clone());
            //update key
            pTwo.find('.cp-region-btn.moveup').eq(0).attr('data-id', pTwo.attr('accessKey'));
            //Remove button moveup pOne
            pOne.find('.cp-region-btn.moveup').eq(0).remove();
        }
    }
    else
    {
        //update button for pTwo
        if(pTwo.find('.cp-region-btn.moveup').length > 0)
        {
            pOne.find('.top-btn').eq(0).prepend(pTwo.find('.cp-region-btn.moveup').eq(0).clone());
            //update key
            pOne.find('.cp-region-btn.moveup').eq(0).attr('data-id', pOne.attr('accessKey'));
            //Remove button moveup pTwo
            pTwo.find('.cp-region-btn.moveup').eq(0).remove();
        }
    }
    //Update for button move down
    if(pOne.find('.cp-region-btn.movedown').length > 0)
    {
        //update button for pTwo
        if(pTwo.find('.cp-region-btn.movedown').length <= 0)
        {
            pTwo.find('.cp-region-btn.edit').eq(0).before(pOne.find('.cp-region-btn.movedown').eq(0).clone());
            //update key
            pTwo.find('.cp-region-btn.movedown').eq(0).attr('data-id', pTwo.attr('accessKey'));
            //Remove button moveup pOne
            pOne.find('.cp-region-btn.movedown').eq(0).remove();
        }
    }
    else
    {
        //update button for pTwo
        if(pTwo.find('.cp-region-btn.movedown').length > 0)
        {
            pOne.find('.cp-region-btn.edit').eq(0).before(pTwo.find('.cp-region-btn.movedown').eq(0).clone());
            //update key
            pOne.find('.cp-region-btn.movedown').eq(0).attr('data-id', pOne.attr('accessKey'));
            //Remove button moveup pOne
            pTwo.find('.cp-region-btn.movedown').eq(0).remove();
        }
    }

    //Change position
    content = pTwo.clone();
    pOne.before(content);
    pTwo.remove();
}

//Update portlet CP button
function updatePortletCPButton(pId, type)
{
    $.ajax({
        url: baseUrl + '/PortletCP/Portlet/getPortletBtn',
        type: 'GET',
        dataType:'json',
        data: {pid: pId, type: type},
        success: function(res)
        {
            if(res.success)
            {
                //replace with exist element
                if($('.renderCP-portlet-border[accesskey=' + pId + ']').length > 0)
                {
                    $('.renderCP-portlet-border[accesskey=' + pId + ']').replaceWith(res.html);
                }
                else if($('.portlet-content-border[accesskey=' + pId + ']').length > 0)
                {
                   //append befor element
                   $('.portlet-content-border[accesskey=' + pId + ']').eq(0).before(res.html);
                }
            }
        }
    });
}
