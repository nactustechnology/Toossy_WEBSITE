<?php
//No direct access to the file
defined('_JEXEC') or die('Restricted access');

class ItineraryModelItinerary extends JModelAdmin
{
	protected $text_prefix = 'COM_ITINERARY';
	
	public function __construct($config = array())
	{
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php' );
		
		parent::__construct($config);
	}
	
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		
		return $user->authorise('core.edit.own','com_itinerary');
	}
	
	public function getTable($type='Itinerary',$prefix='ItineraryTable',$config=array())
	{
		$table = JTable::getInstance($type,$prefix,$config);
		
		return $table;
	}
         
	public function getItem($pk=null)
	{
		$item=parent::getItem($pk);
		
		if($item)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('l.title AS titre_langue, l.image AS image_langue');
			$query->from($db->quoteName('#__languages').' AS l');
			$query->where('l.lang_code = "'.$item->langue.'"');
			
			$db->setQuery($query);
			$result=$db->loadAssoc();
			
			if($result)
			{
				$item->titre_langue = $result['titre_langue'];
				$item->image_langue = $result['image_langue'];
			}
			
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('t.theme AS theme_name');
			$query->from($db->quoteName('#__itinerary_themes').' AS t');
			$query->where('t.clef = "'.$item->theme.'"');
			
			$db->setQuery($query);
			$result=$db->loadAssoc();
			
			if($result)
			{
				$item->theme_name = $result['theme_name'];
			}
			
			$result2=$this->checkSubscription();
			
			if(!is_null($result2))
			{
				$item->subscriptionIsOK=$result2;
			}
			
		}
		
		return $item;
		
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_itinerary.itinerary','itinerary',array('control'=>'jform','load_data'=>$loadData));		
		
		
		if (empty($form))
		{
			return false;
		}

		return $form;
	}
	
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_itinerary.edit.itinerary.data', array());
		
		if(empty($data))
		{
			$data = $this->getItem();
			$data->clef_planificateur = JFactory::getUser()->id;
		}
		
		return $data;
	}
	
	protected function prepareTable($table)
	{		
		if(isset($table->activation_planificateur))
		{
			$parcoursActivation = $this->de_Et_Publication($table->activation_planificateur);
			
			if(!is_null($parcoursActivation))
			{
				$table->activation_planificateur=$parcoursActivation;
			}
		}

		$table->titre = htmlspecialchars($table->titre);
		$table->description = htmlspecialchars($table->description);
		$table->titre_illustrations = htmlspecialchars($table->titre_illustrations);
		
                //neutralisation fonction parcours payant
                $table->payant = 0;
                $table->tarif = 0;
                //Fin neutralisation
                
		if(isset($table->date_modification))
		{
			$table->date_modification = date("Y-m-d H:i:s");
		}
	}
	
	private function de_Et_Publication($planificateurActivationValue)
	{
		$parcoursActivationStatus=$this->getParcoursActivation();	
		
		if($planificateurActivationValue==1)
		{
			if($planificateurActivationValue!=$parcoursActivationStatus)
			{
				//publish
				if($this->checkSubscription()===true)
				{
					
					$this->activateParcours();
					return null;
				}
				else
				{
					JFactory::getApplication()->setUserState('noSubscription',JText::_('COM_ITINERARY_NO_SUBSCRIPTION_WARNING'));
					return 0;
				}
			}
		}
		elseif($planificateurActivationValue==0)
		{
			
			if($planificateurActivationValue!=$parcoursActivationStatus)
			{
				//unpublish
				$idParcours=JFactory::getApplication()->getUserState('idParcours');
				$this->desactivateParcours($idParcours);
				return null;
			}
		}
	}
	
        public function setParcoursActivation($activation=null)
	{
            $user=JFactory::getUser()->id;	
            $idParcours=JFactory::getApplication()->getUserState('idParcours');

            if(!empty($user) && !empty($activation) && !empty($idParcours))
            {
                $db = $this->getDbo();
                $query = $db -> getQuery(true);
                $query->update('#__itinerary_parcours');
                $query->set('activation_planificateur = "'.$activation.'"');
                $query->where('clef ='.(int)$idParcours, 'AND');
                $query->where('clef_planificateur ='.(int)$user);

                $db->setQuery($query);
                $db->execute();
            }
	}
        
	public function getParcoursActivation()
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
	
	public function checkSubscription()
	{
		$user=JFactory::getUser()->id;
		$idParcours=JFactory::getApplication()->getUserState('idParcours');
		
		if(!empty($user) && !empty($idParcours))
		{
			$today=date('Y-m-d');
			
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query -> select('clef');
			$query -> from('#__itinerary_subscriptions');
			$query -> where('clef_parcours ='.(int)$idParcours,'AND');
			$query -> where('clef_planificateur ='.(int)$user,'AND');
			$query -> where('date_debut <= "'.$today.'"','AND');
			$query -> where('date_fin >= "'.$today.'"');
			
			$db -> setQuery($query);
			
			$result = $db->loadAssoc();
			
			if($result!=null)
			{
                            return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
        
        public function getParcoursSubscription()
	{
		$user=JFactory::getUser()->id;
		$idParcours=JFactory::getApplication()->getUserState('idParcours');
		
		if(!empty($user) && !empty($idParcours))
		{
			$today=date('Y-m-d');
			
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query -> select('clef, clef_parcours, date_debut, date_fin');
			$query -> from('#__itinerary_subscriptions');
			$query -> where('clef_parcours ='.(int)$idParcours,'AND');
			$query -> where('clef_planificateur ='.(int)$user,'AND');
			$query -> where('date_debut <= "'.$today.'"','AND');
			$query -> where('date_fin >= "'.$today.'"');
                        $query -> order('clef DESC');
			
			$db -> setQuery($query);
			
			$result = $db->loadAssoc();
			
			if($result!=null)
			{
				return $result;
			}
			else
			{
				return null;
			}
		}
		else
		{
			return null;
		}
	}
	
	public function activateParcours()
	{
		$user=JFactory::getUser()->id;
		$idParcours=JFactory::getApplication()->getUserState('idParcours');
		
             
                
		$this->copyParcoursToProduction($user,$idParcours);
		
		$this->copyMessagesToProduction($user,$idParcours);
		
		//$this->updateAccessLevelMessage();
	}
	
	public function desactivateParcours($idParcours=null)
	{
		$user=JFactory::getUser()->id;
		
		if(!empty($user)&&!empty($idParcours))
		{
			$this->deleteParcoursFromProduction($user,$idParcours);
			
			$this->deleteMessagesFromProduction($user,$idParcours);
		}
	}

	private function copyParcoursToProduction($user=null,$idParcours=null)
	{	

		if(!empty($user) && !empty($idParcours))
		{
			$this ->deleteParcoursFromProduction($user,$idParcours);

			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('clef,clef_planificateur,titre,description,titre_illustrations,illustrations,type_parcours,theme,langue,duree,payant,telechargeable,tarif,tva,currency,nombre_messages,note,nombre_commentaires,latitude,longitude');
			$query->from($db->quoteName('#__itinerary_parcours'));
			$query->where('clef_planificateur = '.(int) $user, 'AND');
			$query->where('clef = '.(int) $idParcours);

			$db->setQuery($query);
			$values=$db->loadAssoc();

			if(!empty($values))
			{
				$columns=array('clef','clef_planificateur','titre','description','titre_illustrations','illustrations','type_parcours','theme','langue','duree','payant','telechargeable','tarif','tva','currency','nombre_messages','note','nombre_commentaires','latitude','longitude');
				
				$db = ItineraryHelper::connectToAnotherDB();
				$query = $db->getQuery(true);
				$query->insert($db->quoteName('itinerary_parcours_prod'));
				$query->columns($db->quoteName($columns));
				$query->values( '"'.implode('","',$values).'"');
				$db->setQuery($query);

				if($db->execute())
				{		
					ItineraryHelper::setItineraryPinsFieldInProduction($this, $idParcours);
					ItineraryHelper::putImageInProduction($values['illustrations']);
					
					if($values['payant'] == 1)
					{
						if(ItineraryHelper::deleteDestinationFromProduction($user)==true)
						{
							ItineraryHelper::copyDestinationToProduction($user);
						}	
					}
					
					return true;
				}
				else
				{
					return false;
				}
			}
			
		}
	}
	
	
	private function deleteParcoursFromProduction($user=null,$idParcours=null)
	{		
		if(!empty($user) && !empty($idParcours))
		{
			//$db = $this->getDbo();
			$db = ItineraryHelper::connectToAnotherDB();

			$query = $db->getQuery(true);
			$query->select('illustrations');
			$query->from('itinerary_parcours_prod');
			$query->where("clef =".(int)$idParcours);
			$db->setQuery($query);

			$illustration = $db->loadResult();

			if($illustration!=null)
			{
                            ItineraryHelper::removeImageFromProduction($illustration, "Itinerary");
			}

			
			$db = ItineraryHelper::connectToAnotherDB();
			$query = $db->getQuery(true);
			$query->delete('itinerary_parcours_prod');
			$query->where('clef = '.(int) $idParcours);
			$db->setQuery($query);
			
			$result = $db->execute();
			
			if($result)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	
	
	
	
	private function copyMessagesToProduction($user=null,$idParcours=null)
	{
		if(!empty($user) && !empty($idParcours))
		{
			$this->deleteMessagesFromProduction($user,$idParcours);
			
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('clef,clef_parcours,titre,texte,titre_illustrations,illustrations');
			$query->from($db->quoteName('#__itinerary_messages'));
			$query->where('clef_planificateur = '.(int) $user, 'AND');
			$query->where('clef_parcours = '.(int) $idParcours);
			
			$db->setQuery($query);
			$messagesParcours=$db->loadAssocList();
			
			if(!empty($messagesParcours))
			{
                            foreach($messagesParcours as $values)
                            {
                                $columns=array('clef','clef_parcours','titre','texte','titre_illustrations','illustrations');

                                //$db = $this->getDbo();
                                $db = ItineraryHelper::connectToAnotherDB();
                                $query = $db->getQuery(true);
                                $query->insert($db->quoteName('itinerary_messages_prod'));
                                $query->columns($db->quoteName($columns));
                                $query->values( '"'.implode('","',$values).'"');

                                $db->setQuery($query);
                                $db->execute();

                                ItineraryHelper::putImageInProduction($values['illustrations']);
                            }
			}
		}
	}
	
	private function deleteMessagesFromProduction($user=null,$idParcours=null)
	{		
		if(!empty($user) && !empty($idParcours))
		{
			//$db = $this->getDbo();
			$db = ItineraryHelper::connectToAnotherDB();
			
			$query = $db->getQuery(true);
			$query->select('illustrations');
			$query->from('itinerary_messages_prod');
			$query->where("clef_parcours =".(int)$idParcours);
			$db->setQuery($query);
			
			$illustrations = $db->loadAssocList();
					
			foreach($illustrations as $illustration)
			{		
				if(!empty($illustration['illustrations']))
				{
					ItineraryHelper::removeImageFromProduction($illustration['illustrations'], "Message");
				}
			}
			
			$db = ItineraryHelper::connectToAnotherDB();
			$query = $db->getQuery(true);
			$query->delete('itinerary_messages_prod');
			$query->where('clef_parcours = '.(int) $idParcours);
			$db->setQuery($query);
			 
			$result = $db->execute();
			
			if($result)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	public function updateDataInProduction($newData=null,$tableName=null)
	{	
		if(!is_null($newData)&&!empty($tableName))
		{
			$db = ItineraryHelper::connectToAnotherDB();
			$config['dbo'] = $db;
			$table = $this->getTable($tableName,'ItineraryTable',$config);
			
			$key = $table->getKeyName();
			$pk = $newData[$key];
			
			// Load the row if saving an existing record.
			$table->load($pk);
			
			// Bind the data.
			if (!$table->bind($newData))
			{
				$this->setError($table->getError());
				
				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);
			
			// Store the data.
			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}
			
			ItineraryHelper::putImageInProduction($newData['illustrations']);

			//$this->updateAccessLevelMessage();
			
			// Clean the cache.
			$this->cleanCache();
		}
	}
	
	public function delete(&$pks)
	{
		$result=parent::delete($pks);
		
		if($result)
		{
			foreach($pks as $idParcours)
			{
				$this->desactivateParcours($idParcours);
			}
		}
	}
	
	
	
	public function updateAccessLevelMessage()
	{
		$user=JFactory::getUser()->id;
		$idParcours=JFactory::getApplication()->getUserState('idParcours');
		
		if(!empty($user) && !empty($idParcours))
		{
			$parcoursPriceAccess = 1;
			$parcoursPriceAccess = ItineraryHelper::getParcoursPriceAccess();
			
			$db = ItineraryHelper::connectToAnotherDB();
			
			$query = $db -> getQuery(true);
			$query->update('#__itinerary_messages_prod');
			$query->set('payant = '.(int)$parcoursPriceAccess);
			$query->where('clef_parcours ='.(int)$idParcours);

			$db->setQuery($query);
			$db->execute();
		}
	}
}