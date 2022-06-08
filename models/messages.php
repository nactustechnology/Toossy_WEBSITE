<?php
defined('_JEXEC') or die;

class ItineraryModelMessages extends JModelList
{
	public function __construct($config = array())
	{
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php' );
		
		if(empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'clef', 'm.clef',
			'date_creation','m.date_creation',
			'date_modification', 'm.date_modification',
			'clef_planificateur', 'm.clef_planificateur',
			'clef_parcours', 'm.clef_parcours',
			'ordering','m.ordering',
			'titre', 'm.titre',
			'texte','m.texte',
			'illustrations', 'm.illustrations',
			'latitude', 'm.latitude',
			'longitude', 'm.longitude',
			'activation_planificateur', 'm.activation_planificateur',
			'activation_administrateur', 'm.activation_administrateur'
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{				
		unset($_SESSION['amount']);
		unset($_SESSION['clefTailleParcours']);
		unset($_SESSION['renewalDate']);
		unset($_SESSION['clefSubscription']);
		unset($_SESSION['dateDebut']);
		unset($_SESSION['dateFin']);
		unset($_SESSION['parcoursMsgMax']);
		
		$app = JFactory::getApplication();
                $jinput = $app->input;
                $jcookie = $jinput->cookie;
                
		if(!empty($jinput->get->get('clef')))
		{
                    $idParcours = (int)$jinput->get->get('clef');
                    
                    $app->setUserState('idParcours',$idParcours);

                    $jcookie->set('idParcours',$idParcours, 0); // Set cookie data ($name, $value, $expire) $expire == 0: cookie lifetime is of browser session.
		}
                else
                {
                    $idParcours = $jcookie->get('idParcours',null); // Get cookie data  ($name, $defaultValue)
                    
                    $app->setUserState('idParcours',$idParcours);
                }

		parent::populateState('m.ordering','asc');
	}
	
	protected function getListQuery()
	{
		$user = JFactory::getUser()->id;
		$idParcours = JFactory::getApplication()->getUserState('idParcours');

		if (!empty($user) && !empty($idParcours))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('m.clef,m.date_creation,m.date_modification,m.ordering,m.clef_parcours,m.clef_planificateur,m.titre,m.texte,m.titre_illustrations,m.illustrations,m.latitude,m.longitude,m.activation_planificateur');
			$query->from($db->quoteName('#__itinerary_messages').' AS m');
			$query->where('m.clef_planificateur = '.(int) $user, 'AND');
			$query->where('m.clef_parcours = '.(int) $idParcours);
			
			$orderCol = $this->state->get('list.ordering','m.ordering');
			$orderDirn = $this->state->get('list.direction','asc');
			
			$query->order($db->escape($orderCol.' '.$orderDirn));
			
			return $query;
		}
		else
		{
			return false;
		}
	}

	private function getParcoursActivation()
	{
		$user=JFactory::getUser()->id;
		$idParcours=JFactory::getApplication()->getUserState('idParcours');
		
		if(!empty($user) && !empty($idParcours))
		{
			$db = $this->getDbo();
			$query = $db -> getQuery(true);
			$query->select('activation_planificateur');
			$query->from($db->quoteName('#__itinerary_parcours'));
			$query->where('clef ='.(int)$idParcours, 'AND');
			$query->where('clef_planificateur ='.(int)$user);

			$db->setQuery($query);
			$result=$db->loadResult();

			if(!is_null($result))
			{
				return $result;
			}
			else
			{
				return null;
			}
		}
	}
	
	public function updateDataInProduction($pks=null,$order=null)
	{
		$user=JFactory::getUser()->id;	
		
		if(!is_null($pks)&&!is_null($order)&&$this->getParcoursActivation()==1)
		{		
			/*$combinedArray = array_combine($pks,$order);
			
			foreach($combinedArray as $idMessage=>$ordering)
			{
				$db = $this->getDbo();
				
				$query = $db->getQuery(true);
				$query->update('#__itinerary_messages_prod AS m');
				$query->set('m.ordering = "'.$ordering.'"');
				$query->where('m.clef = '.(int) $idMessage);
				
				$db->setQuery($query);
				$db->execute();
			}*/
			
			$idParcours=JFactory::getApplication()->getUserState('idParcours');
			ItineraryHelper::setItineraryPinsFieldInProduction($this, $idParcours);	
		}
	}
        
        
}
