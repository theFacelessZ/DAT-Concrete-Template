/*

 DAT jScrollTo module
 by Anthony Lupow
 anthony.lupow@gmail.com

 */

var jScrollTo = function(obj, speed, offset) {
    if (typeof speed === "undefined") speed = 200;
    if (typeof offset === "undefined") offset = 0;

    $('html body').stop(true).animate({
        scrollTop: $(obj).offset().top - offset
    }, speed);
};

var jScrollHandler = {
    buttonClass: "b-button",
    speed: 200,
    containerClass: "info-container",
    topOffset: 0,
    waypoints: [],
    dynamicOffset: false,
    offsetPercentage: 0,
    useStick: false,
    handle: function (button) {
        var blockID = $(button).attr("block-id");

        jScrollTo("." + this.containerClass + "[block-id=" + blockID + "]", this.speed, this.topOffset);

        //jMenu.activateButton(blockID, this.buttonClass, this.containerClass);


    },
    initialize: function(obj) {
        //initialize waypoint
        var _bc = this.buttonClass;
        var _cc = this.containerClass;

        this.waypoints.push($(obj).waypoint({
            handler: function(direction) {

                var bID = -1;

                if (direction == "down") {

                    var _o = $(this.element);
                    if (!_o.hasClass('info-container')) {
                        _o = _o.find('.info-container');
                    }

                } else {

                    var _o = $(this.element).prev();
                    if (!_o.hasClass('info-container')) {
                        _o = _o.find('.info-container');
                    }

                    //MENU STICK WINDOWS-LIKE UP SCROLL
                    //var prevMenu =
                    jStick.unstickAll('info-container.menu');

                }

                bID = _o.attr('block-id');

                jMenu.activateButton(bID, _bc, _cc);

                /*$('.' + _bc + '.active').each(function() {
                    $(this).removeClass('active');
                });
                $('.' + _bc + '[block-id="' + bID + '"]').addClass('active');*/

                if (this.dynamicOffset) {
                    jScrollHandler.updateScrollTopPercentage();
                }
            },
            offset: (this.dynamicOffset) ? (this.offsetPercentage + this.topOffset) + '%' : '50%'
        }));

        if (this.useStick) {
            this.waypoints.push($(obj).waypoint({
                handler: function(direction) {
                    var menuObj = $(obj).find('.info-container-menu');

                    jStick.unstickAll('info-container-menu');
                    if (direction == "down") {
                        //MENU STICK WINDOWS-LIKE DOWN SCROLL
                        /*jStick.stickPosTop(menuObj, jScrollHandler.topOffset,
                            $(obj).outerHeight() + $(obj).offset().top - menuObj.outerHeight());*/
                        jStick.stickPosTop(menuObj, jScrollHandler.topOffset, null, $(obj));
                    } else {
                        //STICK PREVIOUS MENU (IF EXISTS)
                        var prevContainer = null;
                        prevContainer = $(obj).prev();

                        if (!prevContainer.hasClass('info-container')) {
                            prevContainer = prevContainer.find('.info-container');
                        }

                        if (prevContainer.length == 0) return;

                        var prevMenuObj = prevContainer.find('.info-container-menu');
                        /*jStick.stickPosTop(prevMenuObj, jScrollHandler.topOffset,
                            prevContainer.outerHeight() + prevContainer.offset().top - prevMenuObj.outerHeight());*/
                        jStick.stickPosTop(prevMenuObj, jScrollHandler.topOffset, null, prevContainer);
                    }
                },
                offset: 0
            }));
        }
    },
    removeWaypoint: function(obj) {
        $(this.waypoints).each(function(i) {
            if ($(this).element == obj) {
                this.waypoints.splice(i, 1);
            }
        });
    },
    initializeBind: function(obj) {
        $(document).bind('ready', this.initialize(obj));
    },
    updateWaypoints: function() {
        $(this.waypoints).each(function() {
            this[0].context.refresh();
        });
    },
    updateScrollTopPercentage: function() {
        this.offsetPercentage =  ($(document).scrollTop() / ($(document).height())).toFixed(2) * 100;
        jDebug.log(this.offsetPercentage);
    }
};

