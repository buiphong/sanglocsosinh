$(document).ready(function(){
    if($(".jhtmlarea").length > 0)
	    $(".jhtmlarea").htmlarea();
    if($(".treeview").length > 0)
        $(".treeview").treeview();
	
    if($("#frmEditNews").length > 0)
        $('#frmEditNews').validate();

    if($("#news_content").length > 0)
        CKEDITOR.replace('news_content', {
            toolbar: 'Basic',
            filebrowserBrowseUrl: baseUrl + '/Packages/pdw_file_browser/index.php?editor=ckeditor'
        });
    if($("#published_date").length > 0)
	    $("#published_date").mask("9999-99-99 99:99:99");

    // Chosen (chosen)
    if($('.chosen-select').length > 0)
    {
        $('.chosen-select').each(function(){
            var el = $(this);
            var search = (el.attr("data-nosearch") === "true") ? true : false,
            opt = {};
            if(search) opt.disable_search_threshold = 9999999;
            el.chosen(opt);
        });
    }
	
});

$("input[name='key[]']").change(function(){
	if (!$(this).is(":checked")) {
		$(this).parent().parent().removeClass('error');
		$(this).parent().parent().removeClass('selected');
	}
});


$("#frmSearchListNews input[name='status']").change(function(){
	$("#frmSearchListNews").submit();
});

function openFileBrowser(id)
{
	fileBrowserlink = baseUrl + "/Packages/pdw_file_browser/index.php?editor=standalone&returnID=" + id;
	window.open(fileBrowserlink,'pdwfilebrowser', 'width=900,height=650,scrollbars=no,toolbar=no,location=no');
}

//Load list news
function loadListNews()
{
	showLoadingPage();
	$.ajax({
		url: baseUrl + "/NewsCP/NewsCP/list",
		dataType: "json",
		type: "POST",
		data: {},
		success: function(res)
		{
			hideLoadingPage();
			if(res.success)
			{
				
			}
			else
			{
				
			}
		}
	});
}

function editNews(newsId)
{
	
}

/**
 * Duyệt xuất bản tin bài
 * @param newsId
 */
function publishNews(obj)
{
	$.ajax({
		url: baseUrl + "/NewsCP/NewsCP/publishNews",
		dataType: "json",
		data: {newsid: $(obj).attr("accessKey")},
		type: "POST",
		success: function(res)
		{
			showDialog(res.html, 'Duyệt xuất bản tin bài', 800);
		}
	});
}

function publishNewsPost(newsId)
{
	$.ajax({
		url: baseUrl + "/NewsCP/NewsCP/publishNewsPost",
		dataType: "json",
		data: {newsid: newsId},
		type: "POST",
		success: function(res)
		{
			if(res.success)
			{
				$('.close').click();
				window.location.reload();
			}
			else
			{
				alert(res.msg);
			}
		}
	});
}

function getDownNews(obj)
{
	if(confirm("Bạn muốn hạ tin bài này xuống?"))
	{
		$.ajax({
			url: baseUrl + "/NewsCP/NewsCP/getDownNews",
			dataType: "json",
			data: {newsid: $(obj).attr("accessKey")},
			type: "POST",
			success: function(res)
			{
				if(res.success)
				{
					window.location.reload();
				}
				else
				{
					alert(res.msg);
				}
			}
		});
	}
}

//Lựa chọn tin tức
function selectNews()
{
	if($('input[name="key[]"]:checked').length <= 0)
		alert('Chưa có bản ghi nào được chọn');
	else
	{
		var listId = "";
		$('input[name="key[]"]:checked').each(function(){
			if ($(this).attr("data-status") != 1) {
				$(this).parent().parent().addClass("error");
			}
			else
			{
				if(listId == "")
					listId = $(this).val();
				else
					listId += "," + $(this).val();
				$(this).parent().parent().addClass("selected");
			}
		});
		$.ajax({
			url: baseUrl + '/NewsCP/NewsCP/selectNews',
			dataType: "json",
			type: "POST",
			data: {listId: listId},
			success: function(res)
			{
				alert(res.msg);
			}
		});
	}
}

//Lấy tin đã chọn
function getSelectedNews(catid, typeid)
{
	if(!typeid)
		typeid = '';
	$.ajax({
		url: baseUrl + '/NewsCP/SpecialNewsCP/getSelectedNews',
		dataType: "json",
		type: "POST",
		data: {catid: catid, typeid: typeid},
		success: function(res)
		{
			if(res.success)
				window.location.reload();
			else
				alert(res.msg);
		}
	});
}

function moveDownSNews(obj)
{
	$.ajax({
		url: baseUrl + '/NewsCP/SpecialNewsCP/moveDown',
		dataType: "json",
		type: "POST",
		data: { id: $(obj).attr("accessKey")},
		success: function(res)
		{
			if(res.success)
				window.location.reload();
			else
				alert(res.msg);
		}
	});
}

function moveUpSNews(obj)
{
	$.ajax({
		url: baseUrl + '/NewsCP/SpecialNewsCP/moveUp',
		dataType: "json",
		type: "POST",
		data: { id: $(obj).attr("accessKey")},
		success: function(res)
		{
			if(res.success)
				window.location.reload();
			else
				alert(res.msg);
		}
	});
}

