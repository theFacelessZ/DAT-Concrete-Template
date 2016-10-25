<?php

$c = Page::getCurrentPage();
$isEdit = $c->isEditMode();

$slideCount = count($newsItems);
$itemPerPage = 8;
$pages = ($slideCount < $itemPerPage) ? 1 : ceil($slideCount / $itemPerPage);
?>

<div block-id="<?php echo $bID ?>" class="info-container white news-container">
    <div id="news-viewer" style="display: none;">
        <div class="news-viewer-wrapper">

            <div class="news-viewer-container animation-showup-px">
                <div class="news-viewer-image">
                    <div class="news-viewer-title"></div>
                    <div class="news-viewer-image-bg"></div>
                </div>
                <div class="news-viewer-content"></div>
            </div>

            <div class="news-viewer-bg"></div>
        </div>
    </div>

    <div class="content-wrapper news-wrapper">
        <div class="slider-container slide-line">
            <div class="slider-buttons arrow-left"></div>
            <div class="slider-buttons arrow-right"></div>
            <div class="slider-wrapper">
                <?php
                for($i = 0; $i < $pages; $i++) {
                    ?>
                    <div class="slide" style="width: 1000px;">
                        <?php
                        for($pI = 0; $pI < $itemPerPage; $pI++) {
                            $item = $newsItems[($itemPerPage * $i) + $pI];
                            if (is_null($item) || empty($item)) {
                                continue;
                            }

                            $f = isset($item['ImageLink']) && !empty($item['ImageLink']);
                            ?>
                            <div class="news-item" news-id="<?=$item['id']?>">
                                <div class="news-item-bg" <?php echo ($f) ? 'style="background-image: url(' . $item['ImageLink'] . ');"' : '' ?>>
                                    <?php if (!$f) { echo '<span class="news-item-letter">' . mb_substr($item['Title'], 0, 1, 'UTF-8') . '</span>'; } ?>
                                </div>
                                <div class="news-item-info">
                                    <p><?=$item['Title']?></p>
                                </div>
                            </div>
                            <?php
                        } ?>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
