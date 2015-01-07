$(document).ready(function(){
    if($("#news-brief").length > 0)
        CKEDITOR.replace('news-brief', {
            toolbar: [['Source','Image','Font','FontSize','JustifyLeft', 'JustifyCenter', 'JustifyRight','JustifyBlock','List', 'Indent','Bold', 'Italic', 'Underline']],
            filebrowserBrowseUrl: baseUrl + '/Packages/pdw_file_browser/index.php?editor=ckeditor'
        });
	
    if($("#frmEditNews").length > 0)
        $('#frmEditNews').validate();

    if($(".form_datetime").length > 0)
    {
        $(".form_datetime").datetimepicker({
            isRTL: App.isRTL(),
            format: "yyyy-mm-dd hh:ii:ss",
            pickerPosition: (App.isRTL() ? "bottom-right" : "bottom-left")
        });
    }

    if($("#news_content").length > 0)
        CKEDITOR.replace('news_content', {
            toolbar: [
                { name: 'row1',
                    groups: [ 'mode', 'document', 'doctools', 'clipboard', 'undo','find', 'bidi', 'tools'],
                    items: [ 'Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-','Cut', 'Copy', 'Paste', 'PasteText',
                        'PasteFromWord', '-', 'Undo', 'Redo','Find', 'Replace', 'SelectAll', 'Scayt', '-',
                        'BidiLtr', 'BidiRtl', 'Language','Iframe','-', 'Maximize', 'ShowBlocks', 'RemoveFormat'] },
                '/',
                { name: 'row2',
                    groups: ['styles', 'align', 'cleanup' ],
                    items: ['Styles', 'Format', 'Font', 'FontSize', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight',
                        'JustifyBlock','-','TextColor', 'BGColor','-','PageBreak','HorizontalRule','-','Link', 'Unlink', 'Anchor'] },
                { name: 'row3',
                    items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-','NumberedList',
                        'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv','Image', 'Flash', 'Table',
                        'SpecialChar','Smiley','-', 'Form', 'Checkbox','Radio', 'TextField', 'Textarea', 'Select', 'Button',
                        ] }
                ],
            filebrowserBrowseUrl: baseUrl + '/Packages/pdw_file_browser/index.php?editor=ckeditor'
        });

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

    if($("#tableNews").length > 0)
    {
        var oTable = $("#tableNews").DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bDestroy": true,
            "sPaginationType": "bootstrap",
            "sAjaxSource": "/NewsCP/NewsCP/list",
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                $(nRow).attr('id', aData['id']);
            },
            "fnServerParams": function ( aoData ) {
                aoData.push( { "name": "status", "value": $("input[name='status']:checked").val() },
                    { "name": "category_id", "value": $("#selected_catid").val() });
            },
            "aoColumns": [
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
                }
                },
                { "mData": "title" },
                { "mData": "category" },
                { "mData": "created_date" },
                { "bSortable": false,"mData": "username" },
                { "bSortable": false,  "mData": "btn"}
            ]
        });
    }
});

$("input[name='key[]']").change(function(){
	if (!$(this).is(":checked")) {
		$(this).parent().parent().removeClass('error');
		$(this).parent().parent().removeClass('selected');
	}
});

$("#list-news-cat li a").click(function(){
    $("#selected_catid").val($(this).attr('data-id'));
    //Update title category
    $('#category-name').html(' - ' + $(this).html());
    $("#tableNews").dataTable()._fnAjaxUpdate();
});

$("#frmSearchListNews input[name='status']").change(function(){
    $("#tableNews").dataTable()._fnAjaxUpdate();
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
                $("#tableNews").dataTable()._fnAjaxUpdate();
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
                    $("#tableNews").dataTable()._fnAjaxUpdate();
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
			/*if ($(this).attr("data-status") != 1) {
				$(this).parent().parent().addClass("error");
			}
			else*/
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
				showNotification('Thông báo', res.msg);
			}
		});
	}
}

//Add tin đã chọn thành tin sắp xếp danh mục
function addSpecialCat()
{
    if($('input[name="key[]"]:checked').length <= 0)
        showNotification('Thông báo','Chưa có bản ghi nào được chọn');
    else
    {
        var listId = "";
        $('input[name="key[]"]:checked').each(function(){
            if(listId == "")
                listId = $(this).val();
            else
                listId += "," + $(this).val();
            $(this).parent().parent().addClass("selected");
        });
        $.ajax({
            url: baseUrl + '/NewsCP/SpecialNewsCP/addSpecialCat',
            dataType: "json",
            type: "POST",
            data: {listId: listId},
            success: function(res)
            {
                showNotification('Thông báo', res.msg);
            }
        });
    }
}

function loadPage(obj)
{
    $("#page").val($(obj).attr("accessKey"));
    document.frmSearchListNews.submit();
}
function deleteNewsAction()
{
    var listId = "";
    $('input[name="key[]"]:checked').each(function(){
        if ($(this).attr("data-status") == 1) {
            $(this).parent().parent().addClass("error");
        }
        else
        {
            if(listId == "")
                listId = $(this).val();
            else
                listId += "," + $(this).val();
        }
    });
    if (listId != '')
    {
        deleteNews(listId);
    }
    else
        alert('Chưa có mục nào được chọn');
}

function deleteNews(listId)
{
    $.ajax({
        url: "/NewsCP/NewsCP/delete",
        type: "POST",
        dataType: "json",
        data: {listid: listId},
        success: function(res){
            if(res.success)
            {
                $("#tableNews").dataTable()._fnAjaxUpdate();
            }
            else
                alert(res.msg);
        }
    });
}