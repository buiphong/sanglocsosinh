/**
 * User: phongbui
 * Date: 10/3/13
 * Time: 10:10 AM
 */
//index news for search
function createIndexSearch()
{
    $("#index-search-load-icon").show();
    $.ajax({url: baseUrl + '/SearchCP/SearchCP/createIndex',dataType: 'json',success: function(r){
        if(r.success){
            $("#indexedNews").html(r.total);
            $("#index-search-load-icon").hide();
        }
    }});
}

//delete indexData
function deleteIndexSearch()
{
    $("#index-search-load-icon").show();
    $.ajax({url: baseUrl + '/SearchCP/SearchCP/emptyIndexData',dataType: 'json',success: function(r){
        if(r.success)  $("#indexedNews").html('0');
        $("#index-search-load-icon").hide();
    }});
}

//index product for search
function createIndexProduct()
{
    $("#index-product-load-icon").show();
    $.ajax({url: baseUrl + '/SearchCP/SearchCP/createIndex',dataType: 'json',success: function(r){
        if(r.success){
            $("#indexedProduct").html(r.total);
            $("#index-product-load-icon").hide();
        }
    }});
}

//delete indexData product
function deleteIndexProduct()
{
    $("#index-product-load-icon").show();
    $.ajax({url: baseUrl + '/SearchCP/SearchCP/emptyIndexData',dataType: 'json',success: function(r){
        if(r.success)  $("#indexedProduct").html('0');
        $("#index-product-load-icon").hide();
    }});
}

//delete db cache
function deleteDbCache()
{
    $("#db-cache-load-icon").show();
    $.ajax({url: baseUrl + '/ControlPanel/Index/deleteDbCache',dataType: 'json',success: function(r){
        if(r.success){
            showNotification('Xóa cache database thành công!', ' ');
            $("#dbCacheSize").html(r.newSize);
        }
        $("#db-cache-load-icon").hide();
    }});
}

//delete htmlCache
function deleteHtmlCache()
{
    $("#html-cache-load-icon").show();
    $.ajax({url: baseUrl + '/ControlPanel/Index/deleteHtmlCache',dataType: 'json',success: function(r){
        if(r.success){
            showNotification('Xóa cache html thành công!', ' ');
            $("#htmlCacheSize").html(r.newSize);
        }
        $("#html-cache-load-icon").hide();
    }});
}

//delete asset cache
function deleteAssetCache()
{
    $("#asset-cache-load-icon").show();
    $.ajax({url: baseUrl + '/ControlPanel/Index/deleteAssetCache',dataType: 'json',success: function(r){
        if(r.success){
            showNotification('Xóa cache js/css thành công!', ' ');
            $("#assetCacheSize").html(r.newSize);
        }
        $("#asset-cache-load-icon").hide();
    }});
}