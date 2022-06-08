<?php
defined('_JEXEC') or die;
class ItineraryViewItinerary extends JViewLegacy
{
	/**
	* Display the Itinerary View
	*
	* @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	*
	* @return  void
	*/
	 
	protected $item;
	protected $form;
	

	 // Overwriting JView display method
	public function display($tpl = null)
	{		
		// Get some data from the models
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		
		$this->item->illustrations = "images" . DS . "com_itinerary" . DS . $this->item->illustrations; 
		
		$document = JFactory::getDocument();
		$document -> addScriptDeclaration("
			jQuery( document ).ready(function( $ ) {
					
				$(document).ready(function() {
					var text_max = 4000;
					var text_current = text_max - $('#jform_description').val().length;
					
					var comment = ' ".JText::_('COM_ITINERARY_FIELD_CHARACTERS_REMAINING')."';
					
					$('#textarea_feedback').html(text_current + comment);

					$('#jform_description').keyup(function() {
						var text_length = $('#jform_description').val().length;
						var text_remaining = text_max - text_length;

						$('#textarea_feedback').html(text_remaining + comment);
					});
				});

			});
		");
		
		 // Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			//JError::raiseError(500, implode("\n", $errors));
			throw new Exception(implode("\n", $errors),500);
			return false;
		}
		
		// Display the view
		parent::display($tpl);
	}
}