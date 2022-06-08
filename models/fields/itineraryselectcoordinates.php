<?php
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

class JFormFieldItinerarySelectCoordinates extends JFormField
{
	public $type = 'ItinerarySelectCoordinates';

	public function getInput()
	{
		// Initialize variables.
		$html = array();
		
		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$class		= ' class="modal"';
		$readonly	= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		
		$maptype	= ( (string)$this->element['maptype'] ? $this->element['maptype'] : '' );
		
		if ($this->id == 'jform_latitude')
		{
		
			// Add the script to the document head.
			$script='	
				function createWindow()
				{
					var latValue = document.getElementById("jform_latitude").value;
					var lngValue = document.getElementById("jform_longitude").value;
					
					var link = "index.php?option=com_itinerary&view=itinerarygmap&tmpl=default&field=jform_latitude&zoom=18&type=map&lat="+latValue+"&lng="+lngValue;
					
					var myWindow = window.open(link,"'.JText::_('COM_ITINERARY_SELECT_COORDINATES_WINDOW').'","width=1100,height=900");
				}';
		
			JFactory::getDocument()->addScriptDeclaration($script);
		}
		
		
		$html[] = '<div class="center">';
		$html[] = '<input type="hidden" id="'.$this->id.'" name="'.$this->name.'" value="'. $this->value.'"' .
					' '.$class.$size.$disabled.$readonly.$maxLength.' />';
			

			$label = JText::_('COM_ITINERARY_FORM_SELECT_COORDINATES');
			$btntype = 'btn-danger';
			
			if(!empty($this->value))
			{
				$label = JText::_('COM_ITINERARY_FORM_COORDINATES_SELECTED');
				$btntype = 'btn-success';
			}
			
			$html[] = '<input type="button" id="'.$this->id.'_btn" class="modal_'.$this->id.' btn '.$btntype.' span6" value="'.$label.'" onclick="createWindow()"/>';

			
		$html[] = '</div>'. "\n";

		return implode("\n", $html);
	}
}