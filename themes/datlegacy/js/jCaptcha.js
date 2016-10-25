$(document).bind(
    'ready',
    function() {
        $('.form-group.captcha .ccm-captcha-image').each(function() {
            //Attach hover backdrop
            var _b = $('<div class="hover-backdrop"><div class="sprite-animation refresh-50"></div></div>');
            _b.prependTo($(this).parent());
            /*_b.find('.sprite-animation').animateSprite('play');*/

            //Hover

            $(this).hover(function() {
                _b.find('.sprite-animation').animateSprite({
                    fps: 30,
                    loop: false
                }).animateSprite('restart');
            },
            function() {
                _b.find('.sprite-animation').animateSprite('stop');
            });

        });
    }
);