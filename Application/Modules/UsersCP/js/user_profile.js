$("#frmChangepass").submit(function(){
    showLoadingPage();
    $.ajax({
        url: '/UsersCP/Users/changePass',
        dataType: 'json',
        type: "post",
        data: {x_password: $("#x_password").val(),x_password1: $("#x_password1").val(),x_password2: $("#x_password2").val()},
        success: function(res)
        {
            hideLoadingPage();
            if(res.success)
            {
                showNotification('Thông báo hệ thống', res.msg);
                document.getElementById("frmChangepass").reset();
            }
            else
            {
                showNotification('Thông báo hệ thống', res.msg);
            }
        }
    });
    return false;
});
