
$(document).ready(function() {
    addEvents();
    fixScroll();
});


var addEvents = function() {
    $('#seeFollowup').on('click', function(e) {
        e.preventDefault();
        $('.customer:not(.followup), .customer:not(.followup) + .row').hide();
    });

    $('#seeAll').on('click', function(e) {
        e.preventDefault();
        $('.customer, .row').show();
    });
};


var fixScroll = function() {
    $(window).bind('scroll', function() {
        if ($(window).scrollTop() > 240) {
            $('.row.years.main')
                .addClass('fixed')
                .css({
                    left : -$(window).scrollLeft(),
                    paddingLeft :15
                })
                .parent()
                .css('padding-top', 41);
        }
        else {

            $('.row.years.main')
                .removeClass('fixed')
                .css({
                    left : 0,
                    paddingLeft: 0
                })
                .parent()
                .css('padding-top', 0);

            //$('.row.years.main').removeClass('fixed').css('left', 0).parent().css('padding-top', 0);
        }
    });
}