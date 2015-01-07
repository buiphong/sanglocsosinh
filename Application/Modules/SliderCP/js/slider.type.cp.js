$(document).ready(function(){
    var oTable = $("#tableSliderType").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/SliderCP/SliderTypeCP/dataTable",
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('id', aData['id']);
        },
        "fnServerParams": function ( aoData ) {
            aoData.push( {} );
        },
        "aoColumns": [
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
                }
            },
            { "mData": "name" },
            { "mData": "order_no" },
            { "mData": "create_time" },
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/SliderCP/SliderTypeCP/edit?id='+ oObj.aData['id'] +'" title="Sửa"><i class="icon-edit"></i></a>' +
						'<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/SliderCP/SliderTypeCP/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
            }
        ]
    });

    $("#add-category-btn").live('click',function(){
		createLink = baseUrl + '/SliderCP/SliderTypeCP/create';
        $.ajax({
            url: createLink,
            type: "GET",
            async: false,
            success: function(res){
                showModal('Thêm mới nhóm Slider', res, 'large');
            }
        });
    });
});