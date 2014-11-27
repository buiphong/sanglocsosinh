$(document).ready(function(){
    var oTable = $("#tableImgAlbum").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/GalleryCP/ImgAlbum/dataTable",
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('id', aData['id']);
        },
        "fnServerParams": function ( aoData ) {
            aoData.push({ "name": "catid", "value": $("#catid").val()});
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
                return  '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/GalleryCP/ImgAlbum/edit?id='+ oObj.aData['id'] +'" title="Sửa"><i class="icon-edit"></i></a>' +
						'<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/GalleryCP/ImgAlbum/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
            }
        ]
    });

    $("#add-category-btn").live('click',function(){
		createLink = baseUrl + '/GalleryCP/ImgAlbum/create?catid=' + $("#catid").val();
        $.ajax({
            url: createLink,
            type: "GET",
            async: false,
            success: function(res){
                showModal('Thêm mới Album ảnh', res, 'large');
            }
        });
    });
    //load
    $("#list-album-cat a").each(function(){
        hash = window.location.hash;
        if($(this).attr('href') == hash)
        {
            $(this).addClass('clr');
            $("#catid").val($(this).attr('data-id'));
            $("#tableImgAlbum").DataTable()._fnAjaxUpdate();
            $("#categoryName").html($(this).html());
        }
    });

    $("#list-album-cat a").click(function(){
        if(!$(this).hasClass('clr'))
        {
            $("#catid").val($(this).attr('data-id'));
            $("#tableImgAlbum").DataTable()._fnAjaxUpdate();
            $("#categoryName").html($(this).html());

            $('#list-album-cat a.clr').each(function(){
                $(this).removeClass('clr');
            });
            $(this).addClass('clr');
        }
    });
});
