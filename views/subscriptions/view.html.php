<?php
defined('_JEXEC') or die;

class ItineraryViewSubscriptions extends JViewLegacy
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
		
		$this->path_image = "images" . DS . "com_itinerary" . DS ;
		$this->path = "images" . DS . "logos" . DS ;
		
		$model=$this->getModel();
		
		$this->ItinerarySizeMaximum=ItineraryHelper::getItinerarySizeMax($model);
		
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('
                    function subscriptionRenewal(clefSubscription,clefParcours,dateFin)
                    {			
                        document.getElementById("clefSubscription").value=clefSubscription;
                        document.getElementById("clefParcours").value=clefParcours;
                        document.getElementById("dateFin").value=dateFin;

                        document.getElementById("adminForm").action="/gestion-des-parcours?view=subscription&layout=edit";
                        document.getElementById("adminForm").submit();
                    }
                ');

			
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