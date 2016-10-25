<?php
/*$em = $c->getBlockCollectionObject();*/

$c = Page::getCurrentPage();
$isEdit = $c->isEditMode();
?>

<div class="offer-container<?php echo ($isEdit) ? ' edit-mode' : '' ?> filler" style="z-index: 0;">
    <div class="content-wrapper offer-main animation-showup">
        <div class="offer-text"><?php echo $OfferText ?></div>
        <div class="offer-button button" root-container="offer-container" hide-container="offer-main" target-container="offer-form" onclick="jSwitch.switchCustom(this)"><?php echo $OfferButton ?></div>
    </div>
    <div class="content-wrapper offer-form animation-showup" style="display: none;">
        <?php
        if (($isEdit && \Application\Controller\Multiarea::getAreaLevel() > 0) || !$isEdit) { ?>
            <div class="offer-form">
                <?php
                $a = Area::getOrCreate($c, 'OfferFormArea');
                $a->display($c);
                ?>
            </div>
        <?php }
        ?>
    </div>
</div>