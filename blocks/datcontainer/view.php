<?php 
//defined('C5_EXECUTE') or die(_("Access Denied."));
$imageHelper = Loader::helper('image');

include_once('tools/functions.php');
?>

<div class="info-container" block-id="<?php echo $bID ?>">

	<script type="text/javascript">
		$(document).bind(
			'ready',
			function() {
				jScrollHandler.initialize($('.info-container[block-id=<?php echo $bID ?>]'));
				jMenu.set($('.info-container[block-id=<?php echo $bID ?>] .info-container-menu'))
			}
		);
	</script>

	<div class="info-container-menu">
		<?php buildMenu($this->block->getAreaHandle()) ?>
	</div>
	<div class="info-container-wrapper content-wrapper">

		<!--style="background-image: url(<?php //getBackgroundURL($photoFID, $imageHelper) ?>)"-->
		<div class="info-container-bg-w">
			<img class="info-container-bg" src="<?php getImageURL($masterPhotoFID, $imageHelper) ?>"/>
		</div>

		<div class="info-head">
			<p class="title"><?=$Title?></p>
			<p class="mentor"><?=$Mentor?></p>
		</div>
		<div class="content">
			<div class="photo-container"></div>
			<div class="instructor-info">
				<!--<div class="i-photo" style="background-image: url()"></div>-->
				<div class="i-contacts">
					<?php
					if (isset($socialVK) && !empty($socialVK)) {
						?>
						<a href="http://vk.com/<?=$socialVK?>" class="social-vk"></a>
						<?php
					}
					?>
				</div>
			</div>
			<div class="info"><p><?php echo $Description ?></p></div>
		</div>
		<div class="button-container">
			<?php if ($aboutTitle) { ?><div class="button" target-container="about" onclick="jSwitch.switch(this)"><?php echo $aboutTitle ?></div> <?php } ?>
			<?php if (count($staff) > 0) { ?><div class="button" target-container="people" onclick="jSwitch.switch(this)"><?=$StaffButton?></div><?php } ?>
			<div class="button active" target-container="schedule" onclick="jSwitch.switch(this)">Расписание</div>
			<div class="button" target-container="media" onclick="jSwitch.switch(this)">Фото и видео</div>
		</div>

		<div class="info-container-switch-wrapper">
			<?php if (count($staff) > 0) {
				?>
				<div class="info-container-switch people" style="display: none;">
					<div jslider-slides="3" class="slider-container slide-line slider-dynamic">
						<div class="slider-buttons arrow-left" style="background-image: url(<?=$view->getThemePath()?>/images/arrow-left.png)"></div>
						<div class="slider-buttons arrow-right" style="background-image: url(<?=$view->getThemePath()?>/images/arrow-right.png)"></div>
						<div class="slider-wrapper">
						<?php
						foreach($staff as $member) {
							?>
							<div class="member-container slide">
								<div class="member-photo" style="background-image: url(<?php echo getImageURL($member['photoFID'], $imageHelper) ?>);"></div>
								<div class="member-name"><?php echo $member['MemberName'] ?></div>
								<div class="member-description slider-inherit-width"><?php echo stripslashes($member['MemberDescription']) ?></div>
							</div>
							<?php
						}
						?>
						</div>
					</div>
				</div>
				<?php
			} ?>

			<?php if ($aboutTitle) {
				?>
				<div class="info-container-switch about" style="display: none;">
					<div class="about-container card-container">
						<?php echo $aboutText ?>
					</div>
				</div>
				<?php
			} ?>

			<?php if (isset($events) && count($events) > 0) { ?>
				<div class="info-container-switch schedule">
					<table cellspacing="0" cellpadding="0">
						<tbody>
							<tr>
								<td></td>
								<td>Понедельник</td>
								<td>Вторник</td>
								<td>Среда</td>
								<td>Четверг</td>
								<td>Пятница</td>
								<td>Суббота</td>
								<td>Воскресенье</td>
							</tr>
							<?php

							$builder = new SchedulerBuilder(2);
							$t = $builder->getLineCount($events);

							for ($line = 0; $line < $t['Lines']; $line++) {
								$hourLine = $line / $builder->linePerHour;

								$hour = $t['Min'] + $hourLine;
								$even = (floor($hourLine) % 2 === 0) ? true : false; ?>

								<tr class="<?php echo $even ? 'even' : 'odd' ?>">
									<?php if($line % $builder->linePerHour === 0) { ?>
										<td class="time" rowspan="<?php echo $builder->linePerHour ?>"><?php echo $hour . ':00' ?></td>
									<?php } ?>
									<?php for ($d = 0; $d < 7; $d++) {
										$e = $builder->getDayEvent($events, $d, $hour);
										if ($e !== null) { ?>
											<td class="schedule-event" rowspan="<?php echo $builder->getRowSpan($e) ?>">
												<p class="schedule-event-title"><?php echo $e['SchTitle'] ?></p>
												<p class="schedule-event-time"><?php echo $builder->formatTime($e['SchTStart']) . ' - ' . $builder->formatTime($e['SchTEnd']) ?></p>

												<div class="schedule-event-description"><?php echo $e['SchDescription'] ?></div>
											</td>
										<?php } else if($builder->needRow($events, $d, $hour)) { //$t['Min'] + $i ?>
											<td></td>
										<?php }
									} ?>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } ?>

			<div class="info-container-switch media" style="display: none;">
				<?php if(count($mediaEntries) > 0) { ?>

					<div class="slider-container slide-line">
						<div class="slider-buttons arrow-left" style="background-image: url(<?=$view->getThemePath()?>/images/arrow-left.png)"></div>
						<div class="slider-buttons arrow-right" style="background-image: url(<?=$view->getThemePath()?>/images/arrow-right.png)"></div>
						<div class="slider-wrapper">
							<!-- SLIDER DIVs -->
							<!--<img class="slide" src="http://localhost/concrete/application/themes/DAT/images/header_slide_01.jpg"/>
							<img class="slide" src="http://localhost/concrete/application/themes/DAT/images/header_slide_02.jpg"/>
							<img class="slide" src="http://localhost/concrete/application/themes/DAT/images/header_slide_01.jpg"/>-->
							<?php foreach($mediaEntries as $media) {
								if ($media['isVideo'] == 1) {
									?>
									<iframe id="ytplayer"
											class="slide"
											type="text/html"
											width="800" height="450"
											src="https://www.youtube.com/embed/<?php echo getYoutubeEmbUrl($media['ytLink']) ?>?rel=0&showinfo=0&color=blue&iv_load_policy=3"
											frameborder="0"
											style="width: 800px;"
											allowfullscreen></iframe>
									<?php
								} else {
									?>
									<img class="slide" src="<?php echo getImageThumb($media['fID'], $imageHelper, 800, 450) ?>"/>
									<?php
								}
							}?>
						</div>
					</div>

				<?php } ?>
			</div>

		</div>
	</div>
	<div class="info-container-parallax">
		<div class="parallax-line-path">
			<span class="parallax-line"></span>
		</div>
	</div>
</div>