$(document).ready(function(){
    var oTable = $("#tableRelativeNews").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/NewsCP/NewsRelativeCP/list",
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('id', aData['id']);
        },
        "fnServerParams": function ( aoData ) {
            aoData.push( { "name": "newsId", "value": $("#relativeNewsId").val() });
        },
        "aoColumns": [
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
            }
            },
            { "bSortable": false,"mData": "title" },
            { "bSortable": false,"mData": "category" },
            { "bSortable": false,"mData": "order_no" },
            { "bSortable": false, "sDefaultContent": "", "mData": "btn"}
        ]
    });
})

//Add relative news
function addRelativeNews()
{
    $.ajax({
        url: "/NewsCP/NewsRelativeCP/showSelectNews",
        type: "GET",
        success: function(res)
        {
            showDialog(res, 'Lựa chọn tin', 'large');
            getListSelectNews();
        }
    });
}

function getListSelectNews()
{
    var oTable = $("#tableSelectNews").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/NewsCP/NewsRelativeCP/listSelectNews",
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('id', aData['id']);
        },
        "aoColumns": [
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<input type="checkbox" name="news_id[]" value="'+ oObj.aData['id'] +'"/>';
            }
            },
            { "mData": "title" },
            { "mData": "category" }
        ]
    });
}

function selectRelativeNews()
{
    listId = '';
    $('input[name="news_id[]"]:checked').each(function(){
        if(listId == "")
            listId = $(this).val();
        else
            listId += "," + $(this).val();
        $(this).parent().parent().addClass("selected");
    });
    $.ajax({
        url: baseUrl + '/NewsCP/NewsRelativeCP/selectNews',
        dataType: "json",
        type: "POST",
        data: {listId: listId},
        success: function(res)
        {
            showNotification('Thông báo', res.msg);
            $("#tableRelativeNews").dataTable()._fnAjaxUpdate();
        }
    });
}

function moveDownRNews(obj)
{
    $.ajax({
        url: baseUrl + '/NewsCP/NewsRelativeCP/moveDown',
        dataType: "json",
        type: "POST",
        data: { id: $(obj).attr("accessKey")},
        success: function(res)
        {
            if(res.success)
                $("#tableRelativeNews").dataTable()._fnAjaxUpdate();
            else
                alert(res.msg);
        }
    });
}

function moveUpRNews(obj)
{
    $.ajax({
        url: baseUrl + '/NewsCP/NewsRelativeCP/moveUp',
        dataType: "json",
        type: "POST",
        data: { id: $(obj).attr("accessKey")},
        success: function(res)
        {
            if(res.success)
                $("#tableRelativeNews").dataTable()._fnAjaxUpdate();
            else
                alert(res.msg);
        }
    });
}
