/**
 * Created by buiph_000 on 4/1/14.
 */
$(document).ready(function(){
    if($("#tableAction").length > 0)
    {
        var oTable = $("#tableAction").DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bDestroy": true,
            "sPaginationType": "bootstrap",
            "sAjaxSource": "/ActionsCP/Actions/list",
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                $(nRow).attr('id', aData['id']);
            },
            "fnServerParams": function ( aoData ) {
                aoData.push( { "name": "groupid", "value": $("input[name='group_id']").val() });
            },
            "aoColumns": [
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
                }
                },
                { "mData": "name" },
                { "mData": "module" },
                { "mData": "controller" },
                { "mData": "action" },
                { "mData": "description" },
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<a  href="javascript:" class="btn mini purple  frm-edit-btn-ajax" data-editUrl="/ActionsCP/Actions/edit?id='+ oObj.aData['id'] +'" title="Sửa chức năng"><i class="icon-edit"></i></a>' +
                        '<a href="javascript:" class="btn mini black frm-delete-btn" data-id="'+ oObj.aData['id'] +'" data-delUrl="/ActionsCP/Actions/delete" title="Xóa"><i class="icon-trash"></i></a>';
                }
                }
            ]
        });
    }
});

$("#add-action-btn").click(function(){
    createLink = baseUrl + '/ActionsCP/Actions/create?group=' + $("#group_id").val();
    $.ajax({
        url: createLink,
        type: "GET",
        async: false,
        success: function(res){
            showModal('Thêm mới chức năng', res, 'large');
        }
    });
});