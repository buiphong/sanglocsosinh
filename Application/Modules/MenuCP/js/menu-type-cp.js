$(document).ready(function(){
    var oTable = $("#tableMenuType").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/MenuCP/MenuType/dataTable",
        "aoColumns": [
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'" />';
            }},
            { "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<a href="/MenuCP/Menu/list?type='+oObj.aData['id']+'">' + oObj.aData['type_name'] + '</a>';
            }},
            { "bSortable": false,"mData": "type_desc" },
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/MenuCP/MenuType/edit?id='+ oObj.aData['id'] +'" title="Sửa"><i class="icon-edit"></i></a>' +
                    '<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/MenuCP/MenuType/delete" title="Xóa"><i class="icon-trash"></i></a>';
            }
            }
        ]
    });

    $("#add_menu_type").live('click',function(){
        $.ajax({
            url: '/MenuCP/MenuType/create',
            type: "GET",
            async: false,
            success: function(res)
            {
                showModal('Thêm loại menu mới',res,'large');
            }
        });
    });
});