


// CHART SPLINE
// ----------------------------------- 
(function(window, document, $, undefined){

	$(function(){

		var chartv3 = $('.chart-spline');
		if(chartv3.length)
			$.plot(chartv3, data, options);

	});

})(window, document, window.jQuery);


$(document).ready(function(){

		$(".ajax_foreign").ajaxForeignKey();


	$("#loader").hide();

	$( ".date-pick" ).datetimepicker({
		timepicker:false,
		format:'Y-m-d'
	}); 

	$( ".time-pick" ).datetimepicker({
		datepicker:false
	}); 

	$( ".datetime-pick" ).datetimepicker({
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

	$(".advanced-search input,.advanced-search select,.advanced-search textarea").each(function(){
		$(this).removeAttr("required");
		$(this).removeAttr("data-bvalidator");
		var fieldName = $(this).attr("name");
		$(this).attr("name",'data[Search]['+fieldName+']');
	});


	var ajaxGenerateForeignTarget ;
	var ajaxGenerateFieldname ;
	var ajaxGenerateTablename ;

	$(document).on("click",".ajaxGenerate", function(e){
		e.preventDefault();
		var href = $(this).attr('href')+"&ajax";

		ajaxGenerateFieldname =  $(this).attr('data-fieldname');
		ajaxGenerateTablename = $(this).attr('data-tablename');
		ajaxGenerateForeignTarget = $(this).parent();


		showAjaxPopup("<iframe style='width:100%;height:100%;border:0' src='"+href+"'></iframe>",1);


	});

	$("#alert-modal-ajax-close").click(function(e){
		ajaxGenerateForeignTarget.html("loading...");


		$.ajax({
			url: 'actions/get_foreign_dropdown.php',
			type: 'POST',
			dataType: 'html',
			data: "table="+ajaxGenerateTablename+"&fieldname="+ajaxGenerateFieldname,
			success: function(response, textStatus, XMLHttpRequest) {
				if (!response){
					showMsg("There was an error!",1);
					return false;
				}
				else {
					ajaxGenerateForeignTarget.html(response);

					$("select").not(".no-select2").select2({
						theme: 'bootstrap',
						dropdownAutoWidth:true,
					});
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				showMsg("There was an error!");
				if (typeof console != 'undefined')
					console.dir(XMLHttpRequest);
				return false;
			}
		});
	});

	function stopUploadAjax(success,tableName){
		var result = '';

		if (success == 1){
			$("#alert-modal-text").html("Saved...");
		}else{
			$("#alert-modal-text").html("Error saving.<br>"+success)
		}
		$("#alert-modal").modal("show");
		return true;
	}
	$("#advancedSearchBtn").click(function(e){
		e.preventDefault();
		$("div.advanced-search").toggle();
	});

	$("#delete").click(function(event){

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

						showMsg("There was an error!",1);
						return false;
					}
					else {
						showMsg(response,1);
						//$("#saveResult").html(response).delay(2000).fadeOut("slow");
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
	$(document).on('click',".save-generate-form", function(event){
		var $form = $(this).closest('form');
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

					showMsg("There was an error!",0);
					return false;
				}
				else {
					showMsg("File deleted!",1);
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



});


function startUpload(event){
	var $form = $(this).closest("form");

	var myvalidator = $form.bValidator();

	if(myvalidator.data('bValidator').isValid()) {
		$("#saveResult").fadeIn("fast");

		$("#saveResult").html("Submitting.....");

	}
}

function stopUpload(success,tableName){
	var result = '';

	if (success == 1){
		$("#alert-modal-text").html("Saved...<br>Redirecting you");
		redirect(tableName) ;
		//$("#saveResult").removeClass("error").addClass("success").html("Saved!").delay(3000).append(" Redirecting you...").fadeOut("slow",redirect(tableName));
	}else{
		$("#alert-modal-text").html("Error saving.<br>"+success)
		//$("#saveResult").addClass("error").html("Error saving.<br /> "+success).delay(3000).fadeOut("slow");
	}
	$("#alert-modal").modal("show");
	return true;
}


function showMsg(msg, status) {
	if (status == 1) // success
	{
		$("#alert-modal-text").html(msg);
	}
	else {
		$("#alert-modal-text").html(msg)
	}
	$("#alert-modal").modal("show");
	return true;
}

function showAjaxPopup(msg) {
	$("#alert-modal-text-ajax").html(msg);

	$("#alert-modal-ajaxpopup").modal("show");
	return true;
}

function initMCE(){
	tinymce.init({
		// General options
		//        mode : "textareas",
		selector : "textarea.mceEditor",
		//            editor_selector : "mceEditor",
		//            editor_deselector : "mceNoEditor",
		relative_urls : false,
		//            remove_script_host : false,
		//            convert_urls : true,
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

				processResults: function(data, page) {
					return {
						results: data
					};
				}
			},

			escapeMarkup: function (m) { return m; }


		}).css({width : "300px"});
	};

})(jQuery);

