$(function() {

    $('#side-menu').metisMenu();

});

/* Arena Points Flush Countdown */

$(function() {
    var dateElement = $("#flush-datetime");

    if(dateElement.length) {
        var date = new Date(dateElement.text());

        dateElement.text(("0" + date.getDate()).slice(-2) + "." + ("0" + (date.getMonth()+1)).slice(-2) + "." + date.getFullYear() + " " + ("0" + date.getHours()).slice(-2) + ":" + ("0" + date.getMinutes()).slice(-2) + ":" + ("0" + date.getSeconds()).slice(-2));

        $('strong#flush-countdown').countdown(date, function(event) {
            if(event.strftime('%D') > 0)
                $(this).html(event.strftime('%Dd %Hh %Mm %Ss'));
            else
                $(this).html(event.strftime('%Hh %Mm %Ss'));
        });
    }
});

/* Vote Points Countdown */
$(function() {
    var dateElements = $(".next_vote_countdown");

    dateElements.each(function () {
        if($(this).length) {
            var date = new Date($(this).text());

            $(this).text(("0" + date.getDate()).slice(-2) + "." + ("0" + (date.getMonth()+1)).slice(-2) + "." + date.getFullYear() + " " + ("0" + date.getHours()).slice(-2) + ":" + ("0" + date.getMinutes()).slice(-2) + ":" + ("0" + date.getSeconds()).slice(-2));

            $(this).countdown(date, function(event) {
                if(event.elapsed) {
                    location.reload();
                }

                if(event.strftime('%D') > 0)
                    $(this).html(event.strftime('%Dd %Hh %Mm %Ss'));
                else
                    $(this).html(event.strftime('%Hh %Mm %Ss'));
            });
        }
    });
});
//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function() {
    $(window).bind("load resize", function() {
        topOffset = 50;
        width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1) height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    });

    var url = window.location;
    var element = $('ul.nav a').filter(function() {
        return (this.href == url.href || (url.href.indexOf(this.href) == 0 && $.inArray(url.href.charAt(this.href.length), ['/', '?', '#']) >= 0));
    }).addClass('active').parent().parent().addClass('in').parent();
    if (element.is('li')) {
        element.addClass('active');
    }
});
