$(document).ready(function(){
    //$('.tree').tree();
});
$('body').on('submit', '.ajax-form', function(){
    obj = $(this);
    var formData = new FormData(obj.get(0));
    if(obj.valid())
    {
        $.ajax({
            url: $(this).attr('action'),
            dataType: "json",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res)
            {
                if(res.success)
                {
                    showNotification('Thông báo hệ thống', res.msg);
                    if(res.type == 'create')
                    {
                        if(res.table && res.row)
                            $("#"+res.table).append(res.row);
                        //reload for nestable
                        if(res.nestableReload && res.html)
                        {
                            $('.nestable').replaceWith(res.html);
                        }
                        //reset form
                        if(res.continue)
                        {
                            obj.get(0).reset();
                            return true;
                        }
                    }
                    if(res.type == 'edit')
                    {
                        if(res.table && res.row)
                            $("#"+res.table+" tr[active='1']").replaceWith(res.row);
                    }
                    //reload item for nestable list
                    if(res.nestable)
                    {
                        $(".dd-item[data-id="+res.id+"]").children(".dd3-content").eq(0).children('.dd-content-title').eq(0).html(res.str);
                    }
                    if(res.dataTable && $("#"+res.dataTable).length > 0)
                        $("#"+res.dataTable).dataTable()._fnAjaxUpdate();//reload dataTable

                    //Call javascript function
                    if(res.scriptFunc)
                        window[res.scriptFunc]();

                    modal = obj.closest('.custom-modal');
                    modal.modal('hide');
                }
                else
                {
                    showNotification('Thông báo hệ thống', res.msg);
                }
            }
        });
    }
    return false;
});
//Đóng modal
$('body').on('click', '.close-modal',function(){
    modal = $(this).closest('.custom-modal');
    modal.modal('hide');
});
//xử lý thêm, sửa bằng ajax hiển thị modal
$('.btn_ajax').live('click',function(){
    $('table tr').removeAttr('active');
    title = $(this).attr('title');
    $(this).parent().parent().attr('active',1);
    $.ajax({
        url: $(this).attr('rel'),
        type: "GET",
        async: false,
        success: function(res)
        {
            showModal(title,res,'large')
        }
    });
});

$("body").on('click', '.frm-detail-btn', function(){
    obj = $(this);
    if(!obj.attr('disabled'))
    {
        obj.attr('disabled', true);
        $.get(obj.attr('data-detailUrl'),function(res){
            obj.attr('disabled', false);
            showModal('Xem chi tiết', res, 'large');
        });
    }
});
$("body").on('click', '.frm-delete-btn', function(){
    linkDel = $(this).attr('data-delUrl');
    if(!linkDel)
        linkDel = $(this).attr('href');
    id = $(this).attr('data-id');
    if(id != '')
        deleteAct(linkDel,id);
    return false;
});

function DeleteAll(formId, delLink)
{
    var listId = "";
    $('#'+formId+' input[name="key[]"]:checked').each(function(){
        if(listId == "")
            listId = $(this).val();
        else
            listId += "," + $(this).val();
        count =+ 1;
    });
    if (listId != '')
        deleteAct(delLink,listId);
}

function deleteAction(linkDel)
{
    var listId = "";
    $('input[name="key[]"]:checked').each(function(){
        if(listId == "")
            listId = $(this).val();
        else
            listId += "," + $(this).val();
        count =+ 1;
    });
    if (listId != '')
        deleteAct(linkDel,listId);
}
function deleteAct(linkDel,listId)
{
    if(confirm('Bạn có chắc muốn xóa không?'))
    {
        $.ajax({
            url: linkDel,
            dataType: "json",
            type: "POST",
            data:{id: listId},
            success: function(res)
            {
                if(res.success)
                {
                    if(res.dataTable)
                        $("#"+res.dataTable).dataTable()._fnAjaxUpdate();//reload dataTable*/
                    //nestable
                    if(res.nestable)
                        $(".dd-item[data-id="+res.id+"]").remove();
                    //Call javascript function
                    if(res.scriptFunc)
                        window[res.scriptFunc]();
                    if(!res.msg)
                        res.msg = 'Đã xóa thành công!';
                    showNotification('',res.msg);
                }
                else
                    showNotification('Thông báo hệ thống', res.msg);
            }
        });
    }
}
$("body").on('click', '.frm-edit-btn-ajax', function(){
    title = $(this).attr('title');
    if(!title)
        title = 'Sửa';
    url = $(this).attr('data-editUrl');
    if(!url)
        url = $(this).attr('href');
    $.ajax({
        url: url,
        type: "GET",
        async: false,
        success: function(res)
        {
            showModal(title,res,'large')
        }
    });
    return false;
});
//Xóa
function deleteAction(delLink)
{
    var listId = "";
    $('input[name="key[]"]:checked').each(function(){
        if(listId == "")
            listId = $(this).val();
        else
            listId += "," + $(this).val();
    });
    if (listId != '')
    {
        $.ajax({
            url: delLink,
            type: "POST",
            dataType: "json",
            data: {listid: listId},
            success: function(res){
                if(res.success)
                {
                    $('input[name="key[]"]:checked').each(function(){
                        $(this).parent().parent().remove();
                    });
                }
                else
                    alert(res.msg);
            }
        });
    }
}