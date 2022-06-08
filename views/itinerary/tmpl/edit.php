<!--view edit itinerary-->
<?php
defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');

$clef_parcours = (int) $this->item->clef;
?>
<!--<input type="submit" value="" />-->
	<form action="<?php echo JRoute::_('index.php?option=com_itinerary&view=itinerary&layout=edit&clef='.$clef_parcours); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

		<legend class="legend"><?php echo JText::_("COM_ITINERARY_FIELD_INFORMATIONS_PARCOURS_LABEL");?> :</legend>
		
		<div class="row btn-toolbar">
			<div class="btn-group span4">
				<?php
				if(!empty($clef_parcours))
				{
					echo '<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'message.cancel\')">
						<i class="icon-arrow-left"></i>'.JText::_('COM_ITINERARY_RETOUR_LABEL').'
					</button>
                                        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'itinerary.apply\')">
                                                <i class="icon-apply"></i>'.JText::_('JSAVE').'
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'itinerary.itineraryApplyCloseToMessages\')">
						<i class="icon-save"></i>'.JText::_('COM_ITINERARY_BUTTON_SAVE_AND_CLOSE').'
					</button>';
				}
				else
				{
					echo '<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'itinerary.save\')">
						<i class="icon-save"></i>'.JText::_('COM_ITINERARY_BUTTON_SAVE_AND_CONTINUE').'
					</button>
                                        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'itinerary.cancel\')">
						<i class="icon-cancel"></i>'.JText::_('JCANCEL').'
					</button>';
				}
				?>
			</div>                        
		</div>
		
		<?php
			$fieldsetForm = $this->form->getFieldset('myfields');
			echo $fieldsetForm['jform_clef']->renderField();
			echo $fieldsetForm['jform_clef_planificateur']->renderField();
			echo $fieldsetForm['jform_latitude']->renderField();
			echo $fieldsetForm['jform_longitude']->renderField();
			echo $fieldsetForm['jform_date_creation']->renderField();
			echo $fieldsetForm['jform_date_modification']->renderField();
		?>
		
		<div class="form-horizontal parcoursblock">
			<?php
				$hiddenClass ='hidden';
				
				if($fieldsetForm['jform_clef']->value)
				{
					$hiddenClass ='';
				}
				echo '<div class="row '.$hiddenClass.' voffset4">
						<div class="control-group">
							<div class="control-label">
								<label>'.JText::_('COM_ITINERARY_FIELD_PARCOURS_ID_LABEL').'</label>
							</div>
							<div class="controls">
								<input readonly value="'.(4*(int)$fieldsetForm['jform_clef']->value+426957183).'"/>
							</div>
						</div>
					</div>';
				
				echo '<div class="row '.$hiddenClass.' voffset4">';
					echo $fieldsetForm['jform_activation_planificateur']->renderField();
				echo '</div>';
			?>
			
			<div class="row voffset4">
				<?php
					echo $fieldsetForm['jform_telechargeable']->renderField();
				?>
			</div>
			
			<div class="row voffset4">
				<?php
					echo $fieldsetForm['jform_type_parcours']->renderField();
				?>
			</div>
			
			<div class="row voffset4">
				<?php
					$fieldsetForm['jform_titre']->value=htmlspecialchars_decode($fieldsetForm['jform_titre']->value,ENT_QUOTES);
					echo $fieldsetForm['jform_titre']->renderField();
				?>
			</div>
			
			<div class="row  voffset4 control-group">
				<div class="control-label">
					<label><?php echo JText::_('COM_ITINERARY_FIELD_ASSOCIATED_IMAGE'); ?></label>
				</div>
				<div class="controls">
					<?php $illustrations = $this->item->illustrations;
					
						if($illustrations=="images/com_itinerary/")
						{
							echo '<div class="text-center">'.JText::_('COM_ITINERARY_FIELD_NO_ASSOCIATED_IMAGE').'</div>';
						}
						else
						{
							echo '<img src="'.$illustrations.'" class="img-responsive center-block" alt="'.$illustrations.'"/>';
							echo '<div class="text-center">'.htmlspecialchars_decode($this->item->titre_illustrations,ENT_QUOTES).'</div>';
							echo '<div class="text-center"><button type="submit" name="supprimerImage" class="btn btn-warning" onclick="Joomla.submitbutton(\'itinerary.deleteimage\')"><i class="icon-cancel"></i>'.JText::_('COM_ITINERARY_IMAGE_DELETE').'</button></div>';
						}
					?>
				</div>
			</div>
			
			<div class="row voffset4">
				<?php
					echo $fieldsetForm['jform_illustrations']->renderField();
				?>
			</div>
			
			
			
			<div class="row voffset4">
				<?php
					$fieldsetForm['jform_description']->value=htmlspecialchars_decode($fieldsetForm['jform_description']->value,ENT_QUOTES);
					echo $fieldsetForm['jform_description']->renderField();
				?>
			</div>
			<div class="row">
				<div class="span2"></div><div class="span9" id="textarea_feedback"></div>
			</div>
			
			<div class="row voffset4">
				<?php
					echo $fieldsetForm['jform_langue']->renderField();
				?>
			</div>
			
			<div class="row voffset4">
				<?php
					echo $fieldsetForm['jform_theme']->renderField();
				?>
			</div>
			
			<div class="row voffset4">
				<?php
					echo $fieldsetForm['jform_duree']->renderField();
				?>
			</div>
			
			<!--<div class="row voffset4">-->
                        <div class="hidden">    
				<div class="span6 form-inline">
				<?php
					echo $fieldsetForm['jform_payant']->renderField();
				?>

				<?php
					echo $fieldsetForm['jform_tarif']->renderField();
				?>
				</div>
			</div>
				
			<input type="hidden" name="task" value="" />
			
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>