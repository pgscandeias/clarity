$(function(){
    $("a[href=#]").click(function(e) { e.preventDefault(); });

    $("a.room-edit").click(function() {
        $(".form-room-edit").slideDown();
        $("input[name=title]").focus();
    });
    $("a.room-cancel").click(function() {
        $(".form-room-edit").slideUp();
    });

    $(window).scrollTop(99999);
});