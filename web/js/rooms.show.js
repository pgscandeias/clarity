var scrolling, uri, lastMessageId = 0;

$(function(){
    scrolling = false;
    uri = '/' + account + '/rooms/' + room + '.json';

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

    loadNewMessages(lastMessageId);
    window.setInterval("loadNewMessages(lastMessageId)", 2000);

    // Sumit on ctrl+enter or cmd+enter
    $("textarea").on('keydown', function(e) {
        if (e.which == 13 && (e.metaKey || e.ctrlKey)) {
            $("#form-message").submit();
        }
    });

    // Catch form submit
    $("#form-message").on('submit', function() {
        var form = $(this);
        $.ajax({
            url: uri,
            type: 'POST',
            dataType: 'json',
            data: form.serialize(),
            success: function(data) {
                lastMessageId = data.lastMessageId;
                $("#chat").append(data.message);
                $("#form-message textarea").val('');
                if (!scrolling) scrollDown();
            },
            error: function() {
                alert('Sorry, there was an error. Please try again.');
            }
        });
        return false;
    });

    $(".form-message textarea").focus();
});


function loadNewMessages(since)
{
    requestUri = uri;
    if (since > 0) requestUri = uri + '?since=' + since;

    $.getJSON(requestUri, function(data) {
        lastMessageId = data.lastMessageId;
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