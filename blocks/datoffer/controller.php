<?php

namespace Application\Block\Datoffer;

use Core;
use Loader;
use Page;

use \Concrete\Core\Block\BlockController;

class Controller extends BlockController {
    protected $btTable = 'btDatOffer';
    protected $btInterfaceWidth = "800";
    protected $btInterfaceHeight = "600";

    public function getBlockTypeName() {
        return t('DAT Offer');
    }

    public function getBlockTypeDescription() {
        return t('A DAT site offer section block.');
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

    }

    public function delete() {
        parent::delete();
    }

    public function save($args) {
        parent::save($args);
    }
}