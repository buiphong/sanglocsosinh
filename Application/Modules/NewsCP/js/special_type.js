$(document).ready(function(){
    if($("#tableNewsSType").length > 0)
    {
        var oTable = $("#tableNewsSType").DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bDestroy": true,
            "sPaginationType": "bootstrap",
            "sAjaxSource": "/NewsCP/SpecialTypeCP/list",
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                $(nRow).attr('id', aData['id']);
            },
            /*"fnServerParams": function ( aoData ) {
             { "name": "category_id", "value": $("#selected_catid").val() });
             },*/
            "aoColumns": [
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
                }
                },
                { "mData": "code" },
                { "mData": "title" },
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/NewsCP/SpecialTypeCP/edit?id='+ oObj.aData['id'] +'" title="Sửa"><i class="icon-edit"></i></a>' +
                        '<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/NewsCP/SpecialTypeCP/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
                }
            ]
        });
    }
});

$("#add-news-stype").click(function(){
    createLink = baseUrl + '/NewsCP/SpecialTypeCP/create';
    $.ajax({
        url: createLink,
        type: "GET",
        async: false,
        success: function(res){
            showModal('Thêm mới loại tin sắp xếp', res, 'large');
        }
    });
});