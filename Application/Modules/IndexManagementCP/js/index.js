$(document).ready(function(){
    var oTable = $("#tableIndexCP").DataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "sPaginationType": "bootstrap",
        "sAjaxSource": "/IndexManagementCP/IndexCP/dataTable",
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('id', aData['id']);
        },
        "fnServerParams": function ( aoData ) {
            aoData.push();
        },
        "aoColumns": [
            { "bSortable": false, "sDefaultContent": "", "fnRender": function (oObj) {
                return  '<input type="checkbox" name="key[]" value="'+ oObj.aData['id'] +'"/>';
                }
            },
            { "bSortable": false, "mData": "title" },
            { "bSortable": false, "mData": "description" },
            { "bSortable": false, "mData": "status" },
            { "bSortable": false, "mData": "modify" },
            { "bSortable": false, "mData": "reset" }
        ]
    });
	
    $("#tableIndexCP .createIndex").live('click',function(){
        var url = $(this).attr("data-url");
        var id = $(this).attr("data-id");
        var rs = $(this).attr("data-reset");
		while(response != 0){
			var reset = "false";
			if(response == 1 && rs)
				reset = "true";
			indexData(url, response, id, reset);
		}
		var d = new Date();
		response = 1;
		var obj = $(this);
		if(rs){
			obj = $(this).parent().prev().prev().find("div");
		}
		obj.removeClass("createIndex");
		obj.css("cursor", "default");
		obj.html("Đã tạo index");
		obj.css("background","#393");
		var month = d.getMonth() + 1;
		if(month < 10)
			obj.parent().next().html(d.getDate() + "-0" + month + "-" + d.getFullYear());
		else
			obj.parent().next().html(d.getDate() + "-" + month + "-" + d.getFullYear());
    });
});
var response = 1;
function indexData(url, page, id, reset){
	$.ajax({
		url: url + "?page=" + page + "&id=" + id + "&reset=" + reset,   
		dataType: "json",
		type: "POST",
		async: false,
		success : function(res){
			if(!res.success){
				alert("Tạo index thành công");
			}
			response = res.page_next;
		}
	});
	return response;
}
