$(document).ready(function(){
    if($("#tableFaq").length > 0)
    {
        var oTable = $("#tableFaq").DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bDestroy": true,
            "sPaginationType": "bootstrap",
            "sAjaxSource": "/FaqCP/FaqCP/list",
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                $(nRow).attr('id', aData['id']);
            },
            "fnServerParams": function ( aoData ) {
                aoData.push( { "name": "type", "value": $("#specialType").val()});
            },
            "aoColumns": [
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
                }
                },
                { "bSortable": false,"mData": "question" },
                { "bSortable": true,"mData": "created_time" },
                { "bSortable": false,"mData": "fullname" },
                { "bSortable": true,"mData": "orderno" },
                { "bSortable": true,"mData": "status" },
                { "bSortable": false, "mData": "btn"}
            ]
        });
    }

    if($("#detail_content").length > 0)
        CKEDITOR.replace('detail_content', {
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
            width: 780,
            height: 500,
            filebrowserBrowseUrl: baseUrl + '/Packages/pdw_file_browser/index.php?editor=ckeditor'
        });
});

function loadPage(obj)
{
    $("#page").val($(obj).attr("accessKey"));
    document.frmList.submit();
}

function deleteAction()
{
	var listId = "";
	$('input[name="key[]"]:checked').each(function(){
		if(listId == "")
			listId = $(this).val();
		else
			listId += "," + $(this).val();
	});
	if (listId != '')
	{
		$.ajax({
			url: "/FaqCP/FaqCP/delete",
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

function changeStatus(obj)
{
	status = $(obj).attr('data-status');
	id = $(obj).attr('data-id');
	if(status != '')
	{
		$.ajax({
			url: baseUrl+"/FaqCP/FaqCP/changeStatus",
			type: 'post',
			dataType: 'json',
			data:"status="+status+"&id="+id,
			success: function(res)
			{
				if(res.success)
				{
					$(obj).attr('data-status',res.status);
					$(obj).html(res.html);
				}
				else
				{
					alert(res.msg);
				}
			}
		});
	}
}

function pushUp(obj)
{		
	idPrev = $(obj).parents().parents().prev().find('.orderno').attr('data-id'); 
	orderPrev = $(obj).parents().parents().prev().find('.orderno').attr('data-order');
	if($(obj).attr('data-active') == "down")
	{
		idPrev = $(obj).parents().parents().next().find('.orderno').attr('data-id'); 
		orderPrev = $(obj).parents().parents().next().find('.orderno').attr('data-order');
	}
	
	orderno = $(obj).parents().children('.orderno').attr('data-order');
	id = $(obj).parents().children('.orderno').attr('data-id');
	if(orderno != '')
	{
		$.ajax({
			url: baseUrl+"/FaqCP/FaqCP/pushUp",
			type: 'post',
			dataType: 'json',
			data:"orderno="+orderno+"&id="+id+"&orderPrev="+orderPrev+"&idPrev="+idPrev,
			success: function(res)
			{
				if(res.success)
				{
					window.location.href = 	res.link;				
				}
				else
				{
					alert(res.msg);
				}
			}
		});
	}	
}
