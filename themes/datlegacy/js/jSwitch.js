/*

DAT jSwitch module
by Anthony Lupow
anthony.lupow@gmail.com

*/

//TODO: AUTO ACTIVATION

var jSwitchAnimation = [
    'animation-showupleft',
    'animation-showupright'
];

var jSwitch = {
    speed: 600,
    opacityDivider: 5,
    _t_useSlideAnimation: true,
    getHiddenHeight: function(target) {
        //TODO: REMOVE
        target.css({
            display: 'block',
            visibility: 'hidden',
            position: 'relative'
        });

        var height = $(target).outerHeight();

        target.css({
            display: '',
            visibility: '',
            position: ''
        });

        return height;
    },
    handleButton: function(button) {
        var bClass = $(button).attr('class');
        var bContainer = $(button).closest('.button-container');

        if (this._t_useSlideAnimation) {
            //determine neither pressed button is after or before active button
            var _aindex = bContainer.find('.button.active').index();
            var _bindex = $(button).index();
            var outAnimationType = (_aindex > _bindex) ? jSwitchAnimation[0] : jSwitchAnimation[1];
        }

        bContainer.find('.button.active').each(function() {
            $(this).removeClass('active');
        });

        $(button).addClass('active');

        return {animation: outAnimationType};
    },
    switchCustom: function(button) {
        if ($(button).hasClass('active')) return;

        this.handleButton(button);

        var rootContainer = $(button).attr('root-container');
        var hideContainer = $(button).attr('hide-container');
        var targetContainer = $(button).attr('target-container');

        var container = $(button).closest('.' + rootContainer);
        var _last = container.find('.' + hideContainer);
        var _new = container.find('.' + targetContainer);

        this.transition(_last, _new, container);
    },
    switch: function (button) {
        if ($(button).hasClass('active')) return;

        var _animation = this.handleButton(button).animation;
        jDebug.log(_animation);

        var target = $(button).attr('target-container');
        var container = $(button).closest('.info-container').find('.info-container-switch-wrapper');

        var _last = container.find('.info-container-switch:visible');
        var _new = container.find('.' + target);

        this.transition(_last, _new, container, _animation);
    },
    transition: function($last, $new, $container, animationClass) {
        //var _hDiff = _last.height() - _new.height();

        //remove all animation classes
        for (i = 0; i < jSwitchAnimation.length; i++) {
            /*$last.removeClass(jSwitchAnimation[i]);*/
            $new.removeClass(jSwitchAnimation[i]);
        }

        $last.stop(true).animate({opacity: 0}, {
            duration: jSwitch.speed / jSwitch.opacityDivider,
            queue: false,
            easing: 'easeOutQuart',
            complete: function() {

                //recalculate height
                /*var _lastHeight = $last.outerHeight();*/
                var _newHeight = $new.outerHeight();
                if (_newHeight == 0) {
                    _newHeight = jSwitch.getHiddenHeight($new);
                }

                jDebug.log(_newHeight + ($container.outerHeight() - $container.height()));

                $container.css({
                    height: $container.height()
                }).stop(true).animate({height: _newHeight + ($container.outerHeight() - $container.height())}, {
                    duration: jSwitch.speed,
                    queue: false,
                    easing: 'easeOutQuart',
                    step: function() {
                        $container.css({
                            /*'overflow-x': 'visible',
                            'overflow-y': 'hidden'*/
                        })
                    },
                    complete: function () {
                        jScrollHandler.updateWaypoints();
                        $(this).css({height: ''}); //remove height
                    } //"easeOutQuart"
                });

                $last.hide();
                //$new.show();
                $new.css({display: ''});

                //initialize sliders
                if ($new.find('.slider-container').length > 0) {
                    jSlider.set($new.find('.slider-container'));
                }

                $new.stop(true).addClass(animationClass).animate({opacity: 1}, {
                    queue: false,
                    easing: 'easeOutQuart',
                    duration: jSwitch.speed / jSwitch.opacityDivider
                });
            }
        });
    },
    prepare: function(container) {

    }
};