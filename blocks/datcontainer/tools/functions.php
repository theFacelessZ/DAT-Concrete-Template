<?php

/*
 *
 * DAT theme specific functions
 * Anthony Lupow
 * anthony.lupow@gmail.com
 *
 */

function getYoutubeEmbUrl($input) {
    return str_replace(array("youtube.com/watch?v=", "https://", "http://", "youtu.be/", "www.", " "), "", $input);
}

/*
 *
 * TEST FUNCTIONS
 *
 */

function getBlocksMenu($areaName) {
    $out = array();
    $blocks = Page::getCurrentPage()->getBlocks($areaName);

    foreach ($blocks as $blockInfo) {
        array_push($out,
            array(
                'bID' => $blockInfo->bID,
                'Title' => Block::getByID($blockInfo->bID)->getInstance()->Title
            )
        );
    }

    return $out;
}

function buildMenu($areaName) {
    $blocksInfo = getBlocksMenu($areaName);

    echo '<div class="menu-container">';
    foreach($blocksInfo as $bInfo) {
        ?>
        <div class="b-button" block-id="<?php echo $bInfo['bID'] ?>" onclick="jScrollHandler.handle(this)">
            <p><?php echo $bInfo['Title'] ?></p>
        </div>
        <?php
    }
    echo '<div class="menu-line"></div>';
    echo '</div>';
}

/*
 *
 * END
 *
 */

function getImageURL($fID, $imgHelper) {
    if ($fID > 0) {
        $f = File::getByID($fID);
        if (is_object($f)) {
            //$url = $imgHelper->getThumbnail($f, 400, 400)->src;
            $url = $f->getRelativePath();
            echo str_replace('concrete//', '', $url);
        }
    }
}

function getImageThumb($fID, $imgHelper, $maxHeight, $maxWidth) {
    if ($fID > 0) {
        $f = File::getByID($fID);

        $thumb = $imgHelper->getThumbnail($f, $maxHeight, $maxWidth, false);
        return $thumb->src;
    }
}

class SchedulerBuilder {
    public $detailization; //Min per line
    public $linePerHour;

    public function __construct($linesPerHour) {
        $this->linePerHour = $linesPerHour;
        $this->detailization = 60 / $linesPerHour;
        $this->linePerHour = 60 / $this->detailization;
    }

    public static function getDefault() {
        return new self(30);
    }

    public function formatTime($s) {
        return date('H:i', strtotime($s));
    }

    public function rowRule($d, $h, $m, $event) {
        $eventStart = array(
            'H' => $this->strToHours($event['SchTStart']),
            'M' => $this->strToMinutes($event['SchTStart'])
        );
        $eventEnd = array(
            'H' => $this->strToHours($event['SchTEnd']),
            'M' => $this->strToMinutes($event['SchTEnd'])
        );

        if ($d == $event['SchDay']) {
            if ($eventStart['H'] == $h) {
                return ($m > $eventStart['M']);
            }

            if ($eventEnd['H'] == $h) {
                //return ($m <= $eventEnd['M']);
                if ($m == $eventEnd['M']) return false;
                return ($m < $eventEnd['M']);
            }

            return ($eventStart['H'] <= $h && $eventEnd['H'] >= $h);
        }

        return null;
    }

    public function getRowSpan($e) {
        $d1 = new DateTime($e['SchTStart']);
        $d2 = new DateTime($e['SchTEnd']);

        $diff = $d2->diff($d1);

        //return ceil((($diff->h * 60) + $diff->i) / 30);
        //return ceil((($diff->h * 60) + $diff->i) / $GLOBALS['dat-row-detailization']);
        return ceil((($diff->h * 60) + $diff->i) / $this->detailization);
    }

    public function strToHours($s) {
        return intval(date('H', strtotime($s)));
    }

    public function strToMinutes($s) {
        return intval(date('i', strtotime($s)));
    }

    public function isInBetween($e, $h, $m) {
        $esh = $this->strToHours($e['SchTStart']);
        $esm = $this->strToMinutes($e['SchTStart']);
        $eeh = $this->strToHours($e['SchTEnd']);
        $eem = $this->strToMinutes($e['SchTEnd']);

        if ($esh == floor($h)) {
            return ($esm <= $m) ? true : false;
        }

        if ($eeh == floor($h)) {
            return ($eem > $m) ? true : false;
        }

        return (($esh <= floor($h) && $eeh >= floor($h))) ? true : false;
    }

    public function needRow($es, $d, $h) {
        //$m = (floor($h) == $h) ? 0 : 30;
        $m = ($h - floor($h)) * 60;
        foreach ($es as $e) {
            if ($this->rowRule($d, floor($h), $m, $e)) {
                return false;
            }
        }

        return true;
    }

    public function getLineCount($es) {
        //$dMin = strToHours($es[0]['SchTStart']);
        //$dMax = strToHours($es[0]['SchTEnd']);
        //$detail = $GLOBALS['dat-row-detailization'];
        $dMin = new DateTime($es[0]['SchTStart']);
        $dMax = new DateTime($es[0]['SchTEnd']);

        foreach($es as $e) {
            //$dS = strToHours($e['SchTStart']);
            //$dE = strToHours($e['SchTEnd']);
            $dS = new DateTime($e['SchTStart']);
            $dE = new DateTime($e['SchTEnd']);

            if ($dMin > $dS) {
                $dMin = $dS;
            }

            if ($dMax < $dE) {
                $dMax = $dE;
            }
        }

        //$addLine = (date_format($dMin, 'i') != 0);
        //$addLines = (date_format($dMin, 'i') != 0 ? 1 : 0) + (date_format($dMax, 'i') > 30 ? 1 : 0);
        $span = $dMax->diff($dMin);
        //$LineCount = ceil((($span->h * 60) + $span->i) / $detail) + ceil(date_format($dMin,'i') / $detail);
        $LineCount = ceil((($span->h * 60) + $span->i) / $this->detailization) + ceil(date_format($dMin,'i') / $this->detailization);

        return array(
            'Min' => date_format($dMin, 'H'),
            'Max' => date_format($dMax, 'H'),
            'Span' => $span,
            //'AddLines' => $addLines,
            //'Lines' => ($span->h * 2) + round($span->i / 30) + $addLines
            'Lines' => $LineCount
        );
    }

    public function getDayEvent($es, $d, $h) {
        //$f = $h - floor($h);
        //$half = ($f == 0) ? false : true;
        $m = ($h - floor($h)) * 60;
        foreach ($es as $e) {
            if ($e['SchDay'] == $d && $this->strToHours($e['SchTStart']) == floor($h)) {
                $em = $this->strToMinutes($e['SchTStart']);
                if ($em >= $m && $em < $m + $this->detailization) {
                    return $e;
                }
                /*if ($half && $m >= 30) {
                    return $e;
                }
                if (!$half && $m < 30) {
                    return $e;
                }*/
            }
        }

        return null;
    }
}