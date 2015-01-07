$(document).ready(function(){
   	//defaults
	$.fn.editable.defaults.url = '/post'; 
    //editables 
	$('.editable').editable({
		type: 'text',
	    pk: $("#pKey").val(),
	    url: '/ConfigCP/ConfigCP/emailConfig',
	    send: 'always'
	});
});

function sendMailTest()
{
    showLoadingPage();
    $.ajax({
        url: '/ConfigCP/ConfigCP/testSendMail',
        dataType: 'json',
        type: 'POST',
        data: {email: $("#email_test").val()},
        success: function(res)
        {
            hideLoadingPage();
            if(res.success)
                showNotification('Test gửi mail thành công!', 'Vui lòng kiểm tra lại hòm mail để xác nhận kết quả.');
            else
                showNotification('Gửi mail không thành công..', 'Đã có lỗi xảy ra khi thực hiện gửi mail');
        }
    });
}