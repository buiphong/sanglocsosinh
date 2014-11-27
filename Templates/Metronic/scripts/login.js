$("#frmLogin").submit(function(){
    $(".alert-error").hide();
    $.ajax({
        url: '/ControlPanel/Index/login',
        dataType: "json",
        type: "post",
        data: $("#frmLogin").serialize(),
        success: function(res)
        {
            if(res.success)
                location.href=res.url;
            else
            {
                $("#error_msg").html(res.msg);
                $(".alert-error").show();
            }
        }
    });
    return false;
});