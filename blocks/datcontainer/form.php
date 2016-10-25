<?php

//defined('C5_EXECUTE') or die(_("Access Denied."));

$fp = FilePermissions::getGlobal();
$tp = new TaskPermission();

function escapeNewLine($string) {
	return str_replace(array("\r\n", "\n", "\r"), ' ', $string);
}

?>

<style>
	.pick-image {
		padding: 20px;
		margin-top: 2px;
		margin-bottom: 2px;
		cursor: pointer;
		text-align: center;
		vertical-align: middle;
		
		background-color: rgba(0, 0, 0, 0.05);
	}
	
	.pick-image img {
		max-width: 100%;
	}
	
	.dat-entry-container {
		margin-top: 2px;
	}

	.col-clear {
		clear: both;
	}
</style>

<script>
	var CCM_EDITOR_SECURITY_TOKEN = "<?php echo Loader::helper('validation/token')->generate('editor') ?>";
	var attachFileManagerLaunch = function($obj) {
		$obj.unbind().click(function(){
			var oldLauncher = $(this);
			ConcreteFileManager.launchDialog(function (data) {
				ConcreteFileManager.getFileDetails(data.fID, function(r) {
					jQuery.fn.dialog.hideLoader();
					var file = r.files[0];
					oldLauncher.html(file.resultsThumbnailImg);
					oldLauncher.next('.image-fID').val(file.fID)
				});
			});
		});
	}
	
	var attachDelete = function($obj) {
		$obj.click(function(){
			$(this).closest('.dat-entry-container').remove();
		});
	}
	
	var countEntries = function(v, container) {
		$('input[name="' + v + '"]').val($('.' + container + ' .dat-entry-container').size());
	}
	
	$(document).ready(function() {
		var entriesContainer = $('.dat-media-entries-container');
		var eventsContainer = $('.dat-event-container');
		var staffContainer = $('.dat-staff-container');

		var entryTemplate = _.template($('#addMediaTemplate').html());
		var eventTemplate = _.template($('#addEventTemplate').html());
		var staffTemplate = _.template($('#addStaffTemplate').html());

		var prepareRedactor = function($obj) {
			$obj.redactor({
				minHeight: '100',
				'concrete5': {
					filemanager: <?php echo $fp->canAccessFileManager() ?>,
					sitemap: <?php echo $tp->canAccessSitemap() ?>,
					lightbox: true
				}
			});
		};
		var prepareSwitchers = function($obj) {
			$obj.find('.form-select').each(function() {
				$(this).change(function() {
					var container = $(this).closest('.dat-entry-container');
					
					container.find('.select-group').each(function() {
						$(this).hide();
					});
					
					container.find('.' + $(this).find('option[value='+ $(this).val() +']').attr('switch-target')).show();
				});
				
				$(this).trigger('change');
			});
		};
		
		//LOAD EXISTING ITEMS (TODO: AJAX CALLS)
		<?php
		if ($staff) {
			foreach($staff as $member) {
				?>
		staffContainer.append(staffTemplate({
			MemberName: '<?php echo $member['MemberName'] ?>',
			MemberDescription: '<?php echo escapeNewLine($member['MemberDescription']) ?>',
			imageUrl: '<?php if ($member['photoFID'] > 0) {
			 	if ($mediaImg = File::getByID($member['photoFID'])) {
			 		echo $mediaImg->getThumbnailURL('file_manager_listing');
			 	}
			}?>',
			photoFID: '<?php echo $member['photoFID'] ?>'
		}));

		attachDelete(staffContainer.find('.dat-entry-staff').last().find('.dat-delete-entry'));
		attachFileManagerLaunch(staffContainer.find('.pick-image'));
				<?php
			}
		}?>

		<?php 
			if ($events) {
				foreach($events as $event) {
					$TStart = new DateTime($event['SchTStart']);
					$TEnd = new DateTime($event['SchTEnd']);
		?>
		
		eventsContainer.append(eventTemplate({
			TStart: '<?php echo $TStart->format('H:i') ?>',
			TEnd: '<?php echo $TEnd->format('H:i') ?>',
			Title: '<?php echo $event['SchTitle'] ?>',
			Description: '<?php echo str_replace(array("\t", "\r", "\n"), "", addslashes(h($event['SchDescription']))) ?>',
			SchDay: '<?php echo $event['SchDay'] ?>'
		}));

		attachDelete(eventsContainer.find('.dat-entry-event').last().find('.dat-delete-entry'));
		
				<?php }
			}
			if ($mediaEntries) {
				foreach($mediaEntries as $media) { ?>

					entriesContainer.append(entryTemplate({
						fID: '<?php echo $media['fID'] ?>',
						ytLink: '<?php echo $media['ytLink'] ?>',
						isVideo: '<?php echo $media['isVideo'] ?>',
						title: '',
						description: '',
						imageUrl: '<?php
						 if ($media['fID'] > 0) {
						 	if ($mediaImg = File::getByID($media['fID'])) {
						 		echo $mediaImg->getThumbnailURL('file_manager_listing');
						 	}
						 }
						?>',
						linkUrl: ''
					}));

					attachDelete(entriesContainer.find('.dat-entry-media').last().find('.dat-delete-entry'));
					prepareSwitchers(entriesContainer.find('.dat-entry-media').last());

				<?php }
			}
		?>
		countEntries('SchItemsCount', 'dat-event-container');
		
		attachFileManagerLaunch($('#MainPhoto'));
		
		$('#DatAddEntry').click(function() {
			entriesContainer.append(entryTemplate({
				//parameters
				fID: '',
				title: '',
				isVideo: 0,
				description: '',
				ytLink: '',
				imageUrl: ''
			}));
			
			var newContainer = $('.dat-entry-media').last();
			prepareRedactor(newContainer.find('.redactor-content'));
			prepareSwitchers(newContainer);
			
			attachFileManagerLaunch(newContainer.find('.pick-image'));
			attachDelete(newContainer.find('.dat-delete-entry'));
		});
		
		$('#DatAddEvent').click(function() {
			eventsContainer.append(eventTemplate({
				//params
				TStart: '00:00',
				SchDay: 0,
				TEnd: '00:00',
				Title: '',
				Description: ''
			}));
			
			countEntries('SchItemsCount', 'dat-event-container');
			
			var newContainerE = $('.dat-entry-event').last();
			prepareRedactor(newContainerE.find('.redactor-content'));
			attachDelete(newContainerE.find('.dat-delete-entry'));
		});

		$('#DatAddMember').click(function() {
			staffContainer.append(staffTemplate({
				MemberName: '',
				MemberDescription: '',
				imageUrl: '',
				photoFID: '0'
			}));

			var newContainerM = $('.dat-entry-staff').last();
			prepareRedactor(newContainerM.find('.redactor-content'));
			attachDelete(newContainerM.find('.dat-delete-entry'));
		});
		
		$(function() {
			prepareRedactor($('.redactor-content'));
		});
	});

</script>

<?php 
	print Loader::helper('concrete/ui')->tabs(array(
		array('main-t', 'Главные', true),
		array('schedule-t', 'Расписание'),
		array('media-t', 'Медиа'),
		array('staff-t', 'Состав')
	));
?>

<div class="ccm-tab-content" id="ccm-tab-content-main-t">
	<fieldset>
		<legend>Главные настройки</legend>



		<div class="col-xs-6">
			<div class="form-group">
				<label><?php echo t('Image') ?></label>
				<div class="pick-image" id="MainPhoto">
					<?php if ($masterPhotoFID > 0) {
					if ($pFile = File::getByID($masterPhotoFID)) { ?>
					<img src="<?php echo $pFile->getThumbnailURL('file_manager_listing'); ?>" />
					<?php } else { ?>
					<i class="fa fa-picture-o"></i>
					<?php } } ?>
				</div>
				<input type="hidden" name="masterPhotoFID" class="image-fID" value="<?php echo $masterPhotoFID ?>" />
			</div>
		</div>

		<div class="col-xs-6">
			<div class="form-group">
				<label class="control-label" for="Title"><?php echo t('Title') ?></label>
				<input class="form-control" type="text" name="Title" value="<?php echo $Title ?>">
			</div>
			<div class="form-group">
				<label class="control-label" for="Mentor"><?php echo t('Mentor') ?></label>
				<input class="form-control" type="text" name="Mentor" value="<?php echo $Mentor ?>">
			</div>
			<div class="form-group">
				<label>VK</label>
				<div style="display: flex; align-items: center">
					<p style="margin: 0;">http://vk.com/</p>
					<input class="form-control" type="text" name="socialVK" value="<?=$socialVK?>">
				</div>

			</div>
		</div>

		<div class="col-clear"></div>

		<div class="form-group">
			<div class="form-group">
				<label class="control-label" for="Title"><?php echo t('Description') ?></label>
				<div class="redactor-edit-content"></div>
				<textarea style="display:none" class="redactor-content" name="Description"><?php echo $Description ?></textarea>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label"><?php echo t('About') ?></label>
			<input class="form-control" type="text" name="aboutTitle" value="<?php echo $aboutTitle ?>">
		</div>
		<div class="form-group">
			<label class="control-label"><?php echo t('Text') ?></label>
			<div class="redactor-edit-content"></div>
			<textarea style="display: none;" class="redactor-content" name="aboutText"><?php echo $aboutText ?></textarea>
		</div>
	</fieldset>
</div>

<div class="ccm-tab-content" id="ccm-tab-content-media-t">
	<fieldset>
		<legend>Медиа</legend>
		
		<div class="form-group">
			<span class="btn btn-success add-image-entry" id="DatAddEntry"><?php echo t('Add Entry') ?></span>
		</div>
		
		<div class="dat-media-entries-container"></div>
		
	</fieldset>
</div>

<div class="ccm-tab-content" id="ccm-tab-content-schedule-t">
	<fieldset>
		<legend>Расписание</legend>
		
		<input type="hidden" name="SchItemsCount"/>
		<div class="form-group">
			<span class="btn btn-success add-event-entry" id="DatAddEvent"><?php echo t('Add Entry') ?></span>
		</div>
		
		<div class="dat-event-container"></div>
		
	</fieldset>
</div>

<div class="ccm-tab-content" id="ccm-tab-content-staff-t">
	<fieldset>
		<legend>Состав</legend>

		<div class="form-group">
			<label>Текст кнопки</label>
			<input type="text" name="StaffButton" value="<?=htmlentities($StaffButton)?>">
		</div>

		<div class="form-group">
			<span class="btn btn-success add-event-entry" id="DatAddMember"><?php echo t('Add Entry') ?></span>
		</div>

		<div class="dat-staff-container"></div>
	</fieldset>
</div>

<!-- ENTRY TEMPLATE, ECHO USED TO KEEP HTML CODE CHECK SUPPORT -->
<?php echo '<script type="text/template" id="addMediaTemplate">' ?>

	<div class="dat-entry-container dat-entry-media well col-xs-6">
		
		<div class="form-group">
			<select class="form-control form-select" name="<?php echo $view->field('isVideo') ?>[]">
				<option switch-target="dat-picture" value="0" <% if (isVideo == 0) { %> selected  <% } %>>Image</option>
				<option switch-target="dat-video" value="1" <% if (isVideo == 1) { %> selected  <% } %>>Video</option>
			</select>
		</div>
		
		<div class="form-group dat-picture select-group">
			<label><?php echo t('Image') ?></label>
			<div class="pick-image">
				<% if (imageUrl.length > 0) { %>
					<img src="<%= imageUrl %>" />
				<% } else { %>
					<i class="fa fa-picture-o"></i>
				<% } %>
			</div>
			<input type="hidden" name="<?php echo $view->field('fID')?>[]" class="image-fID" value="<%=fID%>" />
		</div>
					
		<div class="form-group dat-video select-group">
			<label><?php echo t('Youtube') ?></label>
			<input class="form-control" type="text" name="<?php echo $view->field('ytLink')?>[]" value="<% if(isVideo == 1) { %> <%=ytLink%> <% } %>"/>
		</div>
					
		<div class="form-group">
			<span class="btn btn-danger dat-delete-entry"><?php echo t('Delete Entry'); ?></span>
		</div>
					
	</div>
				
<?php echo '</script>' ?>
				
<!-- EVENT TEMPLATE -->
<?php echo '<script type="text/template" id="addEventTemplate">' ?>
	
	<div class="dat-entry-container dat-entry-event well col-xs-6">

		<div class="form-group">
			<select class="form-control" name="<?php echo $view->field('SchDay'); ?>[]">
				<option value="0" <% if(SchDay == 0) { %> selected <% } %>>Понедельник</option>
				<option value="1" <% if(SchDay == 1) { %> selected <% } %>>Вторник</option>
				<option value="2" <% if(SchDay == 2) { %> selected <% } %>>Среда</option>
				<option value="3" <% if(SchDay == 3) { %> selected <% } %>>Четверг</option>
				<option value="4" <% if(SchDay == 4) { %> selected <% } %>>Пятница</option>
				<option value="5" <% if(SchDay == 5) { %> selected <% } %>>Суббота</option>
				<option value="6" <% if(SchDay == 6) { %> selected <% } %>>Воскресение</option>
			</select>
		</div>

		<div class="col-xs-6">
			<div class="form-group">
				<label>Начало</label>
				<input name="<?php echo $view->field('SchTStart'); ?>[]" type="time" class="form-control" value="<%=TStart%>" />
			</div>
		</div>
		
		<div class="col-xs-6">
			<div class="form-group">
				<label>Окончание</label>
				<input name="<?php echo $view->field('SchTEnd'); ?>[]" type="time" class="form-control" value="<%=TEnd%>" />
			</div>
		</div>
		
		<div class="form-group">
			<label>Название</label>
			<input name="<?php echo $view->field('SchTitle'); ?>[]" type="text" class="form-control" value="<%=Title%>" />
		</div>
		
		<div class="form-group">
			<label>Описание</label>
			<div class="redactor-edit-content"></div>
			<textarea style="display:none" class="redactor-content" name="<?php echo $view->field('SchDescription'); ?>[]"><%=Description%></textarea>
		</div>
		
		<div class="form-group">
			<span class="btn btn-danger dat-delete-entry"><?php echo t('Delete Entry'); ?></span>
		</div>
		
	</div>
				
<?php echo '</script>' ?>

<!--STAFF TEMPLATE-->
<?php echo '<script type="text/template" id="addStaffTemplate">' ?>

	<div class="dat-entry-container dat-entry-staff well col-xs-6">

		<div class="focm-group">
			<label>Фотография</label>
			<div class="pick-image">
				<% if (imageUrl.length > 0) { %>
				<img src="<%= imageUrl %>" />
				<% } else { %>
				<i class="fa fa-picture-o"></i>
				<% } %>
			</div>
			<input type="hidden" name="<?php echo $view->field('photoFID')?>[]" class="image-fID" value="<%=photoFID%>" />
		</div>

		<div class="form-group">
			<label>Имя</label>
			<input name="<?php echo $view->field('MemberName') ?>[]" type="text" class="form-control" value="<%=MemberName%>"/ >
		</div>

		<div class="form-group">
			<label>Описание</label>
			<textarea style="display: none;" class="redactor-content" name="<?php echo $view->field('MemberDescription') ?>[]"><%=MemberDescription%></textarea>
		</div>

		<div class="form-group">
			<span class="btn btn-danger dat-delete-entry"><?php echo t('Delete Entry') ?></span>
		</div>

	</div>

<?php echo '</script>' ?>
