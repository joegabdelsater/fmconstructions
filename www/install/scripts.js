
function changeForeignDiv(){

    var value = $(this).val();
    var fieldName = $(this).attr("name");

    if(value.indexOf("foreign") != -1){
        $(this).next(".foreignDiv").load('actions/showTables.php?fieldName='+fieldName);
    }else if(value.indexOf("photo_upload") != -1){
        $(this).next(".foreignDiv").load('actions/showPhotoUploadOptions.php?fieldName='+fieldName);
    }else if(value.indexOf("thumbnail") != -1){
        $(this).next(".foreignDiv").load('actions/showThumbnailOptions.php?fieldName='+fieldName);
    }else if(value.indexOf("limited_textarea") != -1){
        $(this).next(".foreignDiv").load('actions/showLimitedTextAreaOptions.php?fieldName='+fieldName);
    }else{
        $(this).next(".foreignDiv").html('');
    }

}

function changeForeignFieldDiv(){

    var table = $(this).val();
    var fieldName = $(this).attr("fieldName");

    if(table != ''){
        $(this).next(".foreignFieldDiv").load('actions/showFields.php?table='+table+'&fieldName='+fieldName);
    }else{
        $(this).next(".foreignFieldDiv").html('');
    }

}

$(document).on("change","#install select",changeForeignDiv);
$(document).on('change',"#install .foreignTable",changeForeignFieldDiv);
//$(document).ready(function() {
//   $('#install select').change(function() {
//        var value = $(this).val();
//        if(value == 'foreign'){
//            $(this).next(".foreignDiv").load('actions/showTables.php');
//        }
//   });
//});

$(function(){

    $('#coolMenu').find('> li').hover(function(){
        $(this).find('ul')
        .removeClass('noJS')
        .stop(true, true).slideToggle('fast');
    });

});

$(document).ready(function(){

    $(".openLink").click(function(){
        if($("#menu_container").width() == '200'){
            $("#menu_container").animate({width :0+"px"}, 500);
            $(".openLink").addClass("menuClosed").removeClass("menuOpen").html('&raquo;');
        }else{
            $("#menu_container").animate({width : 200+"px"}, 500);
            $(".openLink").removeClass("menuClosed").addClass("menuOpen").html('&laquo;');
        }

    });

    $(".showIdsBtn").click(function(e){
        if($(this).find(".sign").html() == '+'){
            $(this).find(".sign").html('-');
        }else{
            $(this).find(".sign").html('+');
        }
        e.preventDefault();

        $(".item_id").toggle();
    });

    $("input[name='tooltipCheckbox']").change(function(){

        $(this).closest(".entry").find(".tooltipEntry").toggle("fast");
    });

    // Advanced Search ;
    // Remove the required and the validator attributes
    $(".advanced-search input,.advanced-search select,.advanced-search textarea").each(function(){
        $(this).removeAttr("required");
        $(this).removeAttr("data-bvalidator");
        var fieldName = $(this).attr("name");
        $(this).attr("name",'data[Search]['+fieldName+']');
    });

    $(".bulkEditContainer input, .bulkEditContainer select,.bulkEditContainer textarea").each(function(){
        $(this).removeAttr("required");
        $(this).removeAttr("data-bvalidator");
    });
    $('.advancedSearchBtn').click( function (e){
        e.preventDefault();
        $('.advanced-search').toggle('slow');
    });

});




$(document).ready(function() {

    var docheight = $(document).height();
    $("#menu_container").height(docheight);

    $(window).resize(function() {
        var winheight = $(window).height();
        if(winheight > docheight){
            $("#menu_container").height(winheight);
        } else {
            $("#menu_container").height(docheight);
        }
    });

    $('#select-all').click(function(event) {
        if(this.checked) {
            // Iterate each checkbox
            $(':checkbox').each(function() {
                this.checked = true;
            });
        }else{
            $(':checkbox').each(function() {
                this.checked = false;
            });
        }
    });
    $('#inverse').click(function(event) {
        $(':checkbox').not('#select-all').each(function() {
            if(this.checked == false){
                this.checked=true;
            }else{
                this.checked=false;
            }
        });
    });


    $(".delete_action").click(function(event){

        var $form = $(this).closest("form");
        event.preventDefault();
        if(confirm("Are you sure you want to delete?")){



            $("#saveResult").fadeIn("slow");

            $("#saveResult").html("Deleting...");

            var datas = $form.serialize();

            var idsArray = []; //Array containing ids to be deleted
            //Insert the ids to be deleted into the array
            $("[name='ids[]']:checked").each(function() {
                idsArray.push( $(this).val() );
            });

            var  url = $form.attr( 'action' );

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'html',
                data: datas,
                success: function(response, textStatus, XMLHttpRequest) {
                    if (!response){

                        $("#saveResult").html("There was an error!").delay(2000).fadeOut("slow");
                        return false;
                    }
                    else {
                        $("#saveResult").html(response).delay(2000).fadeOut("slow");
                        if(response == 'Deleted!'){
                            $("input[type=checkbox]:checked").not("#select-all").each(function(){
                                $(this).closest('tr').fadeOut();
                                //Remove the deleted IDS from the bulk edit form
                                var i;
                                for (i = 0; i < idsArray.length; ++i) {
                                    $("#cmsgen_rowsToEdit option[value='"+idsArray[i]+"']").remove();
                                }
                            });
                        }
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $("#saveResult").html("There was an error!");
                    if (typeof console != 'undefined')
                        console.dir(XMLHttpRequest);
                    return false;
                }
            });


        }
    });


    //$("#generateForm").live("submit",startUpload);
    $(document).on('click',"#save", function(event){
        var $form = $("#generateForm");
        var myvalidator = $form.bValidator();
        event.preventDefault();


        $form.trigger("submit");

    });
    $(document).on('click',".deleteFile", function(event){

        var $deleteLink = $(this);

        var rowId = $(this).attr("data-row-id");
        var tableName = $(this).attr("data-table-name");
        var fieldName = $(this).attr("data-field-name");
        var token = $(this).attr("data-token");
        var url = $(this).attr("data-url");

        $("#saveResult").fadeIn("slow");

        $("#saveResult").html("Deleting...");

        event.preventDefault();

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'html',
            data: {
                table_name : tableName,
                id : rowId,
                field_name : fieldName,
                token : token,
            },
            success: function(response, textStatus, XMLHttpRequest) {
                if (response == 'fail' || response == 'Invalid Request!' ){

                    $("#saveResult").addClass("error").html("There was an error!").delay(2000).fadeOut("slow");
                    return false;
                }
                else {
                    $("#saveResult").addClass("success").html('File deleted!').delay(2000).fadeOut("slow");
                    $deleteLink.closest("div.field").find(".previewFileContainer").fadeOut("slow", function (){
                        $(this).remove();
                    });
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $("#saveResult").html("There was an error!");
                if (typeof console != 'undefined')
                    console.dir(XMLHttpRequest);
                return false;
            }
        });

        // $form.trigger("submit");

    });


    //Function that saves the form
    //Function is used for frontEnd and backEnd saving
    //Validates the form fields with bValidator
    /*
    function saveGenerateForm(event){
    var $form = $("#generateForm");
    var myvalidator = $('#generateForm').bValidator();
    event.preventDefault();
    if(myvalidator.data('bValidator').isValid()) {


    $("#saveResult").fadeIn("slow");

    $("#saveResult").html("Submitting...");

    var datas = $form.serialize();
    var  url = $form.attr( 'action' );
    $.ajax({
    url: url,
    type: 'POST',
    dataType: 'html',
    enctype: 'multipart/form-data',
    data: datas,
    success: function(response, textStatus, XMLHttpRequest) {
    if (!response){

    $("#saveResult").html("There was an error!").delay(2000).fadeOut("slow");
    return false;
    }
    else {
    if(response == 'Created!'){
    $("#save").hide();
    }
    $("#saveResult").html(response).delay(2000).fadeOut("slow");


    }
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    $("#saveResult").html("There was an error!");
    if (typeof console != 'undefined')
    console.dir(XMLHttpRequest);
    return false;
    }
    });

    }
    }
    */


});

// callback for image manager
function responsive_filemanager_callback(fieldID){
    console.log(field_id);
    var url=jQuery('#'+field_id).val();
    alert('update '+field_id+" with "+url); //your code
}

function initMCE(){
    tinymce.init({
        // General options
//        mode : "textareas",
        selector : "textarea.mceEditor",
        //            editor_selector : "mceEditor",
        //            editor_deselector : "mceNoEditor",
         relative_urls: false,
				remove_script_host: false,
        //            remove_script_host : false,
        //        content_css : "/css/styles.css",
        forcePasteAsPlainText : true,
        width: 800,
        height:400,
        menubar:false,
        plugins: [
            "advlist autolink link image lists charmap print preview hr anchor pagebreak",
            "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
            "table contextmenu directionality emoticons paste textcolor responsivefilemanager code"
        ],
        toolbar1: "charmap | source | html | code | undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect ",
        toolbar2: "| responsivefilemanager | link unlink anchor | image media | forecolor backcolor  | print preview code ",
        image_advtab: true ,

        external_filemanager_path:"filemanager/",
        filemanager_title:"File Manager" ,
        external_plugins: { "filemanager" : "../../filemanager/plugin.min.js"}


        //             document_base_url: HTML_PATH,

        //            theme : "advanced",
        //            plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,images",

        // Theme options
        //theme_advanced_buttons1 : "images,save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
        //            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        //            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        //            theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
        //            theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontsizeselect,|,cleanup,|,code,",
        //            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,images,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        //            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,ltr,rtl,|,fullscreen",
        //theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
        //            theme_advanced_toolbar_location : "top",
        //            theme_advanced_toolbar_align : "left",
        //            theme_advanced_statusbar_location : "bottom",
        //            theme_advanced_resizing : true,

        // Skin options
        //            skin : "o2k7",
        //            skin_variant : "silver",
        // Drop lists for link/image/media/template dialogs
        //            template_external_list_url : "js/template_list.js",
        //            external_link_list_url : "js/link_list.js",
        //            external_image_list_url : "js/image_list.js",
        //            media_external_list_url : "js/media_list.js",
        //
        // Replace values for the template plugin
        //            template_replace_values : {
        //                username : "Some User",
        //                staffid : "991234"
        //            }

    });
}

jQuery(document).ready(function($) {
    $('.ConfirmDelete').click(function() {
        return confirm('Delete record?');
    });
});


//Ajax UPLOAD SCRIPTS

function startUpload(event){
    var $form = $("#generateForm");

    var myvalidator = $('#generateForm').bValidator();

    if(myvalidator.data('bValidator').isValid()) {
        $("#saveResult").fadeIn("fast");

        $("#saveResult").html("Submitting.....");
        if(!isLiveEdit()){
            if(($(".modal").length == 0 )){
                $("body").append("<div class='modal'></div>").find(".modal").fadeIn();

            }else{
                $(".modal").fadeIn();
            }
        }
    }
}

$(document).on("keyup",".limitedCharacterText textarea", function(e){

    var value = $(this).val();
    var length = $(this).val().length;
    var maxLength = $(this).attr("data-max-length");

    if (length > maxLength) {
        $(this).val(value.substring(0, maxLength));
    } else {
        $(this).closest(".limitedCharacterText").find(".limitedCharacterCountDisplay").text(maxLength - length);
    }
});
//Character counter function
function countChar(val) {
    var len = val.value.length;
    if (len >= 500) {
        val.value = val.value.substring(0, 500);
    } else {
        $('#charNum').text(500 - len);
    }
};

function isLiveEdit(){
    if($("#liveEditContainer").length > 0 ){
        return true;
    }else{
        return false;
    }
}

function stopUpload(success,tableName,reloadId){
    reloadId = typeof reloadId !== 'undefined' ? reloadId : 0;
    var result = '';
    if(!isLiveEdit()){
        $(".modal").delay(3000).fadeOut();
    }

    if (success == 1){
        if(isLiveEdit()){
            $("#saveResult").html("Saved! Refreshing").delay(1000).fadeOut("slow");

            setTimeout(function() {
                $("#liveEditContainer").fadeOut("fast");
                window.location.reload(true);
                }, 1000);
        }else{
            //$("#saveResult").removeClass("error").addClass("success").html("Saved!").delay(3000).append(" Redirecting you...").fadeOut("slow",redirect(tableName));
            $("#saveResult").removeClass("error").addClass("success").html("Saved!").append(" Reloading...").fadeOut(700,redirect(tableName,reloadId));
        }
    }else{
        $("#saveResult").addClass("error").html("Error saving.<br /> "+success).delay(3000).fadeOut("slow");
    }
    return true;
}



function updateCoords(c)
{
    $('#x').val(c.x);
    $('#y').val(c.y);
    $('#w').val(c.w);
    $('#h').val(c.h);
};

function checkCoords()
{
    if (parseInt($('#w').val())) return true;
    alert('Please select a crop region then press submit.');
    return false;
};

$(document).ready(function(){

    $('.datetime-pick').datetimepicker({
        format:'Y-m-d H:i:s'
    });
    $('.date-pick').livequery(function(){

        Date.firstDayOfWeek = 0;
        Date.format = 'yy-mm-dd';

        $( ".date-pick" ).datepicker({dateFormat:"yy-mm-dd"});
    });
    /*
    $('.url').livequery(function(){
    $(".url").each(function(){
    var value = $(this).val();
    if(value == ""){
    $(this).val("http://");
    }
    });

    });
    */
    //Handles the time field
    $(document).on('change',"select[fieldType='time']", function(){
        var fieldName = $(this).attr('fieldName');
        var hours = $("select[name='"+fieldName+"_cms_hour']").val();
        var mins = $("select[name='"+fieldName+"_cms_minute']").val();
        var secs = $("select[name='"+fieldName+"_cms_seconds']").val();
        $("input[name='"+fieldName+"']").val(hours+":"+mins+":"+secs);
    });



    //Image crop


    $(document).on('click',".cropSubmit",function(){
        if (  $("#x").val() == '' || $("#y").val() == '' || $("#w").val() == '' || $("#h").val() == ''){
            return false;
        }
        $("#saveResult").fadeIn("fast");
        $("#saveResult").html("Submitting.....");


        var $form = $(this).closest("form");
        var datas = $form.serialize();
        var  url = $form.find("[name='cropUrl']").val();

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'html',
            data: datas,
            success: function(response, textStatus, XMLHttpRequest) {
                if (!response || response == '0'){
                    $("#saveResult").html("There was an error!").delay(2000).fadeOut("slow");
                    return false;
                }
                else {
                    var croppedImageUrl = response;
                    var croppedImage = "<img src='"+croppedImageUrl+"' />";
                    $("#saveResult").html("Image was successfully cropped!<br />"+croppedImage+"<br /><br />").delay(3000).fadeOut("fast");
                    $("#thumb_gen").hide(500);
                    $("#thumbContainer").html(croppedImage);
                    jcrop_api.release();
                    $("#x").val('');
                    $("#y").val('');
                    $("#w").val('');
                    $("#h").val('');
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $("#saveResult").html("There was an error!");
                if (typeof console != 'undefined')
                    console.dir(XMLHttpRequest);
                return false;
            }
        });
    });




    $(".loadMoreForm").submit(function(e){
        e.preventDefault();

        var classes = $(".loadToThis").find("li:first").attr("class");
        var loadMoreHtml = $(".loadToThis .loadMore:last").closest("li").html();
        var url = $(this).attr("action");
        var data = $(this).serialize();

        $.post(url, data ,
            function(result){
                $(".loadToThis").html(result);

                //  $.each(result,function(k,v){
                //                                $(".loadToThis").append('<li class="'+classes+'"><a href="'+v.link+'">'+v.displayField+'</a></li>')
                //                        });

                $(".loadToThis").append('<li>'+loadMoreHtml+'</li>');

                $( "#loadMoreModal" ).dialog("close");

                initTooltips();
        });

    });




    // END IMage crop

    $(document).on('click',".loadMore",function(){

        $(".loadToThis").each(function(){
            $(this).removeClass("loadToThis");
        });
        $(this).closest("ul").addClass("loadToThis");

        var tableName = $(this).attr('data-table-name');
        $(".orderBySelect").html("<option value=''>Loading...</option>");
        $.get("actions/getTableFields.php?tableName="+tableName+"",
            function(data) {
                $(".orderBySelect").html("<option value=''>Please select a field</option>");
                $.each(data,function (key,value){
                    $(".orderBySelect").append("<option value='"+key+"'>"+value+"</option>");
                });

            }, "json");

        var $cur = $(this);

        var foreignKey = $(this).attr('data-foreign-key');
        var foreignKeyId = $(this).attr('data-foreign-key-id');

        $( "#loadMoreModal [name='tableName']").val(tableName);
        $( "#loadMoreModal [name='foreignKey']").val(foreignKey);
        $( "#loadMoreModal [name='foreignKeyId']").val(foreignKeyId);

        $( "#loadMoreModal" ).dialog({
            height: 280,
            width: 370,
            modal: true,
            title : 'Load more records',
        });
    });

    $(document).on('click',".bulkEditBtn" ,function(){
        $("#bulkEditContainer .result").html('');
        $(".modal").show();
        $("#bulkEditContainer").show("normal");


    });

    $(document).on('click',".modal, .closeBulkEditBtn",function(e){
        e.preventDefault();
        $(".modal").hide();
        $("#bulkEditContainer").hide("normal");
    });
    $(document).on('change',"#select-all", function(){
        $("#list input[name='ids[]']").each(function(){
            var value = $(this).val();
            if($(this).is(':checked')){
                $("#cmsgen_rowsToEdit [value='"+value+"']").attr("selected","selected");
            }else{
                $("#cmsgen_rowsToEdit [value='"+value+"']").removeAttr("selected");
            }
        });
    });
    $(document).on('change',"#list input[name='ids[]']", function(){
        var value = $(this).val();
        if($(this).is(':checked')){
            $("#cmsgen_rowsToEdit [value='"+value+"']").attr("selected","selected");
        }else{
            $("#cmsgen_rowsToEdit [value='"+value+"']").removeAttr("selected");
        }
    });
});


$(document).on("submit", ".bulkEditForm", function (e){
    var $form = $(this);
    $form.find("input[type='submit']").val('Saving...');
    e.preventDefault();
    var url = $(this).attr("action");
    var data = $(this).serialize();
    $("#bulkEditContainer .result").html('Saving...');
    notify('Saving the record...','loading',false,false);

    $.post(url, data ,
        function(result){

            $form.find("input[type='submit']").val('Save');
            if(result.status == 'success'){
                $("#bulkEditContainer .result").addClass('success');
                $("#bulkEditContainer .result").html('Saved.');

                notify('Record was saved','success',3000,false);
            }else{
                $("#bulkEditContainer .result").addClass('error');
                $("#bulkEditContainer .result").html(result.error);

                notify(result.error,'error',3000,false);
            }

        }, "json");
});


/**
* Display the notification message
*
* @param message The message to be displayed
* @param status success/error
* @param delay delay in seconds or false to last forever
* @param close The jQuery object of the object you want to hide after the message is displayed, false for nothing
*/
function notify(message,status,delay,close){

    //When notifying 2 messages at the same time.
    if($('.notificationMessage').length){
        $('.notificationMessage').remove();

    }


    $(".modal").after('<div class="notificationMessage shadow round notify-'+status+'">'+message+'</div>');

    if(delay != false){
        $('.notificationMessage').show('fast').delay(delay).hide('fast', function(){
            $('.notificationMessage').remove();
        });

    }else{
        $('.notificationMessage').show('fast');
    }

    if(close != false){
        close.hide('fast');
    }

}



/**
* Converts the input form to an ajax searchable Select2 input
*/

(function($){
    $.fn.ajaxForeignKey = function(){
        $(this).select2({


            ajax: {
                url: "actions/search_ajax_foreign_key.php",
                dataType: "json",
                data: function(term, page) {
                    return {
                        q: term,
                        table : $(this).data("table-name"),
                        foreignKey : $(this).data("foreign-key"),
                        originalTable : $(this).data("original-table"),
                    };
                },

                results: function(data, page) {
                    return {
                        results: data
                    };
                }
            },

            escapeMarkup: function (m) { return m; }


        }).css({width : "300px"});
    };

})(jQuery);