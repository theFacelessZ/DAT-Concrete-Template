
var jMenu = {
    activateButton: function(blockID, buttonClass, containerClass) {
        $('.' + buttonClass + '.active').each(function() {
            $(this).removeClass("active");
        });
        $("." + buttonClass + "[block-id=" + blockID + "]").addClass("active");

        //Menu line
        var _b = $('.' + buttonClass + '.active');
        if (_b == null || _b == undefined || _b.length == 0) {
            $('.menu-line').css({ opacity: 0 });
        } else {
            $('.menu-line').css({
                left: Math.floor(_b.position().left),
                width: Math.ceil(_b.outerWidth()),
                opacity: 1
            });
        }
    },
    set: function($root) {
        var w = 0;

        $root.find('.b-button').each(function() {
            w += $(this).outerWidth();
        });

        $root.find('.menu-container').width(w);

        $root.find('.menu-container').draggable({
            axis: "x",
            start: function(e, ui) {
                if ($(document).width() >= $root.find('.menu-container').outerWidth()) {
                    e.preventDefault();
                }
            },
            stop: function(e, ui) {

                if (e.target.position().left > 0) {
                    _c.css({
                        'left': 0
                    });
                }
                if (e.target.position().left < e.target.outerWidth() + $(document).width()) {
                    e.target.css({
                        'left': e.target.outerWidth() + $(document).width()
                    });
                }
            }
        });
    }
}