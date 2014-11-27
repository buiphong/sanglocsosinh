$(document).ready(function() {
    $(".tabContents").hide();
    $(".tabContents:first").show();
    $(".tabContainer ul li a").click(function() {
        var activeTab=$(this).attr("href");
        $(".tabContainer ul li a").removeClass("active");
        $(this).addClass("active");
        $(".tabContents").hide();
        $(activeTab).fadeIn();
    });
    if($("#browser").length > 0)
    {
        $("#browser").click(function(){
            $(this).next().click();
        });
    }
});
var editData= 0;
function syncPictureGallery(){
	$(".picture_list tr").empty();
	strHtml= "";
	$("#gallery_picture_post li").each(function(index){
		img= $(this).find("img:first");
		arrImg= img.attr("src").split("small/");
		checked= '';
		tdClass= '';
		if((arrImg[1] == "") || ("" == "" && index == 0 && editData == 0)){
			checked= ' checked="checked"';
			tdClass= ' class="current"';
		}
		strHtml+= '<td' + tdClass + '><img title="Click để chọn ảnh minh họa" src="' + img.attr("src") + '" onClick="$(\'.picture_list td\').removeClass(\'current\'); $(this).parent().addClass(\'current\').find(\'input\').click()" />' + '<br /><input type="radio" name="data[AdPost][images]" value="' + arrImg[1] + '"' + checked + ' onClick="$(\'.picture_list td\').removeClass(\'current\'); $(this).parent().addClass(\'current\')" /></td>';
	});
	$(".picture_list tr").html(strHtml);
}
function deleteGallery(ob){
	if(typeof(ob.attr("iData")) != "undefined" && ob.attr("iData") > 0) 
		ajaxFile = "/candidates/Attach/deletefile/";
	else{
		arrTemp= $(ob).find("input").attr('value');
		ajaxFile = "/candidates/Attach/deletefile?file=" + arrTemp;
	}
	if(ob.parent().attr("id") == "gallery_picture"){
		if(confirm("Bạn có muốn xóa ảnh này không?")){
			$.get(ajaxFile, function(data){
			//if(checkAjaxResponse(data) == 0) return;
			ob.fadeOut(400, function(){
			$(this).remove();
			});
			});
		}
	}
	else{
		ob.hide().appendTo("#gallery_picture").fadeIn();
		$("#tooltip").hide();
		syncPictureGallery();
	}
}

function postGalleryTemp(form_id){
	$("#" + form_id).submit();
	$("#" + form_id + " input[type='file']").remove();
	inputHtml= '<input type="file" class="form_control" name="Filedata" size="20" style="width:220px" onchange="postGalleryTemp(\'' + form_id + '\')" />';
	if(form_id == "uploadGalleryTempOnPost") 
		inputHtml= '<input type="file" class="form_control" name="Filedata" size="50" style="width:400px" onchange="postGalleryTemp(\'' + form_id + '\')" />';
	$("#" + form_id).prepend(inputHtml);
}

function generateImageInput(){
	control= '';
	$("#gallery_picture_post li").each(function(index){
		commentTemp= (typeof($(this).attr("nData")) != "undefined" ? $(this).attr("nData") : "");
		arrTemp= $(this).find("img:first").attr("src").split("small");
		pictureTemp= (typeof($(this).attr("iData")) != "undefined" ? $(this).attr("iData") : arrTemp[1]);
		control+= '<input type="hidden" name="gallery_comment[]" value="' + commentTemp + '" />';
		control+= '<input type="hidden" name="gallery_picture[]" value="' + pictureTemp + '" />';
	});
	$("form[name='post_final']").append(control);
}

function uploadPost(upload_field, actionimg, actionform){
	var re_text = /\.html|\.HTML|\.htm|\.HTM|\.phtml|\.PHTML/i;
	var filename = upload_field.value;
	if (filename.search(re_text) == -1) {
		alert("Định dạng file không đúng!");
		return false;
	}
	upload_field.form.action = actionimg;
	upload_field.form.target = 'upload_iframe';
	upload_field.form.submit();
	upload_field.form.action = actionform;
	upload_field.form.target = '';
	return true;
}