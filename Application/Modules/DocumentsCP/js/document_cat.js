$(document).ready(function(){
    if($("#tableDocumentCat").length > 0)
    {
        var oTable = $("#tableDocumentCat").DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bDestroy": true,
            "sPaginationType": "bootstrap",
            "sAjaxSource": "/DocumentsCP/CategoryDocumentsCP/list",
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
                { "mData": "orderno" },
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/DocumentsCP/CategoryDocumentsCP/edit?id='+ oObj.aData['id'] +'" title="Sửa"><i class="icon-edit"></i></a>' +
                        '<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/DocumentsCP/CategoryDocumentsCP/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
                }
            ]
        });
    }
});

$("#add-document-cat").click(function(){
    createLink = baseUrl + '/DocumentsCP/CategoryDocumentsCP/create';
    $.ajax({
        url: createLink,
        data: "parentid=" + $("#parentId").val(),
        type: "GET",
        async: false,
        success: function(res){
            showModal('Thêm mới danh mục tài liệu', res, 'large');
        }
    });
});