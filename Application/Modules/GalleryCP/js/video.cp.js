$(document).ready(function(){
    var oTable = $("#tableVideos").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/GalleryCP/Videos/dataTable",
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('id', aData['id']);
        },
        "fnServerParams": function ( aoData ) {
            aoData.push({ "name": "album_id", "value": $("#album_id").val()});
        },
        "aoColumns": [
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
                }
            },
            { "mData": "name" },
            { "mData": "orderno" },
            { "mData": "status" },
            { "mData": "create_time" },
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/GalleryCP/Videos/edit?id='+ oObj.aData['id'] +'" title="Sửa"><i class="icon-edit"></i></a>' +
						'<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/GalleryCP/Videos/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
            }
        ]
    });

    $("#add-category-btn").live('click',function(){
		createLink = baseUrl + '/GalleryCP/Videos/create?album_id=' + $("#album_id").val();
        $.ajax({
            url: createLink,
            type: "GET",
            async: false,
            success: function(res){
                showModal('Thêm mới Video', res, 'large');
            }
        });
    });
});