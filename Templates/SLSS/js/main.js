jQuery.extend(jQuery.validator.messages, {
    required: "Giá trị này không được bỏ trống.",
    email: "Vui lòng nhập địa chỉ email chính xác."
});
$(document).ready(function(){
    if($("#slide-home").length > 0)
    {
        $("#slide-home").cycle({
            next: '#slider-next',
            prev: '#slider-prev'
        });
    }

    var $visible = 6, $menuItem = 5;
    var $width = jQuery(window).width();

    if ($width <= 380){
        $menuItem = 2;
        $visible = 2;
    } else if ($width <= 480){
        $visible = 2;
        $menuItem = 3;
    } else if ($width <= 640){
        $visible = 3;
        $menuItem = 4;
    } else if ($width <= 768){
        $visible = 4;
    }

    if ($width < 980) {
        var mySwiper = new Swiper ('.swiper-container', {
            loop: true,
            slidesPerView: $menuItem,
            spaceBetween: 10,

            // Navigation arrows
            //nextButton: '.swiper-button-next',
            //prevButton: '.swiper-button-prev',
        });
    }

    $("#gallery-home-list-partner").carouFredSel({
        items: {visible: $visible},
        next: ".next-btn",
        prev: ".back-btn",
        //auto: true,
        responsive: 'true',
        speed: 1600
    });

    $(".faq_question").on('click',function(){
        $(".faq_answer_" + $(this).attr("accessKey")).toggleClass('dpn');
        //Update hits faq
        if($(this).attr('data-hit') == 0)
            updateHitFaq($(this).attr("accessKey"));
    });

    url = window.location.href;//.substring(7).split('/')[1];
    $("#main_menu li a").each(function(){
        if($(this).attr('href') == url){
            $("#main_menu ul li").removeClass('active');
            $(this).parent().addClass('active');
        }
        else
        {
            //Check if href is a part of url
            href = $(this).attr("href");
            href = href.substr(0, href.length - 5);
            if(url.indexOf(href) >= 0)
            {
                $("#main_menu li").removeClass('active');
                $(this).parent().addClass('active');
            }
        }
    });

    if($(".news_list_sidebar").length > 0)
    {
        showNews($(".news_list_sidebar").eq(0).attr("accessKey"));
        $(".news_list_sidebar").removeClass('active');
        $(".news_list_sidebar").eq(0).addClass('active');
    }

    $(".news_list_sidebar").on('click', function(){
        showNews($(this).attr('accessKey'));
    });

    if($("#frmRegister").length > 0)
    {
        $("#frmRegister").validate({
            errorElement: "span",
            submitHandler: function(){
                $("#bt_register").hide();
                $(".loading").show();
                if(!$("#agree_condition").is(":checked"))
                {
                    alert("Để có thể trở thành thành viên, bạn cần đồng ý với các điều khoản của thành viên.");
                    return false;
                }
                $.ajax({
                    url: $("#frmRegister").attr("action"),
                    type: "POST",
                    dataType: "JSON",
                    data: $("#frmRegister").serialize(),
                    success: function(res){
                        if(res.success)
                        {
                            $("#frmRegister")[0].reset();
                            $(".ft_notify").html(res.msg).css("color", "#0000ff");
                            $("#bt_register").show();
                            $(".loading").hide();
                        }
                        else
                        {
                            reloadCaptcha('captcha_img');
                            $("input[name=captcha_code]").val('');
                            $(".ft_notify").html(res.msg).css("color", "#ff0000");
                            $("#bt_register").show();
                            $(".loading").hide();
                        }
                    }
                })
                return false;
            }
        });

        $("#frmRegister input[type='text']").on("focus", function(){
            $(".ft_notify").html("");
        })
    }

    if($("#frmLogin").length > 0)
    {
        $("#frmLogin").validate({
            errorElement: "span",
            submitHandler: function(){
                $.ajax({
                    url: $("#frmLogin").attr("action"),
                    type: "POST",
                    dataType: "json",
                    data: $("#frmLogin").serialize(),
                    success: function(res){
                        if(res.success)
                        {
                            $('#login').css("display", "none");
                            if(res.html)
                                $("#box_member_header").replaceWith(res.html);
                        }
                        else
                            alert(res.msg);
                    }
                })
            }
        })
    }

    if($("#frmContacts").length > 0)
    {
        $("#frmContacts").validate({
            errorElement: "span",
            submitHandler: function(){
                $("#bt_SendContact").hide();
                $(".loading").show();
                $.ajax({
                    url: $(this).attr('action'),
                    type: "POST",
                    dataType: "json",
                    data: $("#frmContacts").serialize(),
                    success: function(res){
                        if(res.success)
                        {
                            $("div.ft_notify").html(res.msg).css("color", "#0000ff");
                            $("#frmContacts")[0].reset();
                            $("#bt_SendContact").show();
                            $(".loading").hide()
                        }
                        else
                        {
                            $("div.ft_notify").html(res.msg).css("color", "#ff0000");
                            $("#bt_SendContact").show();
                            $(".loading").hide()
                        }
                    }
                })
            }
        });

        $("#frmContacts input, #frmContacts textarea").on("focus", function(){
            $("div.ft_notify").html("");
        })
    }

    if(jQuery().datepicker)
    {
        $('.datepicker').datepicker({format: "dd/mm/yyyy"});
        $('.datepicker-news').datepicker({format: "dd/mm/yyyy"}).on('changeDate', function(e){
            location.href = $(this).attr('data-href') + '/' + e.date.getDate() + '-' + (e.date.getMonth() + 1) +'-'+ e.date.getFullYear() + '.html';
        });
    }

    if($('.tab-title').length > 0)
    {
        $('.tab-title').click(function(){
            $(".tab-content").hide();
            $("#" + $(this).attr('accessKey')).show();
        });
    }
    $(".news_list_sidebar").click(function(){
        $(".news_list_sidebar").removeClass('active');
        $(this).addClass('active');
    })

    $.ajax({
        url: "/Counter/Counter/Online",
        dataType: "json",
        type: "POST",
        data: { sessionId: sessionId, ipAddress: ipAddress},
        success: function(res)
        {
            $("#online-total").html(res.online.total);
        }
    });

    if($(".ajax-snews").length > 0){
        $('body').on('click', '.ajax-snews', function(){
            var obj = $(this);
            var parent = $(this).parent();
            $.ajax({
                url: parent.children('input[name=ajaxUrl]').val(),
                dataType: "json",
                type: "POST",
                data: {data: parent.children('input[name=ajaxParams]').val(), page: obj.attr('accessKey')},
                success: function(res)
                {
                    if(res.success){
                        obj.closest('.ajax-border').replaceWith(res.html);
                    }
                    $('html,body').scrollTop(0);
                }
            });
        });
    }

    // Handle toggle menu
    $('.toggle-menu').on('click touchstart', function(){
        $("#main_menu ul").toggle();
        return false;
    });

    handleScrollRightSidebar();
});

$('body').on('submit', '.ajax-form', function(){
    obj = $(this);
    var formData = new FormData(obj.get(0));
    if(obj.valid())
    {
        $(".loading").show();
        obj.find('input[type=submit]').hide();
        $.ajax({
            url: $(this).attr('action'),
            dataType: "json",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res)
            {
                $(".loading").hide();
                obj.find('input[type=submit]').show();
                if(res.success)
                    alert(res.msg);
                else
                    alert(res.msg);
            }
        });
    }
    return false;
});

function showNews(id)
{
    $.ajax({
        url: '/News/NewsComponent/detailNews',
        dataType: "json",
        type: "get",
        data: {id: id},
        success: function(res)
        {
            if(res.success)
            {
                $("#detail_content_border").html(res.html);
                $('html,body').scrollTop(0);
                FB.XFBML.parse();
            }
        }
    });
}

function openBoxLogin(obj)
{
    var sObj = document.getElementById(obj);
    sObj.style.display = (sObj.style.display != "block") ? "block" : "none";
}

function updateHitFaq(id)
{
    $.ajax({
        url: '/Faq/FaqComponent/updateHit',
        dataType: "json",
        type: "post",
        data: {id: id},
        success: function(res)
        {

        }
    });
}

function reloadCaptcha(elementId)
{
    document.getElementById(elementId).src = '/Packages/securimage/securimage_show.php?' + Math.random();
}

function showFaqForm()
{
    $("#frmSendFaq").toggleClass('dpn');
}

function handleScrollRightSidebar()
{
    var windowWidth = jQuery(window).width();
    // Scroll right nav for mobile
    if (windowWidth <= 960 && windowWidth > 640) {
        var container, cssMarginFix = 0, offsetTop,
            elm = $('.fr.wp251'),
            elmOffsetTop,
            elmHeight,
            windowHeight = screen.height;

        var boxTop = $("#slide-menu").innerHeight();

        container = elm.parent().find('.wp764');
        if (container.length <= 0) {
            container = elm.parent().find('.wp768');
        }
        elmOffsetTop = parseInt(elm.offset().top);
        elmHeight = parseInt(elm.innerHeight());
        console.log('elmH: ' + elmHeight);

        var containerHeight = container.innerHeight();
        var lastScrollTop = 0;
        var maxMargin = container.innerHeight() - elmHeight - boxTop;

        $(window).scroll(function () {
            var scrollTop = $(this).scrollTop();
            var elmMargin = parseInt(elm.css('margin-top'));

            if (scrollTop + windowHeight < (elmOffsetTop + elmHeight) && parseInt(elm.css('margin-top')) === 0) {
                elm.css('margin-top', 0);
            } else {
                if ((elmOffsetTop + elmHeight + elmMargin) < (container.offset().top + containerHeight) && (elmOffsetTop + parseInt(elm.css('margin-top'))) < scrollTop) {
                    offsetTop = scrollTop - elmOffsetTop;
                } else {
                    if ((elmOffsetTop + parseInt(elm.css('margin-top'))) > scrollTop && parseInt(elm.css('margin-top')) > 0) {
                        offsetTop = scrollTop - container.offset().top;
                    } else if (parseInt(elm.css('margin-top')) != 0) {
                        offsetTop = containerHeight - boxTop - elmHeight;
                    }
                }
                if (offsetTop < 0) {
                    offsetTop = 0;
                }

                if (offsetTop > maxMargin) {
                    offsetTop = maxMargin;
                }

                elm.css("margin-top", offsetTop + 'px');
            }

            lastScrollTop = scrollTop;
        });
    }
}