$(document).ready(function(){
    var oTable = $("#tableProvince").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/ListCP/ProvinceCP/dataTable",
        "aoColumns": [
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'" />';
            }
            },
            { "mData": "name" },
            { "bSortable": false,"mData": "order_no" },
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/ListCP/ProvinceCP/edit?id='+ oObj.aData['id'] +'" title="Sửa"><i class="icon-edit"></i></a>' +
                        '<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/ListCP/ProvinceCP/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
            }
        ]
    });

    $("#add-province").live('click',function(){
        $.ajax({
            url: '/ListCP/ProvinceCP/create',
            type: "GET",
            async: false,
            success: function(res)
            {
                showModal('Thêm tỉnh thành mới',res,'large');
            }
        });
    });
})

function openFileBrowser(id){
    fileBrowserlink = baseUrl + "/Packages/pdw_file_browser/index.php?editor=standalone&returnID=" + id;
    window.open(fileBrowserlink,'pdwfilebrowser', 'width=900,height=650,scrollbars=no,toolbar=no,location=no');
}