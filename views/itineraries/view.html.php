<!--VIEW ITINERARIES-->
<?php
defined('_JEXEC') or die;

class ItineraryViewItineraries extends JViewLegacy
{
	protected $items;
	
	protected $state;

	protected $pagination;
	
	public function display($tpl = null)	
	{
		$this->items = $this->get('Items');
		$this->state= $this->get('State');
		$this->pagination = $this->get('Pagination');
		
		$this->path = "images" . DS . "com_itinerary" . DS ;
		
		$document = JFactory::getDocument();
		$document->addScriptDeclaration("function deleteConfirmation()
			{
				if(confirm('".JText::_('COM_ITINERARY_WARNING_BEFORE_DELETE_LABEL')."'))
				{
					Joomla.submitbutton('itineraries.delete');
				}
			};
			
			function messagesDuParcours(clefParcours){	
				location.href = '".JRoute::_('index.php?option=com_itinerary&view=messages')."&layout=default&clef='+clefParcours;
			};
			");
		
		if(count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		// Display the view
		parent::display($tpl);
	}
	
	protected function getSortFields()
	{		
		return array(	'a.titre' => JText::_('JGLOBAL_TITLE'),
						'a.description' => JText::_('JGLOBAL_DESCRIPTION'),
						'a.nombre_messages' => JText::_('COM_ITINERARY_FIELD_NOMBRE_MESSAGES_LABEL'),
						'a.note' => JText::_('COM_ITINERARY_FIELD_NOTE_LABEL'),
						'a.nombre_commentaires' => JText::_('COM_ITINERARY_FIELD_NOMBRE_NOTATIONS_LABEL'),
						'a.langue' => JText::_('JFIELD_LANGUAGE_LABEL'),
						'a.duree' => JText::_('COM_ITINERARY_FIELD_DUREE_LABEL'),
						'a.payant' => JText::_('COM_ITINERARY_FIELD_ACCESS_CATEGORY_LABEL'),
						'a.tarif' => JText::_('COM_ITINERARY_FIELD_TARIF_LABEL')
		);
	}
}