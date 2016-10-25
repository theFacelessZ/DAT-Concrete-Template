<?php

$fp = FilePermissions::getGlobal();
$tp = new TaskPermission();

?>

<style>

</style>
<script>
    var prepareRedactor = function($obj) {
        $obj.redactor({
            minHeight: '100',
            'concrete5': {
                filemanager: <?php echo $fp->canAccessFileManager() ?>,
                sitemap: <?php echo $tp->canAccessSitemap() ?>,
                lightbox: true
            }
        });
    };

    $(document).ready(function() {
        $('.redactor-content').each(function() {
            prepareRedactor($(this));
        });
    });

</script>

<fieldset>
    <legend>Предложение</legend>
    <div class="form-group">
        <label>Текст предложения</label>
        <div class="redactor-edit-content"></div>
        <textarea style="display:none" class="redactor-content" name="OfferText"><?php echo $OfferText ?></textarea>
    </div>

    <div class="form-group">
        <label>Текст кнопки принятия</label>
        <input class="form-control" type="text" name="OfferButton" value="<?php echo $OfferButton ?>">
    </div>
</fieldset>

