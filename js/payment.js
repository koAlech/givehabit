function payMe() {

    var success;
    $.ajax({
        type: "POST",
        url: "http://192.168.12.11/ghServer/index.php/makePayment",
        data: JSON.stringify({"userId": "5060"}),
        dataType: "json",
        success: function(msg){
            //$("#lePay").replaceWith( '<div id="lePay" class="button transparent aqua">Done!</div>' );  //Your resulting action
            alert("Done!");
            location.reload(true);
        }
    });


}

function loadData() {

    var success;
    $.ajax({
        type: "GET",
        url: "http://192.168.12.11/ghServer/index.php/getCategories/5060",
        success: function(msg){
            var design = ((msg.Design === undefined || msg.Design == null)?"0":msg.Design);
            var tech = ((msg.Technology === undefined || msg.Technology == null)?"0":msg.Technology);
            var art = ((msg.Art === undefined || msg.Art == null)?"0":msg.Art);
            var video = ((msg.Video === undefined || msg.Video == null)?"0":msg.Video);

            $("#catDesign").replaceWith( '<div id="catDesign" class="count-number" data-from="0" data-to="'+design+'" data-speed="1000" data-refresh-interval="25"></div>' );  //Your resulting action
            $("#catTechnology").replaceWith( '<div id="catDesign" class="count-number" data-from="0" data-to="'+tech+'" data-speed="1000" data-refresh-interval="25"></div>' );  //Your resulting action
            $("#catArt").replaceWith( '<div id="catDesign" class="count-number" data-from="0" data-to="'+art+'" data-speed="1000" data-refresh-interval="25"></div>' );  //Your resulting action
            $("#catVideo").replaceWith( '<div id="catDesign" class="count-number" data-from="0" data-to="'+video+'" data-speed="1000" data-refresh-interval="25"></div>' );  //Your resulting action
        }
    });

}