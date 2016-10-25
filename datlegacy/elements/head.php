<head>
    <?=Loader::element('header_required');?>

    <link href='https://fonts.googleapis.com/css?family=Roboto:300,700,500&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Roboto+Condensed:400,300&subset=latin,cyrillic' rel='stylesheet' type='text/css'>

    <?php $this->addHeaderItem(Loader::helper('html')->javascript('jquery.js')) ?>
    <?php $this->addHeaderItem(Loader::helper('html')->javascript('jquery-form.js')) ?>
    <?php $this->addHeaderItem(Loader::helper('html')->javascript('jquery-ui.js')) ?>

    <link href="<?=$view->getThemePath()?>/css/animations.css" rel="stylesheet" type="text/css">
    <link href="<?=$view->getThemePath()?>/css/template.css" rel="stylesheet" type="text/css">
    <link href="<?=$view->getThemePath()?>/css/social.css" rel="stylesheet" type="text/css">
    <link href="<?=$view->getThemePath()?>/css/uielements.css" rel="stylesheet" type="text/css">

    <script src="<?=$view->getThemePath()?>/js/jquery.waypoints.js"></script>
    <script src="<?=$view->getThemePath()?>/js/jquery.animateSprite.min.js"></script>

    <script src="<?=$view->getThemePath()?>/js/jStick.js"></script>
    <script src="<?=$view->getThemePath()?>/js/jScrollTo.js"></script>
    <script src="<?=$view->getThemePath()?>/js/jMenu.js"></script>
    <script src="<?=$view->getThemePath()?>/js/jSwitch.js"></script>
    <script src="<?=$view->getThemePath()?>/js/jSliders.js"></script>
    <script src="<?=$view->getThemePath()?>/js/jDebug.js"></script>
    <script src="<?=$view->getThemePath()?>/js/jCaptcha.js"></script>

    <?php \Application\Controller\Dattools::getScript(); ?>

    <script src="<?=$view->getThemePath()?>/js/basket.min.js"></script>
</head>