<?php
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

class JFormFieldItinerarySelectImage extends JFormField
{
	public $type = 'ItinerarySelectImage';

	public function getInput()
	{
		// Initialize variables.
		$html = array();
		
		// Initialize some field attributes.
			$html= 	'<div class="btn-group image-preview">
						
							<!-- image-preview-clear button -->
							<button class="btn btn-default image-preview-clear" type="button" style="display:none;">
								<span class="glyphicon glyphicon-remove"><i class="icon-remove"></i></span>'.JText::_('JACTION_DELETE').'
							</button>
							
							<!-- image-preview-input -->
							<div class="btn btn-default image-preview-input">
								<span class="glyphicon glyphicon-folder-open"><i class="icon-folder-open"></i></span>
								<span class="image-preview-input-title">'.JText::_('COM_ITINERARY_IMAGE_SELECT').'</span>
								<input type="file" accept="image/png, image/jpeg, image/jpg" id="jform_illustrations"  name="jform[illustrations]"/>
							</div>
						
                                                <input type="hidden" name="MAX_FILE_SIZE" value="610000">
						<input type="text" class="form-control image-preview-filename" disabled="disabled">
					</div>';
	
		return $html;
		
		//<span class="input-group-btn"></span>
	}
}