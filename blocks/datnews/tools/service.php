<?php

//if (!isset($_GET['cID'])) die;

$h = new \Application\Block\Datnews\NewsHelper();
$h->bID = $_GET['bID'];
$h->cID = $_GET['cID'];

$id = ($_POST['id']) ? $_POST['id'] : $_GET['id'];

switch($_GET['mode']) {
    case 'getContent':
        json_encode($h->getNewsInfo());
        break;
    case 'getNewsPreview':
        $h->getNewsPreview();
        break;
    case 'addNewsRecord':
        $h->addNewsRecord($_POST['Title'], $_POST['Content']);
        break;
    case 'removeNews':
        $h->removeNews($id);
        break;
    case 'commitEditNews':
        $h->editNews($id, $_POST['Title'], $_POST['Content']);
        break;
    case 'getNewsInfo':
        echo json_encode($h->getNewsInfoID($id));
        break;
    case 'refreshOrder':
        $h->reorderNews($_POST['ids'], $_POST['order']);
        break;
}