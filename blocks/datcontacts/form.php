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
    <legend>Контакты</legend>

    <div class="form-group">
        <label>Заголовок</label>
        <input type="text" class="form-control" name="Title" value="<?php echo $Title ?>">
    </div>

    <div class="form-group">
        <label>Адрес</label>
        <div class="redactor-edit-content"></div>
        <textarea style="display:none" class="redactor-content" name="Address"><?php echo $Address ?></textarea>
    </div>

    <div class="form-group">
        <label>Телефон</label>
        <input type="phone" class="form-control" name="PhoneNumber" value="<?php echo $PhoneNumber ?>">
    </div>

    <div class="form-group">
        <label>E-Mail</label>
        <input type="email" class="form-control" name="EMail" value="<?php echo $EMail ?>">
    </div>

    <div class="form-group">
        <label>IFrame карты</label>
        <div class="redactor-edit-content"></div>
        <textarea style="display:none" class="redactor-content" name="EmbeddedMap"><?php echo $EmbeddedMap ?></textarea>
        <!--<input type="text" class="form-control" name="EmbeddedMap" value="<?php echo addslashes($EmbeddedMap) ?>">-->
    </div>
</fieldset>

