$(document).ready(function(){
    var oTable = $("#tableVideoAlbum").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/GalleryCP/VideoAlbum/dataTable",
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('id', aData['id']);
        },
        "fnServerParams": function ( aoData ) {
            aoData.push({ "name": "parent_id", "value": $("#parent_id").val()});
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
                return  '<a href="/GalleryCP/Videos/index?album_id='+ oObj.aData['id'] + '" class="btn mini black" data-id="'+ oObj.aData['id'] + '" title="Danh sách video"><i class="icon-list"></i></a>'
						+ '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/GalleryCP/VideoAlbum/edit?id='+ oObj.aData['id'] +'" title="Sửa"><i class="icon-edit"></i></a>' + '<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/GalleryCP/VideoAlbum/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
            }
        ]
    });

    $("#add-category-btn").live('click',function(){
		createLink = baseUrl + '/GalleryCP/VideoAlbum/create?parent_id=' + $("#parent_id").val();
        $.ajax({
            url: createLink,
            type: "GET",
            async: false,
            success: function(res){
                showModal('Thêm mới Album video', res, 'large');
            }
        });
    });
});