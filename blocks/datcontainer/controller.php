<?php

namespace Application\Block\Datcontainer;

use File;
use Core;
use Loader;
use Page;

use \Concrete\Core\Block\BlockController;

class Controller extends BlockController {
    
	protected $btTable = 'btDatSection';
	protected $btExportTables = array('btDatSection', 'btDatSectionMedia', 'btDatSectionSchedule');
	/*protected $btDefaultSet = 'basic';*/
	protected $btInterfaceWidth = "800";
    protected $btInterfaceHeight = "600";
	
	protected $btExportFileColumns = array('fID');
	
	public function getBlockTypeName() {
		return t('DAT Section');
	}
	
	public function getBlockTypeDescription() {
		return t('A DAT site section block.');
	}
	
	public function getSearchableContent() {
		$content = '';
		$db = Loader::db();
		$v = array($this->bID);
		$q = 'SELECT * from btDatSectionMedia where bID = ?';
		$r = $db->query($q, $v);
		
		foreach($r as $row) {
			$content.= $row['title'].' ';
			$content.= $row['description'].' ';
		}
		
		return $content;
	}
	
	public function add() {
		$this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');
	}
	
	public function edit() {
		$this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');
		
		$db = Loader::db();
		
		$q = $db->GetAll('SELECT * FROM btDatSectionSchedule WHERE bID = ?', array($this->bID));
		$this->set('events', $q);

		$q = $db->GetAll('SELECT * FROM btDatSectionMedia WHERE bID = ?', array($this->bID));
		$this->set('mediaEntries', $q);

		$q = $db->GetAll('SELECT * FROM btDatSectionStaff WHERE bID = ?', array($this->bID));
		$this->set('staff', $q);
	}
	
	public function composer() {
		$this->edit();
	}
	
	public function registerViewAssets() {
		$this->requireAsset('javascript', 'jquery');
	}
	
	public function getEntries() {
		$db = Loader::db();
		$r = $db->GetAll('SELECT * from btDatSectionMedia WHERE bID = ? ORDER BY id', array($this->bID));
		
		$rows = array();
		foreach($r as $q) {
			if (!$q['linkURL'] && $q['internalLinkCID']) {
				$c = Page::getByID($q['internalLinkCID'], 'ACTIVE');
				$q['linkURL'] = $c->getCollectionLink();
				$q['linkPage'] = $c;
			}
			$rows[] = $q;
		}
		
		return $rows;
	}
	
	public function getEvents() {
		$db = Loader::db();
		$q = $db->GetAll('SELECT * FROM btDatSectionSchedule WHERE bID = ? ORDER BY SchTStart ASC', array($this->bID));

		return $q;
	}

	public function getStaff() {
		$db = Loader::db();
		$q = $db->GetAll('SELECT * FROM btDatSectionStaff WHERE bID = ?', array($this->bID));

		return $q;
	}
	
	public function getEventTime($s) {
		return date("Y-m-d H:i:s", strtotime("2015-08-11 " . $s . ":00"));
	}
	
	public function view() {
		$this->set('mediaEntries', $this->getEntries());
		$this->set('events', $this->getEvents());
		$this->set('staff', $this->getStaff());
	}
	
	public function delete() {
		$db = Loader::db();
		$db->delete('btDatSectionMedia', array('bID' => $this->bID));
		$db->delete('btDatSectionSchedule', array('bID' => $this->bID));
		$db->delete('btDatSectionStaff', array('bID' => $this->bID));
		parent::delete();
	}
	
	public function save($args) {
		$db = Loader::db();

		$db->execute('DELETE FROM btDatSectionMedia WHERE bID = ?', array($this->bID));
		$db->execute('DELETE FROM btDatSectionSchedule WHERE bID = ?', array($this->bID));
		$db->execute('DELETE FROM btDatSectionStaff WHERE bID = ?', array($this->bID));

		parent::save($args);

		$count = count($args['isVideo']);
		$i = 0;

		//MEDIA
		while ($i < $count) {
			$db->execute('INSERT INTO btDatSectionMedia (bID, fID, ytLink, isVideo) values(?, ?, ?, ?)', array(
				$this->bID,
				intval($args['fID'][$i]),
				$args['ytLink'][$i],
				intval($args['isVideo'][$i])
			));
			
			$i++;
		}
		
		//EVENTS
		//$count = intval($args['SchItemsCount']);
		$count = count($args['SchTStart']);
		$i = 0;
		
		while ($i < $count) {
			$db->execute('INSERT INTO btDatSectionSchedule (bID, SchTStart, SchTEnd, SchTitle, SchDescription, SchDay) values(?, ?, ?, ?, ?, ?)', array(
				$this->bID,
				$this->getEventTime($args['SchTStart'][$i]),
				$this->getEventTime($args['SchTEnd'][$i]),
				$args['SchTitle'][$i],
				$args['SchDescription'][$i],
				$args['SchDay'][$i]
			));
			
			$i++;
		}

		//STAFF
		$count = count($args['MemberName']);
		$i = 0;

		while($i < $count) {
			$db->execute('INSERT INTO btDatSectionStaff (bID, photoFID, MemberName, MemberDescription) VALUES(?,?,?,?)', array(
				$this->bID,
				$args['photoFID'][$i],
				$args['MemberName'][$i],
				addslashes($args['MemberDescription'][$i])
			));

			$i++;
		}
	}
}