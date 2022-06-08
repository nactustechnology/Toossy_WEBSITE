<!--view edit message-->
<?php
defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');

$clef_message = (int) $this->item->clef;
?>
	<form action="<?php echo JRoute::_('index.php?option=com_itinerary&view=message&layout=edit&clef='.$clef_message); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
		
		<legend class="legend"><?php echo JText::_("COM_ITINERARY_FIELD_INFORMATIONS_MESSAGE_LABEL");?> :</legend>		
		
		<div class="btn-toolbar">
			<div class="btn-group">
				<?php
				if(!empty($clef_message))
				{
					echo '<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'message.cancel\')">
						<i class="icon-arrow-left"></i>'.JText::_('COM_ITINERARY_RETOUR_MESSAGES_LABEL').'
					</button>
                                        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'message.apply\')">
                                                <i class="icon-apply"></i>'.JText::_('JSAVE').'
                                        </button>

                                        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'message.save\')">
                                                <i class="icon-save"></i>'.JText::_('COM_ITINERARY_BUTTON_SAVE_AND_CLOSE').' 
                                        </button>

                                        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'message.save2new\')" >
                                                <i class="icon-save-new"></i>'.JText::_('COM_ITINERARY_BUTTON_SAUVEGARDER_AJOUTER_MESSAGE_LABEL').'
                                        </button>';
				}
                                else
				{
                                    echo '<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'message.save\')">
                                                <i class="icon-save"></i>'.JText::_('COM_ITINERARY_BUTTON_SAVE_AND_CONTINUE').' 
                                        </button>

                                        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'message.save2new\')" >
                                                <i class="icon-save-new"></i>'.JText::_('COM_ITINERARY_BUTTON_SAUVEGARDER_AJOUTER_MESSAGE_LABEL').'
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'message.cancel\')">
						<i class="icon-cancel"></i>'.JText::_('JCANCEL').'
					</button>';
                                }   ?>		
			</div>
		</div>
		
		<?php 
		$fieldsetForm = $this->form->getFieldset('myfields');
		echo $fieldsetForm['jform_clef']->renderField();
		echo $fieldsetForm['jform_clef_planificateur']->renderField();
		echo $fieldsetForm['jform_clef_parcours']->renderField();
		echo $fieldsetForm['jform_longitude']->renderField();
		echo $fieldsetForm['jform_latitude_msg_suiv']->renderField();
		echo $fieldsetForm['jform_longitude_msg_suiv']->renderField();
		echo $fieldsetForm['jform_date_creation']->renderField();
		echo $fieldsetForm['jform_date_modification']->renderField();
		echo $fieldsetForm['jform_ordering']->renderField();
		?>
		
		
		<div class="container-fluid parcoursblock">
		<?php
		echo '<div class="row voffset4 '.$this->hiddenClass.'">'; ?>
				<div class="span6 form-inline">
				</div>
				<div class="span6 text-right form-inline">
					<?php 
						echo $fieldsetForm['jform_activation_planificateur']->label; 
						echo $fieldsetForm['jform_activation_planificateur']->input;
					?>
				</div>
			</div>
			
			<div class="clearfix"></div>
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
				<div id="parcoursImage" class="controls">
					<?php $illustrations = $this->item->illustrations;
					
						if($illustrations=="images/com_itinerary/")
						{
							echo '<div class="text-center">'.JText::_('COM_ITINERARY_FIELD_NO_ASSOCIATED_IMAGE').'</div>';
						}
						else
						{
							echo '<img src="'.$illustrations.'" class="img-responsive center-block" alt="no image"/>';
							echo '<div class="text-center">'.htmlspecialchars_decode($this->item->titre_illustrations,ENT_QUOTES).'</div>';
							echo '<div class="text-center"><button type="submit" name="supprimerImage" class="btn btn-warning" onclick="Joomla.submitbutton(\'message.deleteimage\')"><i class="icon-cancel"></i>'.JText::_('COM_ITINERARY_IMAGE_DELETE').'</button></div>';
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
						$fieldsetForm['jform_texte']->value=htmlspecialchars_decode($fieldsetForm['jform_texte']->value,ENT_QUOTES);
						echo $fieldsetForm['jform_texte']->renderField();
					?>
			</div>
			<div class="row">
				<div class="span2"></div><div class="span9" id="textarea_feedback"></div>
			</div>
			
			<div class="row voffset4">
					<?php
						echo $fieldsetForm['jform_latitude']->renderField();
					?>
			</div>		
			
			<input type="hidden" name="task" value="" />
			
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
