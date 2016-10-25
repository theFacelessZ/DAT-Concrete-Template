<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

$u=new \User();
$ui=\UserInfo::getByID($u->uID);

$bID = intval($_REQUEST['bID']);
$cID = time();

$db = \Loader::db();
$db->query('DELETE FROM btDatNewsItems WHERE bID=?', array(null));

?>

<script type="text/javascript">
    var thisbID=parseInt(<?php echo intval($_REQUEST['bID'])?>);
    var thisbtID=parseInt(<?php echo $bt->getBlockTypeID()?>);
</script>

<?php $this->inc('form.php', array('bID' => $bID, 'cID' => $cID)); ?>