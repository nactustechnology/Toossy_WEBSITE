<?php
defined('_JEXEC') or die;

class ItineraryViewMessages extends JViewLegacy
{
	protected $items;
	
	protected $state;

	protected $pagination;
	
	protected $idParcours;
	
	protected $itinerary;
	
	public function display($tpl = null)	
	{
		$this->items = $this->get('Items');
		$this->state= $this->get('State');
		$this->pagination = $this->get('Pagination');
		$this->path = "images" . DS . "com_itinerary" . DS ;
		
		$app = JFactory::getApplication();
		$idParcours = $app->getUserState('idParcours');
		
		$modelParcours = JModelLegacy::getInstance('Itinerary', 'ItineraryModel');
		$document = JFactory::getDocument();
                
		$this->itinerary = $modelParcours->getItem($idParcours);
		
                if($modelParcours->checkSubscription()===true)
                {
                    $this->subscription = $modelParcours->getParcoursSubscription();
                    
                    if(!empty($this->subscription))
                    {
                        $subscription = $this->subscription;
                        
                        $document->addScriptDeclaration('
                            function subscriptionIncrease(clefSubscription,clefParcours,clefTailleParcours,dateDebut,dateFin)
                            {
                                console.log("clicked");
                                document.getElementById("clefSubscription").value=clefSubscription;
                                document.getElementById("clefParcours").value=clefParcours;
                                document.getElementById("dateFin").value=dateFin;
                                document.getElementById("dateDebut").value=dateDebut;
                                document.getElementById("clefTailleParcours").value=clefTailleParcours;



                                document.getElementById("adminForm").action="/gestion-des-parcours?view=subscription&layout=edit";
                                document.getElementById("adminForm").submit();
                            }
                        ');
                    }
                        
                }
                    
		
		
		$document->addScriptDeclaration('
			function deleteConfirmation()
			{
				if(confirm("'.JText::_('COM_ITINERARY_WARNING_BEFORE_DELETE_LABEL').'"))
				{
					Joomla.submitbutton("messages.delete");
				}
			}
			
			function messagesDuParcours(clefMessage)
			{
				location.href = "'.JRoute::_('index.php?option=com_itinerary&view=message').'&layout=edit&clef="+clefMessage;
			}
			
			function numMsg()
			{
				var x = document.getElementsByClassName("rowNb");
				
				var i;
				for (i = 0; i < x.length;i++)
				{
					x[i].innerHTML = (i+1);
				}
			}
			
			function createPreview()
			{
				var link = "index.php?option=com_itinerary&view=itinerarypreview&tmpl=default";
				
				var myWindow = window.open(link,"'.JText::_('COM_ITINERARY_ITINERARY_PREVIEW').'","width=1100,height=900");
			}
			
			document.addEventListener("load",updateOrdering);
			
			function updateOrdering() 
			{
				jQuery.ajax({
					url:"index.php?option=com_itinerary&task=messages.saveOrderAjax&tmpl=component",
					type:"POST"
				});
			}			
		');
		
		//document.addEventListener("load",updateOrdering);

		if(count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}
		
		// Display the view
		parent::display($tpl);
	}
	
	
}