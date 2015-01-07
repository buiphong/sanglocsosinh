$(document).ready(function(){
	$('#frmedit').validate();
    $("#file_data").on('click change', function(){
        getSizeBanner($(this).val());
    });
});


function openFileBrowser(id)
{
	fileBrowserlink = baseUrl + "/Packages/pdw_file_browser/index.php?editor=standalone&returnID=" + id;
	window.open(fileBrowserlink,'pdwfilebrowser', 'width=900,height=650,scrollbars=no,toolbar=no,location=no');
}

function reloadSizeBanner()
{
    $("#banner-width").val($("#banner_real_w").html());
    $("#banner-height").val($("#banner_real_h").html());
}

function getSizeBanner(banner)
{
    $.ajax({
        url: 'BannerCP/BannerCP/getSizeBanner',
        type: "get",
        dataType: "json",
        data: {file: banner},
        success: function(res)
        {
            if(res.success)
            {
                $("#banner-width").val(res.width);
                $("#banner-height").val(res.height);
                $("#banner_real_w").html(res.width);
                $("#banner_real_h").html(res.height);
            }
        }
    });
}