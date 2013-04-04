$(function(){
    $("a[href=#]").click(function(e) { e.preventDefault(); });

    $("a.room-edit").click(function() {
        $(".form-room-edit").slideDown();
        $("input[name=title]").focus();
    });
    $("a.room-cancel").click(function() {
        $(".form-room-edit").slideUp();
    });

    timestamp = 0;
    window.setInterval("loadNewMessages(timestamp)", 2500);
});


function loadNewMessages(since)
{
    var uri = '/' + account + '/rooms/' + room + '.json';
    if (since > 0) uri += '?since=' + since;

    $.getJSON(uri, function(data) {
        timestamp = data.timestamp;
        $.each(data.messages, function(i, m) {
            // messages are pre-rendered
            $("#chat").append(m);
            scrollDown();
        });
    });
}

function scrollDown()
{
    $(window).scrollTop(99999);
}