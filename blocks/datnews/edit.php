<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

?>

<script>
    <?php if (is_object($b->getProxyBlock())) { ?>
    var thisbID=parseInt(<?php echo $b->getProxyBlock()->getBlockID()?>);
    <?php } else { ?>
    var thisbID=parseInt(<?php echo $b->getBlockID()?>);
    <?php } ?>
</script>

<?php $view->inc('form.php', array('cID', $cID));  ?>