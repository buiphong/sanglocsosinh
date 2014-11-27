$(document).ready(function(){
    if($("#tableNewsCat").length > 0)
    {
        var oTable = $("#tableNewsCat").DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bDestroy": true,
            "sPaginationType": "bootstrap",
            "sAjaxSource": "/NewsCP/CategoryCP/list",
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
                { "mData": "image_path" },
                { "mData": "title" },
                { "mData": "orderno" },
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/NewsCP/CategoryCP/edit?id='+ oObj.aData['id'] +'" title="Sửa"><i class="icon-edit"></i></a>' +
                        '<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/NewsCP/CategoryCP/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
                }
            ]
        });
    }
});

$("#add-news-cat").click(function(){
    createLink = baseUrl + '/NewsCP/CategoryCP/create';
    $.ajax({
        url: createLink,
        data: "parentid=" + $("#parentId").val(),
        type: "GET",
        async: false,
        success: function(res){
            showModal('Thêm mới danh mục tin', res, 'large');
        }
    });
});