<?php
defined('_JEXEC') or die;

class ItineraryViewitinerarypreview extends JViewLegacy
{
	protected $items;
	
	protected $state;

	protected $pagination;
	
	protected $idParcours;
	
	protected $itinerary;
	
	public function display($tpl = null)	
	{
		$modelMessages = JModelList::getInstance('Messages', 'ItineraryModel');
		
		$this->items = $modelMessages->getItems();

		$this->path = "images" . DS . "com_itinerary" . DS ;

		
		$modelParcours = JModelLegacy::getInstance('Itinerary', 'ItineraryModel');
		
		$idParcours = JFactory::getApplication()->getUserState('idParcours');
		$this->itinerary = $modelParcours->getItem($idParcours);
		
		$document = JFactory::getDocument();
		$document->addCustomTag( "<style type=\"text/css\"> \n" 
			." html,body, .contentpane{overflow:hidden;background:#ffffff;} \n" 
			." </style> \n");
		
		if(count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}
		
		// Display the view
		parent::display($tpl);
	}
}