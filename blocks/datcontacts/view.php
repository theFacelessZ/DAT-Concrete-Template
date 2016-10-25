<?php

$c = Page::getCurrentPage();
$isEdit = $c->isEditMode();
?>

<div block-id="<?php echo $bID ?>" class="info-container white dat-contacts">
    <div class="map-wrapper">
        <?php echo $EmbeddedMap ?>
    </div>
    <div class="info-container-wrapper content-wrapper text-center" style="padding-top: 25pt;;">
        <div class="info-head">
            <div class="title">
                Наши контакты
            </div>
        </div>

        <div class="column-wrapper">
            <div class="column-2 filler filler-bg" style="text-align: left;">
                <div class="line-title">Задайте нам вопрос</div>
                <div class="line-underline"></div>
                <?php
                if (($isEdit && \Application\Controller\Multiarea::getAreaLevel() > 0) || !$isEdit) {
                    $a = new Area('ContactsFeedbackArea');
                    $a->display($c);
                }
                ?>
            </div>
            <div class="column-2 center-vertical contacts-main">
                <div>
                    <p class="mail"><?php echo $EMail ?></p>
                    <p class="phone filler filler-bg"><?php echo $PhoneNumber ?></p>
                    <p class="address"><?php echo $Address ?></p>
                </div>

                <script type="text/javascript" src="//vk.com/js/api/openapi.js?121"></script>
                <div id="vk_groups" style="margin: 25px auto;"></div>
                <script type="text/javascript">
                    VK.Widgets.Group("vk_groups", {mode: 2, width: "220", height: "200"}, 98441341);
                </script>
            </div>
        </div>
    </div>
</div>
