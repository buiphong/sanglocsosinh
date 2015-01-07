window.___gcfg = {lang: 'vi'};
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
jQuery.extend(jQuery.validator.messages, {
	 required: "Không được bỏ trống !",
	 email: "Email sai định dạng !"
	});
$("#frmContact").validate({
	errorElement: "p",
	submitHandler: function(form) {
		$('.btnSend').hide();
		$('.btnLoad').show();
		$.ajax({
			url: baseUrl + '/ContactsCP/ContactComponent/contactPage',
			dataType: "json",
			type: "post",
			data: $('#frmContact').serialize(),
            contentType:attr("enctype","multipart/form-data"),
			success: function(res)
			{
				if(res.success)
				{
					$('#frmContact')[0].reset();
					$('.btnLoad').hide();
					$('.btnSend').show();
					//showNotification('Thông báo từ HRC','Phản hồi đã được gửi đi. Chúng tôi sẽ hồi đáp trong thời gian ngắn nhất!');
                    //alert ('Phản hồi đã được gửi đi. Chúng tôi sẽ hồi đáp trong thời gian ngắn nhất!');
                    //var html = 'Phản hồi đã được gửi đi. Chúng tôi sẽ hồi đáp trong thời gian ngắn nhất!';
                    //$("p#pThem").text(html);
                    alert(res.msg);
				}
				else
				{
					//showNotification('Lỗi đăng nhập!', res.msg);
                    //var html = "Không thể gửi phản hồi!";
                    //$("p#pThem").text(html);
                    alert (res.msg);
				}
			}
		});
		return false;
	}
});
/*function openFileBrowser(id)
{
    //fileBrowserlink = baseUrl + "/packages/pdw_file_browser/index.php?editor=standalone&returnID=" + id;
    fileBrowserlink = "D:\Projects\images";
    window.open(fileBrowserlink,'pdwfilebrowser', 'width=900,height=650,scrollbars=no,toolbar=no,location=no');
}*/