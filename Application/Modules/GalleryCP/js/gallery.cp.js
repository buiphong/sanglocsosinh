$(document).ready(function(){
    $('#album_id').change(function(){
        $("#album_id").closest("form").submit();
    });
	if($(".colorbox-image").length > 0){
		$(".colorbox-image").colorbox({
			maxWidth: "90%",
			maxHeight: "90%",
			rel: $(this).attr("rel")
		});
	}
	
	$(".jhtmlarea").htmlarea();
	tinymce.init({
	    selector: "textarea#artical_content",
	    theme: "modern",
	    width: 800,
	    height: 500,
	    plugins: [
	         "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
	         "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
	         "save table contextmenu directionality emoticons template paste textcolor"
	   ],
	   content_css: "css/content.css",
	   toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons", 
	   style_formats: [
	        {title: 'Bold text', inline: 'b'},
	        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
	        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
	        {title: 'Example 1', inline: 'span', classes: 'example1'},
	        {title: 'Example 2', inline: 'span', classes: 'example2'},
	        {title: 'Table styles'},
	        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
	    ],
	    
	    file_browser_callback: function(field_name, url, type, win) { 
	    	fileBrowserURL = baseUrl + "/Packages/pdw_file_browser/index.php?editor=tinymce&filter=" + type;
	        tinymce.activeEditor.windowManager.open({
	            title: "Quản lý file",
	            url: fileBrowserURL,
	            width: 800,
	            height: 600
	        }, {
	            oninsert: function(url) {
	                win.document.getElementById(field_name).value = url; 
	            }
	        });
	    }
	 }); 
});

//Add mới ảnh
$(document).ready(function(){
	$(".gallery li").live({
		mouseenter: function(){
			$(this).find(".extras").show();
		},
		mouseleave: function(){
			$(this).find(".extras").hide();
		}
	});
    $('#uploadImageForm').validate({
        errorElement: 'span',
        submitHandler: function()
        {
            $.ajax({
                url: $('#uploadImageForm').attr('action'),
                dataType: "json",
                type: "POST",
                data: $('#uploadImageForm').serialize(),
                success: function(res)
                {
                    if(res.success)
                    {
                        showNotification(res.msg);
                        $('#uploadImageForm')[0].reset();
                        $("#uploadImageForm .close").click();
                        //Add mới ảnh vào list
                        $("ul.gallery").prepend(res.html);
                        $(".colorbox-image").colorbox({
                            maxWidth: "90%",
                            maxHeight: "90%",
                            rel: $(this).attr("rel")
                        });
                    }
                    else
                    {
                        showNotification('Thông báo hệ thống', res.msg);
                    }
                }
            });
            return false;
        }
    });
})

var liParent;
//Edit ảnh
$(".edit-gallery-pic").live('click', function(){
	liParent = $(this).parent().parent().parent();
	$.ajax({
		url: $(this).attr('rel'),
		dataType: "json",
		type: "POST",
		success: function(res)
		{
			if(res.success)
			{
				showDialog(res.html, 'Edit Picture', 700);
			}
			else
			{
				showNotification(res.msg);
			}
		}
	});
});

//delete image
$(".del-gallery-pic").live('click', function(){
	if(confirm('Bạn thực sự muốn xóa ảnh này khỏi thư viện ảnh?'))
	{
		liParent = $(this).parent().parent().parent();
		$.ajax({
			url: $(this).attr('rel'),
			dataType: "json",
			type: "POST",
			success: function(res)
			{
				if(res.success)
				{
					showNotification('Thông báo hệ thống', 'Xóa ảnh thành công');
					liParent.remove();
				}
				else
				{
					showNotification('Thông báo hệ thống', res.msg);
				}
			}
		});
	}
});

/*$("#btnSubmitEditForm").live('click', function(){
    $.ajax({
        url: $('#editImageForm').attr('action'),
        dataType: "json",
        type: "POST",
        data: $('#editImageForm').serialize(),
        success: function(res)
        {
            if(res.success)
            {
                showNotification('Thông báo hệ thống', 'Cập nhật thông tin thành công!');
                $(".close").click();
                //Update lại ảnh trong list
                liParent.replaceWith(res.html);
                $(".colorbox-image").colorbox({
                    maxWidth: "90%",
                    maxHeight: "90%",
                    rel: $(this).attr("rel")
                });
            }
            else
            {
                showNotification(res.msg);
            }
        }
    });
    return false;
});*/

function openFileBrowser(id)
{
	fileBrowserlink = baseUrl + "/Packages/pdw_file_browser/index.php?editor=standalone&returnID=" + id;
	window.open(fileBrowserlink,'pdwfilebrowser', 'width=900,height=650,scrollbars=no,toolbar=no,location=no');
}