jQuery.extend(jQuery.validator.messages, {
    required: "Giá trị không được để trống.",
    remote: "Please fix this field.",
    email: "Địa chỉ email chưa đúng.",
    url: "Địa chỉ url chưa đúng.",
    date: "Định dạng ngày tháng chưa đúng.",
    dateISO: "Please enter a valid date (ISO).",
    number: "Giá trị phải là kiểu số.",
    digits: "Please enter only digits.",
    creditcard: "Please enter a valid credit card number.",
    equalTo: "Giá trị chưa khớp.",
    accept: "Please enter a value with a valid extension.",
    maxlength: jQuery.validator.format("Độ dài không vượt quá {0} ký tự."),
    minlength: jQuery.validator.format("Độ dài ít nhất là {0} ký tự."),
    rangelength: jQuery.validator.format("Độ dài phải từ {0} đến {1} ký tự."),
    range: jQuery.validator.format("Vui lòng chỉ nhập giá tri trong khoảng {0} - {1}."),
    max: jQuery.validator.format("Giá trị không được vượt quá {0}."),
    min: jQuery.validator.format("Giá trị tối thiểu phải là {0}.")
});

var DialogHeight;
var dialogNum = 1;

//Thiết lập chiều cao dialog
function getDHeight()
{
	bHeight = window.innerHeight;
	DialogHeight = bHeight - 150;
}

//Show boxy dialog
function showDialog(content,title, width)
{
	if(!width)
		width = 650;
	getDHeight();
	if($(".boxy-modal-blackout").length > 0)
		modalVal = false;
	else
		modalVal = true;
	imageClose = '<img src="../../Templates/flatadmin/images/popup-close-button.png" height="16" title="Đóng"/>';
	boxyContent = '<div id="boxy-content'+dialogNum+'" style=" width:'+width+'px; max-height: '+DialogHeight+'px; overflow-y: scroll;"><div style="height: '+DialogHeight+'px;">wait</div></div>';
	var dialog = new Boxy(boxyContent, {title: title, modal: modalVal, unloadOnHide: true, closeText: imageClose});
	$("#boxy-content" + dialogNum).html(content);
	dialogNum += 1;
}

//Show notifications
function showNotification(title,message,time,sticky,overlay)
{
	$.gritter.add({
		title: 	(typeof title !== 'undefined') ? title : 'Message - Head',
		text: 	(typeof message !== 'undefined') ? message : 'Body',
		image: 	(typeof image !== 'undefined') ? image : null,
		sticky: (typeof sticky !== 'undefined') ? sticky : false,
		time: 	(typeof time !== 'undefined') ? time : 3000
	});
}

//show loading ajax
function showLoadingPage()
{
	$("#mainPageLoading").show();
}

function hideLoadingPage()
{
	$("#mainPageLoading").hide();
}

function seoTitleGenerate(str){
    str= str.toLowerCase();
    str= str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g,"a");
    str= str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g,"e");
    str= str.replace(/ì|í|ị|ỉ|ĩ/g,"i");
    str= str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g,"o");
    str= str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g,"u");
    str= str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g,"y");
    str= str.replace(/đ/g,"d");
    str= str.replace(/!|@|®|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'| |\"|\&|\#|\[|\]|~|$|_/g,"-");
    /* tìm và thay thế các kí tự đặc biệt trong chuỗi sang kí tự - */
    str= str.replace(/-+-/g,"-"); //thay thế 2- thành 1-
    str= str.replace(/^\-+|\-+$/g,"");
    //cắt bỏ ký tự - ở đầu và cuối chuỗi
    return str;
}