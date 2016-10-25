/*

    jStick module
    by Anthony Lupow

 */

var jStick = {
    handlerObject: function() {
        this.maxHeight = 0;
        this.handler = function() { jDebug.log('Empty handler.') };
    },
    scrollBinds: [],
    getObjScreenPos: function($obj) {
        return {
            top: Math.abs($(window).scrollTop() - $obj.offset().top),
            left: Math.abs($(window).scrollLeft() - $obj.offset().left)
        }
    },
    getScrollBind: function(maxTop, $obj, stickPos) {
        /*var maxTop = $(window).scrollTop() + _max;*/
        return function() {
            var scrollTop = $(window).scrollTop();
            if (scrollTop >= maxTop) {
                var newPos = stickPos - (scrollTop - maxTop);

                if (newPos > stickPos + $obj.outerHeight()) {
                    newPos = stickPos;
                }

                $obj.css({
                    top: stickPos - (scrollTop - maxTop)
                });
            } else if ($obj.position().top < stickPos) {
                $obj.css({
                    top: stickPos
                });
            }
        };
    },
    //TODO: DYNAMIC HEIGHT SCROLL BIND
    getScrollBindDynamic: function($dynamicContainer, $obj, stickPos) {
        return function() {
            var maxScroll = $dynamicContainer.offset().top + $dynamicContainer.outerHeight() - $obj.outerHeight() - stickPos;
            var scrollTop = $(window).scrollTop();

            if (scrollTop >= maxScroll) {
                var newPos = stickPos - (scrollTop - maxScroll);

                if (newPos > stickPos + $obj.outerHeight()) {
                    newPos = stickPos;
                }

                $obj.css({
                    top: stickPos - (scrollTop - maxScroll)
                });
            } else if ($obj.position().top < stickPos) {
                $obj.css({
                    top: stickPos
                });
            }
        };
    },
    stick: function($obj, $maxScrollTop, $dynamicContainer, $stickTop) {
        jDebug.log('Initializing stick ' + ($obj.attr('id') != 'undefined' ? '#' + $obj.attr('id') : '') + ($obj.attr('class') != 'undefined' ? ' .' + $obj.attr('class') : ''));

        var pos = this.getObjScreenPos($obj);

        $obj.attr({
            'stick-id': new Date().getTime().toString()
        });

        var newObj = $obj.clone();
        var parent = $obj.parent();
        var offset = {
            left: 0,
            top: 0
        }
        offset.left += ($obj.outerWidth(true) - $obj.outerWidth()) / 2;

        /*$obj.parent().prepend(newObj);*/
        //prepend to new div block
        $('body').prepend(newObj);
        if (parent.css('position') == 'relative') {
            var _o = parent.offset();
            offset.left += _o.left;
            offset.top += _o.top;
        }

        newObj.addClass('stick-object').css({
                position: 'fixed',
                left: pos.left - offset.left,
                top: pos.top - offset.top,
                'z-index': 999,
                width: $obj.outerWidth()

            }
        );
        $obj.css({
            opacity: 0,
            'pointer-events': 'none'
        });

        //Add scroll bind
        if ($maxScrollTop != 'undefined' || $dynamicContainer != 'undefined') {
            for (i = 0; i < this.scrollBinds.length; i++) {
                $(window).unbind('scroll', this.scrollBinds[i]);
            }
            if ($dynamicContainer == null || $dynamicContainer == 'undefined') {
                var newHandler = this.getScrollBind($maxScrollTop, newObj, $stickTop);
            } else {
                var newHandler = this.getScrollBindDynamic($dynamicContainer, newObj, $stickTop);
            }
            /*var newHandler = this.getScrollBind($maxScrollTop, newObj, 0); //TODO: magic num fix (0 - top position)*/
            /*var newHandler = this.getScrollBindDynamic($dynamicContainer, newObj, 0);*/
            $(window).bind('scroll', newHandler);
            this.scrollBinds.push(newHandler);
        }

        return newObj;
    },
    stickPosTop: function($obj, posTop, $maxScrollTop, $dynamicContainer) {
        if ($maxScrollTop == 'undefined') { $maxScrollTop = 0; }
        var newObj = this.stick($obj, $maxScrollTop, $dynamicContainer, posTop);
        newObj.css({
            /*'transition': '200ms ease-out',*/
            'top': posTop
        });
    },
    unstick: function($obj) {
        var id = $obj.attr('stick-id');
        if (id == '') { return; }

        $('.stick-object[stick-id=' + id + ']').remove();
        $('[stick-id=' + id + ']').removeAttr('stick-id').css({
            opacity: '',
            'pointer-events': ''
        });
    },
    unstickAll: function($class) {
        $('body').find('.' + $class + '[stick-id]:not(.stick-object)').each(function() {
            jStick.unstick($(this));
        });
    }
};
