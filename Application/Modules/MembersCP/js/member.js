$(document).ready(function(){
    var oTable = $("#tableMember").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/MembersCP/MemberCP/dataTable",
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('id', aData['id']);
        },
        "aoColumns": [
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
                }
            },
            { "mData": "fullname" },
            { "mData": "username" },
            { "mData": "email" },
            { "mData": "created_date" },
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<a  href="javascript:" class="btn mini purple frm-edit-btn-ajax" data-editUrl="' + oObj.aData['detailLink'] + '" title="Chi tiết"><i class="icon-list"></i></a>' +
						'<a  href="javascript:" class="btn mini purple frm-edit-btn-ajax" data-editUrl="' + oObj.aData['editLink'] + '" title="Sửa"><i class="icon-edit"></i></a>' +
                        oObj.aData['viewBookingLink'] +
						'<a  href="javascript:" class="btn mini purple frm-edit-btn-ajax" data-editUrl="' + oObj.aData['changePassLink'] + '" title="Thay đổi mật khẩu"><i class="icon-wrench"></i></a>' +
						'<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/MembersCP/MemberCP/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
            }
        ]
    });
    $("#add-member-btn").live('click',function(){
        createLink = baseUrl + '/MembersCP/MemberCP/create';
        $.ajax({
            url: createLink,
            type: "GET",
            async: false,
            success: function(res)
            {
                showModal('Thêm danh mục sản phẩm',res,'large');
            }
        });
    });
});

