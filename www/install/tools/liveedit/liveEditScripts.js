$(document).ready(function() {
        var docheight = $(document).height();
       // var editHeight = $("#liveEditContainer").height();
        //$("#liveEditContainer").css("margin-top",-(editHeight/2)+'px');
        //On escape key up, close the live edit window
//        $(document).keyup(function(e) {
//                if (e.keyCode == 27) { close(); }   // esc
//        });

        $( "#liveEditContainer" ).dialog({
                autoOpen: false,
                height: 600,
                width: 960,
                modal: true
        });
});


// this function is called from the edit button when "getLiveEditAttr($product['id'])" is called
function edit(id,table){

    $("#generatedForm").html("LOADING...<img src='../images/loading.gif' />");

    $( "#liveEditContainer" ).dialog( "open" );

    $.ajax({
            url: "../actions/generateForm.php",
            type: 'POST',
            dataType: 'html',
            enctype: 'multipart/form-data',
            data: {
                id : id,
                table : table
            },
            success: function(response, textStatus, XMLHttpRequest) {
                if (!response){
                    $("#generatedForm").html("There was an error!");
                    return false;
                }
                else {
                    $("#generatedForm").html(response);

                    initMCE();

                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $("#generatedForm").html("There was an error!");
                if (typeof console != 'undefined')
                    console.dir(XMLHttpRequest);
                return false;
            }
    });

}


function close(){
    //$("#liveEditContainer").animate({top: "-600px", opacity: 0}, 'slow');
   // $('.editModal').fadeOut("slow");
    $( "#liveEditContainer" ).dialog( "close" );
    //$("div").css("opacity","1");

}
