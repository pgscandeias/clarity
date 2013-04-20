$(function(){
    $("a[href=#]").click(function(e) { e.preventDefault(); });

    $("a.show-invite").click(function() {
        $(".form-team-add").slideDown();
        $("input[name=name]").focus();
    });
    $("a.cancel-invite").click(function() {
        $(".form-team-add").slideUp();
        $("input[name=email]").val('');
    });
});