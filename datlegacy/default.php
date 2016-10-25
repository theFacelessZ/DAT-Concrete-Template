<?php defined('C5_EXECUTE') or die("Access Denied.");?>
<html>
    <?php
        $this->inc('elements/head.php');
    ?>
    <body>

        <div class="<?=$c->getPageWrapperClass()?>">

            <?php
                $_maintenance = Config::get('concrete.maintenance_mode');
                if ($_maintenance == 1) {
                    echo '
                    <div id="preview-sign"
                    style="display: block;
                    position:fixed;
                    z-index: 1000;
                    width: 100%;
                    background-color: black;
                    color: white;
                    text-align: center;">
                        <p style="margin: 0;">Режим обслуживания</p>
                    </div>
                    ';
                }
                \Application\Controller\Multiarea::multiAreaSupport();

                $this->inc('elements/header.php');
                $uinfo = new User();
            ?>
            <?php
            /*if ($uinfo->IsLoggedIn()) {
                ?>
                <script type="text/javascript"> $(document).bind(
                        'ready',
                        function() {
                            //jScrollHandler.topOffset = 49
                        }
                    );
                </script>
                <?php
            }*/
            ?>
            <script type="text/javascript">
                $(document).ready(function() {
                    $('.info-container').each(function(i) {
                        $(this).css({'z-index': i});
                    });
                });

                $(document).bind(
                    'scroll',
                    function () {
                        var stt = $('#scroll-to-top-z');
                        var scrollY = $(document).scrollTop();
                        var triggerY = 300;

                        if (scrollY >= triggerY && !stt.hasClass('triggered')) {
                            stt.addClass('triggered');
                            /*stt.stop(true).fadeIn();*/
                        } else if (scrollY < triggerY  && stt.hasClass('triggered')) {
                            /*stt.stop(true).fadeOut();*/
                            stt.removeClass('triggered');
                        }
                    }
                )
            </script>

            <div id="scroll-to-top-z"
                 onclick="jScrollTo('body')"></div>

            <div id="offers-container">
                <?php
                    $a = new Area('OffersArea');
                    $a->display($c);
                ?>
            </div>

			<div id="main-container">
				<?php
					$a = new Area('MainArea');
                    /*$a->enableGridContainer();*/
					$a->display($c);
				?>
			</div>

            <?=Loader::element('footer_required');?>

            <footer class="filler">
                <div class="footer-wrapper">
                    <div class="footer-info">
                        <p>© DAT Dance School (Постнова Анастасия Сергеевна ИП), 2015</p>
                        <p>Свидетельство о регистрации №291322042 от 22.09.2014 Барановичский горисполком УНП 291322042</p>
                    </div>
                    <div class="footer-copyright">
                        <p>Designed with <span style="color: rgb(241, 72, 150);">❤</span> by <a href="mailto:anthony.lupow@gmail.com">Anthony Lupow</a></p>
                        <?php
                        if ($uinfo->IsLoggedIn()) {
                            ?>
                            <p>Привет, <?php echo $uinfo->getUserName() ?>.
                                <a href="<?php echo $this->url('/dashboard') ?>">Панель управления</a>
                                <a href="<?php echo URL::to('/login', 'logout', Loader::helper('validation/token')->generate('logout')) ?>">Выйти</a> </p>
                            <?php
                        } else {
                            ?>
                            <p><a href="<?php echo $this->url('/login') ?>">Вход</a></p>
                            <?php
                        }
                        ?>
                    </div>

                </div>
            </footer>
        </div>
    </body>
</html>
