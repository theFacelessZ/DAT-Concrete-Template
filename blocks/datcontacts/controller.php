<?php

namespace Application\Block\Datcontacts;

use Core;
use Loader;
use Page;
/*use Concrete\Core\Block\BlockController;*/

class Controller extends \Concrete\Core\Block\BlockController {
    protected $btTable = 'btDatContact';
    /*protected $btExportTables = array('btDatContact');*/
    /*protected $btDefaultSet = 'basic';*/
    protected $btInterfaceWidth = "800";
    protected $btInterfaceHeight = "600";

    protected $btCacheBlockRecord = true;
    protected $btCacheBlockOutput = true;
    protected $btCacheBlockOutputOnPost = true;
    protected $btCacheBlockOutputForRegisteredUsers = true;

    public function getBlockTypeName() {
        return t('DAT Contacts');
    }

    public function getBlockTypeDescription() {
        return t('A DAT site contacts section block.');
    }

    public function add() {
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');
    }

    public function edit() {
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');
    }

    public function composer() {
        $this->edit();
    }

    public function registerViewAssets() {
        $this->requireAsset('javascript', 'jquery');
    }

    public function view() {
        //Format phone
        $_p = $this->get('PhoneNumber');
        $_p = trim($_p);

        if ($_p[0] != '+') {
            $_p = '+' . $_p;
        }

        //First block code 4 digits
        $p_global = substr($_p, 0, 4);
        //Second block (regional code) 2 digits
        $p_region = substr($_p, 4, 2);
        //First phone block 3 digits
        $p_a = substr($_p, 6, 3);
        //Second phone block 2 digits
        $p_b = substr($_p, 9, 2);
        //Thirt phone block 2 digits
        $p_c = substr($_p, 11, 2);
        //Final formatting
        $_p = sprintf('%s (%s) %s-%s-%s', $p_global, $p_region, $p_a, $p_b, $p_c);

        $this->set('PhoneNumber', $_p);

        //Format phone end
    }

    public function delete() {
        parent::delete();
    }

    public function save($args) {
        parent::save($args);
    }
}