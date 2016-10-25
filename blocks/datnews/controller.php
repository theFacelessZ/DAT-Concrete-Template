<?php

namespace Application\Block\Datnews;

use Core;
use Loader;
use Page;
use Punic\Exception;

/*use Concrete\Core\Block\BlockController;*/

class Controller extends \Concrete\Core\Block\BlockController {
    protected $btTable = 'btDatNews';
    /*protected $btExportTables = array('btDatContact');*/
    /*protected $btDefaultSet = 'basic';*/
    protected $btInterfaceWidth = "800";
    protected $btInterfaceHeight = "600";

    protected $btCacheBlockRecord = true;
    protected $btCacheBlockOutput = true;
    protected $btCacheBlockOutputOnPost = true;
    protected $btCacheBlockOutputForRegisteredUsers = true;

    public function getBlockTypeName() {
        return t('DAT News');
    }

    public function getBlockTypeDescription() {
        return t('DAT site news section block.');
    }

    public function add() {
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');
    }

    public function edit() {
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');

        $this->requireAsset('core/file-manager');
        $this->requireAsset('core/lightbox');

        /*$db = Loader::db();
        $r = $db->GetAll("SELECT * FROM btDatNewsItems WHERE bID=?", array($this->bID));

        $this->set('newsItems', $r);*/
        $e = $this->getEntries();
        /*$this->set('newsItems', $e);*/
        $this->set('cID', $e[0]['cID']);
    }

    public function composer() {
        $this->edit();
    }

    public function registerViewAssets() {
        $this->requireAsset('javascript', 'jquery');
    }

    public function view() {
        $this->set('newsItems', $this->getEntries());
    }

    public function delete() {
        $db = \Loader::db();
        $db->execute('DELETE FROM btDatNewsItems WHERE bID=?', array(
            $this->bID
        ));

        parent::delete();
    }

    public function save($args) {
        if (!$args || count($args) == 0) $args=$_POST;

        //Update news to blockID
        $this->updateNews($args['contentID'], $this->bID);

        //Update first image link
        $r = $this->getEntries();

        foreach ($r as $item) {
            preg_match('/<img[^>]+>/i', $item['Content'], $_img);

            if ($_img && count($_img) > 0) {
                preg_match('/(src)=("[^"]*)/i', $_img[0], $_src);

                $_link = str_replace(array('src=', '"'), '', $_src[0]);

                $this->updateLink(intval($item['id']), $_link);
            }
        }

        parent::save($args);
    }

    function getEntries() {
        $db = Loader::db();
        $r = $db->GetAll("SELECT * FROM btDatNewsItems WHERE bID=? ORDER BY OrderIndex ASC", array($this->bID));
        return $r;
    }

    static function updateNews($cID, $bID) {
        $db = \Loader::db();
        $db->query('UPDATE btDatNewsItems SET bID=? WHERE cID=?', array(
            intval($bID),
            intval($cID)
        ));
    }

    static function updateLink($id, $link) {
        $db = \Loader::db();
        $db->query('UPDATE btDatNewsItems SET ImageLink=? WHERE id=?', array(
            $link,
            $id
        ));
    }
}

class NewsHelper {
    public $bID = -1;
    public $cID = 0;

    function __construct() {
        $db = \Loader::db();
        $this->db = $db;
    }

    public function getCID() {
        $r = $this->db->GetAll('SELECT * FROM btDatNewsItems WHERE bID=? AND cID=?', array(
            $this->bID,
            $this->cID
        ));

        if (count($r) === 0) {
            $cID = time();
        }
    }

    static function removeAllRecords($cID = 0) {
        $db = \Loader::db();
        $db->query('DELETE FROM btDatNewsItems WHERE cID=?', array($cID));
    }

    public function getNewsInfo() {
        $r = $this->db->GetAll("SELECT * FROM btDatNewsItems WHERE cID=? ORDER BY OrderIndex ASC", array($this->cID));
        return $r;
    }

    public  function removeNews($id) {
        $this->db->execute("DELETE FROM btDatNewsItems WHERE (id=?)", array(
            $id
        ));
    }

    public  function editNews($id, $newTitle, $newContent, $order = null) {
        //$this->removeNews($id);
        //$this->addNewsRecord($newTitle, $newContent);

        if ($order == null) {
            $this->db->query('UPDATE btDatNewsItems SET (Title, Content) VALUE (?, ?) WHERE id=?', array(
                $newTitle,
                $newContent,
                $id
            ));
        } else {
            $this->db->query('UPDATE btDatNewsItems SET (Title, Content, OrderIndex) VALUE (?, ?, ?) WHERE id=?', array(
                $newTitle,
                $newContent,
                $order,
                $id
            ));
        }

    }

    public  function reorderNews($ids, $order) {
        $i = 0;
        foreach($ids as $id) {
            $this->db->query("UPDATE btDatNewsItems SET OrderIndex=? WHERE id=?", array(
                $order[$i],
                $id
            ));

            $i++;
        }
    }

    public function getNewsInfoID($id) {
        return $this->db->GetAll('SELECT * FROM btDatNewsItems WHERE id=?', array($id));
    }

    public function getNewsPreview() {
        $q = $this->getNewsInfo();

        foreach($q as $item) {
            echo '<div class="p-news" news-id="'. $item['id'] .'">';
            echo '<p class="p-news-title">' . $item['Title'] . '</p>';
            echo '<div class="p-news-controls">
                    <i class="fa fa-chevron-up"></i>
                    <i class="fa fa-chevron-down"></i>
                    <i class="fa fa-pencil"></i>
                    <i class="fa fa-trash"></i>
                 </div>';
            echo '</div>';
        }
    }

    public function addNewsRecord($title, $content) {

        $order = 0;

        $q = $this->db->GetAll('SELECT * FROM btDatNewsItems WHERE cID=? ORDER BY OrderIndex DESC', array(
            $this->cID
        ));

        if (count($q) > 0) {
            $order = $q[0]["OrderIndex"] + 1;
        }

        $this->db->execute('INSERT INTO btDatNewsItems (cID, Title, Content, OrderIndex) VALUES(?, ?, ?, ?)',
            array(
                $this->cID,
                $title,
                $content,
                $order
            ));
    }

    public static function updateNews($cID, $bID) {
        $db = \Loader::db();
        $q = $db->query('UPDATE btDatNewsItems SET bID=? WHERE cID=?', array(
            intval($bID),
            intval($cID)
        ));
    }
}