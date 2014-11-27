$(document).ready(function(){
    if($("#tableSpecialNews").length > 0)
    {
        var oTable = $("#tableSpecialNews").DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bDestroy": true,
            "sPaginationType": "bootstrap",
            "sAjaxSource": "/NewsCP/SpecialNewsCP/list",
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                $(nRow).attr('id', aData['id']);
            },
            "fnServerParams": function ( aoData ) {
                aoData.push( { "name": "type", "value": $("#specialType").val()});
            },
            "aoColumns": [
                { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                    return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
                }
                },
                { "bSortable": false,"mData": "title" },
                { "bSortable": false,"mData": "typeTitle" },
                { "bSortable": false,"mData": "orderno" },
                { "bSortable": false, "mData": "btn"}
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

//Lấy tin đã chọn
function getSelectedNews(catid, typeid)
{
    if(!typeid)
        typeid = '';
    $.ajax({
        url: baseUrl + '/NewsCP/SpecialNewsCP/getSelectedNews',
        dataType: "json",
        type: "POST",
        data: {catid: catid, typeid: typeid},
        success: function(res)
        {
            if(res.success)
                $("#tableSpecialNews").dataTable()._fnAjaxUpdate();
            else
                alert(res.msg);
        }
    });
}

function moveDownSNews(obj)
{
    $.ajax({
        url: baseUrl + '/NewsCP/SpecialNewsCP/moveDown',
        dataType: "json",
        type: "POST",
        data: { id: $(obj).attr("accessKey")},
        success: function(res)
        {
            if(res.success)
                $("#tableSpecialNews").dataTable()._fnAjaxUpdate();
            else
                alert(res.msg);
        }
    });
}

function moveUpSNews(obj)
{
    $.ajax({
        url: baseUrl + '/NewsCP/SpecialNewsCP/moveUp',
        dataType: "json",
        type: "POST",
        data: { id: $(obj).attr("accessKey")},
        success: function(res)
        {
            if(res.success)
                $("#tableSpecialNews").dataTable()._fnAjaxUpdate();
            else
                alert(res.msg);
        }
    });
}