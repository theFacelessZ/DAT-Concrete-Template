<?php

$fp = FilePermissions::getGlobal();
$tp = new TaskPermission();

$uh = Loader::helper('concrete/urls');
$bt = BlockType::getByHandle('datnews');

?>

<style>
    .p-news {
        background-color: rgb(232, 232, 232);
        display: table;
        width: 100%;
        padding: 10px;
        margin: 5px;
    }

    .p-news-title {
        float: left;
        margin: 0 !important;
    }

    .p-news-controls {
        float: right;
    }

    .p-news-controls > * {
        cursor: pointer;
    }

    .redactor_redactor-content.redactor_editor {
        resize: vertical;
    }
</style>

<script>
    var prepareRedactor = function($obj, onchange) {
        $obj.redactor({
            minHeight: '100',
            'concrete5': {
                filemanager: <?php echo $fp->canAccessFileManager() ?>,
                sitemap: <?php echo $tp->canAccessSitemap() ?>,
                lightbox: true
            },
            callbacks: {
                change: function() {
                    console.log(this.code.get());
                    //onchange(this.code.get());
                }
            }
        });
    };

</script>

<p>
    <?php
    print Loader::helper('concrete/ui')->tabs(array(
        array('form-preview', 'Список'),
        array('form-editor', 'Редактор')
    ));
    ?>
</p>

<script>
    (function ($) {
        $.each(['show', 'hide'], function (i, ev) {
            var el = $.fn[ev];
            $.fn[ev] = function () {
                this.trigger(ev);
                return el.apply(this, arguments);
            };
        });
    })(jQuery);

    var newsHelper = {
        bID: 0,
        cID: 0,
        lastContentValue: '',
        regex: {
            youtube: /(?:[hH][tT]{2}[pP][sS]{0,1}:\/\/)?[wW]{0,3}\.{0,1}[yY][oO][uU][tT][uU](?:\.[bB][eE]|[bB][eE]\.[cC][oO][mM])?\/(?:(?:[wW][aA][tT][cC][hH])?(?:\/)?\?(?:.*)?[vV]=([a-zA-Z0-9--]+).*|([A-Za-z0-9--]+))/i
        },
        service: $('input[name=services]').val() + '?block=datnews&',
        switchTabs: function(newTabName) {
            $('*[id^=ccm-tabs-').find('*[data-tab=' + newTabName + ']').click();
        },
        titleValue: function() {
            return $('input[name=Name]').val();
        },
        contentValue: function() {
            return $('textarea[name=Content]').val();
        },
        setContent: function(value) {
            $('#ccm-tab-content-form-editor textarea[name=Content]').val(value);
        },
        replaceContent: function(search, replace) {
            var _c = this.contentValue();
            _c = _c.replace(search, replace);
            this.setContent(_c);
        },
        clearValues: function() {
            $('#ccm-tab-content-form-editor input[name=Name]').val('');
            $('#ccm-tab-content-form-editor textarea[name=Content]').redactor('set', '');
        },
        refreshPreview: function() {
            $.ajax({
                url: this.service + 'mode=getNewsPreview&cID=' + this.cID,
                success: function(msg) {
                    $('#newsListWrapper').html(msg);
                }
            });
        },
        addEntry: function() {
            $.ajax({
                method: "POST",
                url: this.service + 'mode=addNewsRecord&cID=' + this.cID,
                data: {Title: this.titleValue(), Content: this.contentValue()} ,
                success: function(msg) {
                    //clear
                    newsHelper.clearValues();

                    newsHelper.switchTabs('form-preview');
                    newsHelper.refreshPreview();
                }
            });
        },
        removeEntry: function(id) {
            $.ajax({
                url: this.service + 'mode=removeNews&id=' + id,
                success: function() {
                    newsHelper.refreshPreview();
                }
            });
        },
        getEntryInfo: function(id, done) {
            var _r = null;

            $.ajax({
                url: this.service + 'mode=getNewsInfo&id=' + id,
                success: function(msg) {
                    _r = JSON.parse(msg);
                    done(_r[0]);
                }
            });
        },
        editEntry: function(id) {
            newsHelper.getEntryInfo(id, function(res) {
                $('#ccm-tab-content-form-editor input[name=Name]').val(res.Title);
                $('#ccm-tab-content-form-editor textarea[name=Content]').redactor('set', res.Content);
                $('*[name=editID]').val(id);

                $('#editNewsEntry').show();
                $('#addNewsEntry').hide();

                newsHelper.lastContentValue = res.Content;
                console.log(newsHelper.lastContentValue);

                newsHelper.switchTabs('form-editor');
            });
        },
        commitEdit: function() {
            $.ajax({
                method: "POST",
                url: this.service + 'mode=commitEditNews&id=' + $('*[name=editID]').val() + '&cID=' + this.cID,
                data: { Title: this.titleValue(), Content: this.contentValue() },
                success: function(msg) {
                    newsHelper.switchTabs('form-preview');

                    newsHelper.clearValues();

                    $('#editNewsEntry').hide();
                    $('#addNewsEntry').show();

                    newsHelper.refreshPreview();
                }
            });
        },
        moveUp: function($item) {
            if ($item.index() == 0) return;

            $item.insertBefore(
                $('#newsListWrapper').find('.p-news').eq(
                    $item.index() - 1
                )
            );

            this.refreshOrder();
        },
        moveDown: function($item) {
            $item.insertAfter(
                $('#newsListWrapper').find('.p-news').eq(
                    $item.index() + 1
                )
            );

            this.refreshOrder();
        },
        refreshOrder: function() {
            var ids = [];
            var order = [];

            $('.p-news').each(function() {
                ids.push($(this).attr('news-id'));
                order.push($(this).index());
            });

            $.ajax({
                method: "POST",
                url: this.service + 'mode=refreshOrder',
                data: {
                    ids: ids,
                    order: order
                },
                success: function() {
                    newsHelper.refreshPreview();
                }
            })
        }
    };

    $(document).ready(function() {
        newsHelper.bID = thisbID;
        newsHelper.cID = <?=$cID?>;
        newsHelper.refreshPreview();

        $('#addNewsEntry').unbind().click(function() {
            newsHelper.addEntry();
        });

        $('#editNewsEntry').unbind().click(function() {
            newsHelper.commitEdit();
        });

        //ITEMS MODIFY EVENTS
        $('#newsListWrapper').on('click', '.fa.fa-trash', function() {
            //remove news entry
            //TODO: remove query, use removal flag
            newsHelper.removeEntry($(this).closest('.p-news').attr('news-id'));
        });

        $('#newsListWrapper').on('click', '.fa.fa-pencil', function() {
            newsHelper.editEntry($(this).closest('.p-news').attr('news-id'));
        });

        /*$('#newsListWrapper').on('click', '.fa[class*=fa-chevron]', function() {
            alert('Temporary unavailable.');
        });*/
        $('#newsListWrapper').on('click', '.fa.fa-chevron-up', function() {
            newsHelper.moveUp($(this).closest('.p-news'));
        })

        $('#newsListWrapper').on('click', '.fa.fa-chevron-down', function() {
            newsHelper.moveDown($(this).closest('.p-news'));
        });

        //====REDACTOR====
        $('.redactor-content').each(function() {
            prepareRedactor($(this));
        });

        //AUTO FORMATTING
        /*$('.redactor_editor').on('keydown.callback.redactor', function() {
            console.log(this.code.get());
        });*/
    });
</script>

<input type="hidden" name="services" value="<?php echo $uh->getBlockTypeToolsURL($bt) ?>/service"/>
<input type="hidden" name="contentID" value="<?php echo $cID ?>"/>
<input type="hidden" name="editID" value="0"/>

<div id="ccm-tab-content-form-preview" class="ccm-tab-content">
    <fieldset>
        <legend>Новости</legend>

        <div class="form-group">
            <label>Список</label>
            <div id="newsListWrapper"></div>
        </div>
    </fieldset>
</div>

<div id="ccm-tab-content-form-editor" class="ccm-tab-content">
    <fieldset>
        <legend>Редактор новости</legend>

        <div class="form-group">
            <label>Название</label>
            <input type="text" class="form-control" name="Name" value="">
        </div>

        <div class="form-group">
            <label>Текст</label>
            <textarea style="display: none;" class="redactor-content" name="Content"></textarea>
        </div>

        <div class="form-group">
            <span class="btn btn-success" id="addNewsEntry"><?php echo t('Add Entry') ?></span>
            <span class="btn btn-success" id="editNewsEntry" style="display: none;"><?php echo t('Edit Entry') ?></span>
        </div>

    </fieldset>
</div>
