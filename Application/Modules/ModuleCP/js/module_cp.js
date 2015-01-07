$(document).ready(function(){
    if($("#tableModule").length > 0)
    {
        var oTable = $("#tableModule").DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bDestroy": true,
            "sPaginationType": "bootstrap",
            "sAjaxSource": "/ModuleCP/ControlPanel/list",
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                $(nRow).attr('name', aData['name']);
            },
            "aoColumns": [
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['name'] +'"/>';
                }
                },
                { "mData": "name" },
                { "mData": "version" },
                { "mData": "update" },
                { "bSortable": false,"mData": "desc" },
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

$('body').on('click', '.btn-update-module', function(){
    if($(this).attr('accessKey'))
        updateModule($(this).attr('accessKey'));
});

//Update module
function updateModule(module)
{
    if(module)
    {
        $.ajax({
            url: baseUrl + '/ModuleCP/ControlPanel/updateModule',
            dataType: 'json',
            type: "POST",
            data: {module: module},
            success: function(res)
            {
                if(res.success)
                {
                    alert('ok');
                }
                else
                    alert(res.msg);
            }
        });
    }
}

