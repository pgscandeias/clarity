$(function(){
    $("a[href=#]").click(function(e) { e.preventDefault(); });

    $("a.room-create").click(function() {
        $(".form-room-create").slideDown();
        $("input[name=title]").focus();
    });
    $("a.room-cancel").click(function() {
        $(".form-room-create").slideUp();
    });
});