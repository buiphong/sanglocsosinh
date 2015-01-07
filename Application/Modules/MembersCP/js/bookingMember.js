$(document).ready(function(){
    var oTable = $("#tableBookingMember").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/MembersCP/MemberCP/listBooking",
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('id', aData['id']);
        },
        "fnServerParams": function ( aoData ) {
            aoData.push( { "name": "member_id", "value": $("#member_id").val() } );
        },
        "aoColumns": [
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
            }
            },
            { "bSortable": false, "mData": "fullname" },
            { "mData": "from" },
            { "mData": "to" },
            { "mData": "depart_date" },
            { "mData": "return_date" },
            { "mData": "quantity_passengers" },
            { "bSortable": false, "mData": "type_booking" },
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<a  href="javascript:" class="btn mini purple frm-edit-btn-ajax" data-editUrl="' + oObj.aData['viewPassengerLink'] + '" title="Danh sách hành khách"><i class="icon-list"></i></a>';
            }
            }
        ]
    });
})