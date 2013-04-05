var scrolling = false;

$(function(){
    $("a[href=#]").click(function(e) { e.preventDefault(); });

    $("a.room-edit").click(function() {
        $(".form-room-edit").slideDown();
        $("input[name=title]").focus();
    });
    $("a.room-cancel").click(function() {
        $(".form-room-edit").slideUp();
    });

    $(document).on('scroll', function() {
        scrolling = true;
        if (inView($("#chatFooter"))) scrolling = false;
    });

    timestamp = 0;
    loadNewMessages(timestamp);
    window.setInterval("loadNewMessages(timestamp)", 2000);

    $(".form-message textarea").focus();
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

            // keep up with messages unless looking at something
            if (!scrolling) scrollDown();
        });
    });
}

function scrollDown()
{
    $(window).scrollTop(99999);
    // $('window').animate({scrollTop: 99999}, 800);
}

function inView(elem)
{
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

    var elemTop = $(elem).offset().top;
    var elemBottom = elemTop + $(elem).height();

    return ((elemBottom >= docViewTop) && (elemTop <= docViewBottom));
}