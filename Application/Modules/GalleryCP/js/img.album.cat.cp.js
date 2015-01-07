$(document).ready(function(){
    var oTable = $("#tableImgAlbumCat").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/GalleryCP/ImgAlbumCategory/dataTable",
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
            { "mData": "desc" },
            { "mData": "order_no" },
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/GalleryCP/ImgAlbumCategory/edit?id='+ oObj.aData['id'] +'" title="Sửa"><i class="icon-edit"></i></a>' +
						'<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/GalleryCP/ImgAlbumCategory/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
            }
        ]
    });

    $("#add-category-btn").live('click',function(){
		createLink = baseUrl + '/GalleryCP/ImgAlbumCategory/create?parent_id=' + $("#parent_id").val();
        $.ajax({
            url: createLink,
            type: "GET",
            async: false,
            success: function(res){
                showModal('Thêm mới danh mục album ảnh', res, 'large');
            }
        });
    });
});