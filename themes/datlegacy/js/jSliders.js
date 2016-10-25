/*

 DAT jSliders module
 by Anthony Lupow
 anthony.lupow@gmail.com

 */

jQuery.fn.initializeSlider = function() {
    $(this).each(function() {
        jSlider.set(this);
    });
};

var jSlider = {
    time: 10000,
    intervals: [],
    acceleration: true,
    slideNext: function(wrapper) {
        var count = $(wrapper).find('.slide').length - 1;

        var jSlides = $(wrapper).parent().attr('jslider-slides');
        var _sOffset = (jSlides != "undefined") ? Math.ceil(jSlides / 2) - 1 : 0;

        if (count == 0) return;

        var shownSlide = $(wrapper).find('.slide.shown');
        var nextSlide = (shownSlide.index() >= count - _sOffset) ? $(wrapper).find('.slide').eq(0 + _sOffset) : shownSlide.next();

        this.transition(nextSlide, wrapper);
    },
    slideInt: function(root, index) {
        var wrapper = $(root).find('.slider-wrapper');
        var shownIndex = $(root).find('.slider.shown').index();

        var jSlides = $(wrapper).parent().attr('jslider-slides');
        var _sOffset = (jSlides != undefined) ? Math.ceil(jSlides / 2) - 1 : 0;

        var sCount = $(wrapper).find('.slide').length;

        if (index > sCount - 1 - _sOffset) {
            index = _sOffset;
        }
        if (index < _sOffset || index == -1) {
            index = sCount - 1 - _sOffset;
        }

        var nextSlide = wrapper.find('.slide:not(.instance)').eq(index);

        jDebug.log("SWITCHING TO INDEX " + index);

        this.transition(nextSlide, root);
    },
    transition: function(next, root) {
        var shown = $(root).find('.slider-wrapper .slide.shown');
        var shownInd = shown.index();
        var nextInd = $(next).index();
        var jSlides = $(root).attr('jslider-slides');
        jSlides = (jSlides != undefined) ? jSlides : 0;


        //OPACITY BASED TRANSITION TODO:MID-ANIMATION SWITCH SUPPORT
        if ($(root).hasClass('slide-opacity')) {
            shown.addClass('transition');
            next.addClass('transition');

            next.css({opacity: 1});
            shown.stop(true).animate({opacity: 0}, this.time/10, function() {
                $(this).removeClass('shown');
                next.addClass('shown');

                shown.removeClass('transition');
                next.removeClass('transition');
            });
        } else if ($(root).hasClass('slide-line')) {

            shown.removeClass('shown');

            var _nW = $(next).outerWidth();
            var _rW = $(root).outerWidth();

            var _l = (_nW / 2) - (_rW / 2);

            $(next).prevAll().each(function() {
                _l += $(this).outerWidth();
            });

            if (this.acceleration) {
                $(root).find('.slider-wrapper').css({'transform' : 'translate3d(' + (_l * -1) + 'px,0,0)'});
            } else {
                $(root).find('.slider-wrapper').css({left: _l * -1});
            }

            $(next).addClass('shown');

            //ADD FOCUS
            var jsSlides = jSlides;
            if (jsSlides != "undefined" && jsSlides > 1) {
                var _indexOffset = (jsSlides - 1) / 2;
                var _startIndex = $(next).index() - _indexOffset;

                if (_startIndex < 0) {
                    _startIndex = 0;
                    jsSlides /= 2;
                }

                $(root).find('.slide.focused').each(function() {
                    $(this).removeClass('focused');
                });

                for (i = 0; i < jsSlides; i++) {
                    $(root).find('.slide').eq(_startIndex + i).addClass('focused');
                }
            }

            //Infinite scroll
            /*var _v = 0;
            if (jSlides != "undefined") {
                _v = Math.ceil(jSlides / 2) - 1;
            }

            var newInd = $(next).index();
            var _infCount = newInd - shownInd;
            var _sCount = $(root).find('.slide').length;

            for (i = 0; i < _infCount; i++) {
                if (_infCount > 0) {
                    $(root).find('.slide').eq(0).appendTo($(root).find('.slider-wrapper'));
                } else {
                    $(root).find('.slide').eq(_sCount - 1).prependTo($(root).find('.slider-wrapper'));
                }
            }*/
        }

        //Dynamic height support
        if ($(root).hasClass('slider-dynamic')) {
            if (!$(root).hasClass('initialized')) {
                //stop container animation if height is not initialized yet
                $(root).closest('.info-container-switch-wrapper').stop(true).css({
                    height: ''
                });
            }


            var _nh = this.calcDynamicHeight(root);
            $(root).css({height: _nh});
            if (!$(root).outerHeight() == _nh) {
                /*$(root).stop().animate({height: this.calcDynamicHeight(root)}, {
                    duration: this.time/10,
                    easing: "easeOutQuad",
                    queue: false
                });*/

            }
        } else {
            /*if (!$(root).hasClass('initialized')) {
                $(root).css({
                    height: this.calcDynamicHeight(root)
                });
            }*/
        }
    },
    set: function(root) {
        if ($(root).hasClass('slide-opacity')) {
            $(root).find('.slide').each(function(i) {
                if (i == 0) {
                    $(this).css({opacity: 0}).addClass('shown').stop(true).animate({opacity: 1}, 500);
                } else {
                    $(this).stop(true).animate({opacity: 0}, 500);
                }
            });
        }

        if ($(root).hasClass('slide-line') && $(root).is(':visible') && !($(root).hasClass('initialized'))) {
            //Slide width calculation
            if ($(root).attr('jslider-slides') > 0) {
                var _slideW = $(root).outerWidth() / $(root).attr('jslider-slides');
                $(root).find('.slide').each(function() {
                    $(this).css({width: _slideW});

                    /*var _rootPadding = 40;
                    $(this).find('.slider-inherit-width').each(function() {
                        $(this).css({width: _slideW - _rootPadding });
                    });*/
                });

                //INSTANCES (ENDLESS SCROLL)

                /*$(root).find('.slide:not(.instance)').slice(Math.floor(_jsSlide / 2) * -1).clone().addClass('instance').insertBefore($('.slide:first', root));
                $(root).find('.slide:not(.instance)').slice(0, Math.floor(_jsSlide / 2)).clone().addClass('instance').insertAfter($('.slide:last', root));*/
            }

            //Refresh waypoints after resizing root
            $(root).bind(
                'transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd',
                function() {
                    jScrollHandler.updateWaypoints();
                }
            )


            var _oW = 0;

            $(root).find('.slide').each(function() {
                _oW += $(this).outerWidth();
            });

            $(root).find('.slider-wrapper').width(_oW);

            //INITIAL POSITION
            var _jsSlide = $(root).attr('jslider-slides');
            if (_jsSlide != "undefined" && _jsSlide > 0) {
                jSlider.slideInt(root, Math.ceil(_jsSlide / 2) - 1);
                //jSlider.slideInt(root, 0);
            } else {
                jSlider.slideInt(root, 0);
            }

            //Hide arrows if necessary
            if ($(root).find('.slide').length <= 1) {
                $(root).find('.slider-buttons').hide();
            }

            $(root).addClass('initialized');
        }

        if ($(root).hasClass('slide-opacity'))  {
            this.intervals.push(setInterval(function() { jSlider.slideNext(root) }, jSlider.time));
        }

        //button init
        $(root).find('.slider-button.button-circle').each(function(i) {
            $(this).click(function () {
                jSlider.slideInt(root, i);
            });
        });

        $(root).find('.slider-buttons').each(function() {
            //TODO: STEP SIZE (PARAMETER DEPENDENCE)
            $(this).unbind().click(function() {
                if ($(this).hasClass('arrow-left')) {
                    //var nextInd = $(root).find('.slide.shown:not(.instance)').index() - 1 - jSlides;
                    //if (nextInd < jSlides) { nextInd = $(root).find('.slide').length - jSlides - 1; }
                    jSlider.slideInt(root, $(root).find('.slide.shown:not(.instance)').index() - 1);
                } else {
                    jSlider.slideInt(root, $(root).find('.slide.shown:not(.instance)').index() + 1);
                }
            });
        });
    },
    calcDynamicHeight: function(root) {
        jDebug.log('Recalculating height...');
        var _h = 0;
        $(root).find('.slide.focused').each(function() {
            var _nH = $(this).outerHeight();
            if (_nH > _h) {
                _h = _nH;
            }
        })

        return _h;
    },
    stopAll: function() {
        $(this.intervals).each(function() {
            window.clearInterval(this);
        })
    }
};

$(window).ready(
    function() {
        $('.slider-container').initializeSlider();
    }
)