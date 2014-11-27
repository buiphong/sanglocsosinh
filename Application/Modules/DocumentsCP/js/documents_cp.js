$(document).ready(function(){
	//$(".treeview").treeview();
	
	$('#frmEditDocuments').validate();

    if($("#tableDocument").length > 0)
    {
        var oTable = $("#tableDocument").DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bDestroy": true,
            "sPaginationType": "bootstrap",
            "sAjaxSource": "/DocumentsCP/DocumentsCP/list",
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                $(nRow).attr('id', aData['id']);
            },
            "fnServerParams": function ( aoData ) {
                aoData.push( { "name": "parentid", "value": $("#parentId").val() });
            },
            "aoColumns": [
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
                }
                },
                { "mData": "title" },
                { "mData": "category" },
                { "mData": "created_time" },
                { "bSortable": false, "mData": "btn"}
            ]
        });
    }
});

$("#add-document").click(function(){
    createLink = baseUrl + '/DocumentsCP/DocumentsCP/create';
    $.ajax({
        url: createLink,
        data: "category=" + $("#document_category").val(),
        type: "GET",
        async: false,
        success: function(res){
            showModal('Thêm mới tài liệu', res, 'large');
        }
    });
});

$("input[name='key[]']").change(function(){
	if (!$(this).is(":checked")) {
		$(this).parent().parent().removeClass('error');
		$(this).parent().parent().removeClass('selected');
	}
});


$("#frmSearchListDocuments input[name='status']").change(function(){
	$("#frmSearchListDocuments").submit();
});

function openFileBrowser(id)
{
	fileBrowserlink = baseUrl + "/Packages/pdw_file_browser/index.php?editor=standalone&returnID=" + id;
	window.open(fileBrowserlink,'pdwfilebrowser', 'width=900,height=650,scrollbars=no,toolbar=no,location=no');
}

//Load list documents
function loadListDocuments()
{
	showLoadingPage();
	$.ajax({
		url: baseUrl + "/DocumentsCP/DocumentsCP/list",
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

function updateSeoUrl(a, b)
{
	$(b).val(seoTitleGenerate($(a).val()));
}

/**
 * Duyệt xuất bản thư viện, tài liệu
 * @param documentId
 */
function publishDocuments(obj)
{
	$.ajax({
		url: baseUrl + "/DocumentsCP/DocumentsCP/publishDocuments",
		dataType: "json",
		data: {documentsid: $(obj).attr("accessKey")},
		type: "POST",
		success: function(res)
		{
			showDialog(res.html, 'Duyệt xuất bản thư viện, tài liệu', 800);
		}
	});
}

function publishDocumentsPost(documentId)
{
	$.ajax({
		url: baseUrl + "/DocumentsCP/DocumentsCP/publishDocumentsPost",
		dataType: "json",
		data: {documentsid: documentId},
		type: "POST",
		success: function(res)
		{
			if(res.success)
			{
				$('.close').click();
                $("#tableDocument").dataTable()._fnAjaxUpdate();
			}
			else
			{
				alert(res.msg);
			}
		}
	});
}

function getDownDocuments(obj)
{
	if(confirm("Bạn muốn hạ thư viện, tài liệu này xuống?"))
	{
		$.ajax({
			url: baseUrl + "/DocumentsCP/DocumentsCP/getDownDocuments",
			dataType: "json",
			data: {documentsid: $(obj).attr("accessKey")},
			type: "POST",
			success: function(res)
			{
				if(res.success)
				{
                    $("#tableDocument").dataTable()._fnAjaxUpdate();
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
function selectDocuments()
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
			url: baseUrl + '/DocumentsCP/DocumentsCP/selectDocuments',
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
function getSelectedDocuments(catid, typeid)
{
	if(!typeid)
		typeid = '';
	$.ajax({
		url: baseUrl + '/DocumentsCP/SpecialDocumentsCP/getSelectedDocuments',
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

function moveDownSDocuments(obj)
{
	$.ajax({
		url: baseUrl + '/DocumentsCP/SpecialDocumentsCP/moveDown',
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

function moveUpSDocuments(obj)
{
	$.ajax({
		url: baseUrl + '/DocumentsCP/SpecialDocumentsCP/moveUp',
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
function detailDoc(obj)
{
	$.ajax({
		url: baseUrl + '/DocumentsCP/DocumentsCP/publishDocuments',
		dataType: "json",
		type: "POST",
		data: "documentsid="+$(obj).attr("document_id")+"& view="+$(obj).attr("view"),
		success: function(res)
		{
			if(res.success)
			{
				showDialog(res.html,res.title, 800);

			}
			else
				showNotification(res.msg);
		}
	});
}