var newsTools = {
    service: '/index.php/tools/blocks/datnews/service',
    getColor: function() {
        return {
            R: Math.round(80 + (Math.random() * 90)),
            G: Math.round(100 + (Math.random() * 90)),
            B: Math.round(130 + (Math.random() * 90))
        };
    },
    init: function($root) {
        $root.find('.news-item').each(function(){
            var newColor = newsTools.getColor();

            $(this).css({
                'background-color': 'rgb(' + newColor.R + ',' + newColor.G + ',' + newColor.B + ')'
            });

            $(this).unbind().click(function() {
                newsTools.openNews($(this).attr('news-id'));
            });
        });
    },
    openNews: function(id) {
        $.ajax({
            url: this.service + '?mode=getNewsInfo&id=' + id,
            success: function(msg) {
                var _r = JSON.parse(msg);
                $('#news-viewer .news-viewer-title').html(_r[0].Title);
                $('#news-viewer .news-viewer-content').html(_r[0].Content);

                if (_r[0].ImageLink != null) {
                    $('#news-viewer .news-viewer-image-bg').css({
                        'background-image': 'url(' + _r[0].ImageLink + ')'
                    });
                } else {
                    $('#news-viewer .news-viewer-image-bg').css({
                        'background-image': ''
                    });
                }

                newsTools.openViewer();
            }
        });
    },
    appendViewer: function() {
        $('#news-viewer').prependTo('body').addClass('body-viewer');
        $('#news-viewer').each(function() {
            if(!$(this).hasClass('body-viewer')) {
                $(this).remove();
            }
        });

        $('<div id="news-viewer-bg" style="display: none;"></div>').insertAfter('#news-viewer');
        $('#news-viewer .news-viewer-bg').click(function() {
            newsTools.closeViewer();
        });
    },
    openViewer: function() {
        $('body').css({'overflow' : 'hidden'});

        var bgColor = this.getColor();
        $('#news-viewer .news-viewer-image').css({
            'background-color': 'rgba(' + bgColor.R + "," + bgColor.G + "," + bgColor.B + ",0.8)"
        });

        $('#news-viewer').stop(true).fadeIn(300);
        $('#news-viewer-bg').stop(true).fadeIn(300);

        this.preparePlayer();
    },
    closeViewer: function() {
        $('body').css({'overflow' : ''});

        $('#news-viewer').stop(true).fadeOut(300);
        $('#news-viewer-bg').stop(true).fadeOut(300);
    },
    preparePlayer: function() {
        $('#news-viewer').find('iframe[src*=youtu]').each(function() {
            var ratio = 0.5625;

            var width = $(this).outerWidth();
            $(this).css({
                height: (width * ratio) + 'px'
            });
        });
    }
};

$(document).ready(function() {
    $('.news-container').each(function() {
        newsTools.init($(this));
        newsTools.appendViewer();
    });
});