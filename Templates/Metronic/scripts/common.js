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
var modalData =[];

$('body').on('hidden', '.modal', function(e){
    if ($('.modal').size() === 0) {
        $('body').removeClass('modal-open');
    }
});

//ajax loading
jQuery(document).bind("ajaxSend", function(){
    jQuery("#mainPageLoading").show();
}).bind("ajaxComplete", function(){
        jQuery("#mainPageLoading").hide();
    });

//Thiết lập chiều cao dialog
function getDHeight()
{
	bHeight = window.innerHeight;
	DialogHeight = bHeight - 150;
}

//Show boxy dialog
function showDialog(content,title, width)
{
    showModal(title,content,width);
}

function showModal(title,content, width, hideFunction)
{
    $('body').modalmanager('loading');
    modal = jQuery('.custom-modal');
    modal.modal('hide');
    //last modal
    total = jQuery(".custom-modal").length;
    if(total > 0)
        modalData[total] = jQuery("#Modal-0" + total).clone();
    total = total+1;
    if(!width)
        width = '';
    id = 'Modal-0' + total;
    modalHtml = '<div id="'+id+'" class="custom-modal modal '+width+'"><div class="modal-header">'+
        '<button data-dismiss="modal" class="close" type="button"></button>'+
        '<h3>'+title+'</h3></div><div class="modal-body">'+
        '<p>'+content+'</p></div></div>';
    jQuery("body").append(modalHtml);
    jQuery("#" + id).modal();
/*    jQuery("#" + id).draggable({
        handle: ".modal-header"
    });*/

    jQuery("#" + id).on('hide', function(){
        jQuery(this).empty();
    });
}

//Show notifications
function showNotification(title,message,time,sticky,overlay)
{
    if(message)
    {
        jQuery.gritter.add({
            title: 	(typeof title !== 'undefined') ? title : 'System message',
            text: 	(typeof message !== 'undefined') ? message : '',
            image: 	(typeof image !== 'undefined') ? image : null,
            sticky: (typeof sticky !== 'undefined') ? sticky : false,
            time: 	(typeof time !== 'undefined') ? time : 3000
        });
    }
}

function CPConfirm(content, okFunction, param)
{	
	if(content !='')
	{
		jQuery('#modal-confirm .modal-body p').html(content);
	}
	jQuery("#modal-confirm").modal();
    jQuery("#modal-confirm #ok").click(function(){
    	if(param)
            okFunction(param);
        else
            okFunction();
    });
}


//show loading ajax
function showLoadingPage()
{
	jQuery("#mainPageLoading").show();
}

function hideLoadingPage()
{
	jQuery("#mainPageLoading").hide();
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
    str= str.replace(/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'| |\"|\&|\#|\[|\]|~|$|_/g,"-");
    /* tìm và thay thế các kí tự đặc biệt trong chuỗi sang kí tự - */
    str= str.replace(/-+-/g,"-"); //thay thế 2- thành 1-
    str= str.replace(/^\-+|\-+$/g,"");
    //cắt bỏ ký tự - ở đầu và cuối chuỗi
    return str;
}

function updateSeoUrl(a, b)
{
	jQuery(b).val(seoTitleGenerate(jQuery(a).val()));
}