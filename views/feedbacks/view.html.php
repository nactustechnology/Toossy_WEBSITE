<?php
defined('_JEXEC') or die;

class ItineraryViewFeedbacks extends JViewLegacy
{
	/**
	* Display the Message View
	*
	* @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	*
	* @return  void
	*/
	protected $items;

	 // Overwriting JView display method
	public function display($tpl = null)
	{		
		// Get some data from the models
		$this->items = $this->get('Items');
		$this->state= $this->get('State');
		$this->pagination = $this->get('Pagination');
		
		$app= JFactory::getApplication();
                $idParcours = $app-> getUserState('idParcours');

                $modelItinerary = JModelLegacy::getInstance('Itinerary', 'ItineraryModel');
                
                $this->itinerary = $modelItinerary->getItem($idParcours);
                $this->reports = $this->getModel()->getReportList();
                
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