$(document).ready(function(){
    if($("#tableBionetContact").length > 0)
    {
        var oTable = $("#tableBionetContact").DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bDestroy": true,
            "sPaginationType": "bootstrap",
            "sAjaxSource": "/BionetContactCP/BionetContactCP/list",
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                $(nRow).attr('id', aData['id']);
            },
            "aoColumns": [
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
                }
                },
                { "mData": "title" },
                { "mData": "order_no" },
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/BionetContactCP/BionetContactCP/edit?id='+ oObj.aData['id'] +'" title="Sửa"><i class="icon-edit"></i></a>' +
                        '<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/BionetContactCP/BionetContactCP/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
                }
            ]
        });
    }
});

$("#add-bionet-contact").click(function(){
    createLink = baseUrl + '/BionetContactCP/BionetContactCP/create';
    $.ajax({
        url: createLink,
        type: "GET",
        async: false,
        success: function(res){
            showModal('Thêm mới Điểm thu mẫu', res, 'large');
        }
    });
});