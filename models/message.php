<?php
//No direct access to the file
defined('_JEXEC') or die('Restricted access');

class ItineraryModelMessage extends JModelAdmin
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
	
	protected function populateState()
	{	
		unset($_SESSION['amount']);
		unset($_SESSION['clefTailleParcours']);
		unset($_SESSION['renewalDate']);
		unset($_SESSION['clefSubscription']);
		unset($_SESSION['dateDebut']);
		unset($_SESSION['dateFin']);
		unset($_SESSION['parcoursMsgMax']);
		
		parent::populateState();
	}
	
	public function getTable($type='Message',$prefix='ItineraryTable',$config=array())
	{
		$table=JTable::getInstance($type,$prefix,$config);
			
		return $table;
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_itinerary.message','message',array('control'=>'jform','load_data'=>$loadData));
		
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_itinerary.edit.message.data', array());
		
		if(empty($data))
		{
			$data = $this->getItem();
			$data -> clef_planificateur = JFactory::getUser()->id;
			$data -> clef_parcours = JFactory::getApplication()->getUserState('idParcours');
		}

		return $data;
	}

	
	protected function prepareTable($table)
	{
		$table->titre = htmlspecialchars($table->titre, ENT_QUOTES);
		$table->texte = htmlspecialchars($table->texte, ENT_QUOTES);
		$table->titre_illustrations = htmlspecialchars($table->titre_illustrations, ENT_QUOTES);
		
		if(isset($table->date_modification))
		{
			$table->date_modification = date("Y-m-d H:i:s");
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

	public function saveorder($pks = array(), $order = null)
	{
		$table = $this->getTable();
		$tableClassName = get_class($table);

		if (empty($pks))
		{
			return JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
		}

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load((int) $pk);

			// Access checks.
			if (!$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
			}
			elseif ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
		}
		// Clear the component's cache
		$this->cleanCache();

		return true;
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
	
	
	public function isMsgInProduction($msgId=null)
	{
		if(!empty($msgId))
		{
			//$db=$this->getDbo();
			$db = ItineraryHelper::connectToAnotherDB();
			$query=$db -> getQuery(true);
			$query->select('clef');
			$query->from('itinerary_messages_prod');
			$query->where('clef = '.(int)$msgId);
			
			$db->setQuery($query);
			$result=$db->loadResult();
			
			if(!empty($result))
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
	
	public function updateDataInProduction($newData=null,$tableName=null)
	{		
		if(!empty($newData)&&!empty($tableName))
		{
			$db = ItineraryHelper::connectToAnotherDB();
			$config['dbo'] = $db;
			$table = $this->getTable($tableName,'ItineraryTable',$config);
			
			$key = $table->getKeyName();
			$pk = (!empty($newData[$key])) ? $newData[$key] : (int) $this->getState($this->getName() . '.id');
			
			//$newMsgId=null;
			if($pk>0)
			{
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
				
				$idParcours=JFactory::getApplication()->getUserState('idParcours');
				ItineraryHelper::setItineraryPinsFieldInProduction($this, $idParcours);				
				// Clean the cache.
				$this->cleanCache();
			}
		}
	}
	
	public function putMsgInProduction($newData=null)
	{
		if(!empty($newData))
		{
			$this->removeMsgInProduction($newData['clef']);
			
			$user=JFactory::getUser()->id;
			$idParcours=JFactory::getApplication()->getUserState('idParcours');
			
			$db = $this->getDbo();
			
			$query = $db -> getQuery(true);
			$query->select('clef,clef_parcours,titre,texte,titre_illustrations,illustrations');
			$query->from('#__itinerary_messages');
			$query->where('clef_planificateur = '.(int) $user,'AND');
			$query->where('clef_parcours = '.(int) $idParcours,'AND');
			$query->where('clef = '.(int) $newData['clef']);
			
			$db->setQuery($query);
			$newData=$db->loadAssoc();

			$newData['titre'] = htmlspecialchars($newData['titre'], ENT_QUOTES);
			$newData['texte'] = htmlspecialchars($newData['texte'], ENT_QUOTES);
			$newData['titre_illustrations'] = htmlspecialchars($newData['titre_illustrations'], ENT_QUOTES);
			
			$columns=array('clef','clef_parcours','titre','texte','titre_illustrations','illustrations');
			
			//$db = $this->getDbo();
			$db = ItineraryHelper::connectToAnotherDB();
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('itinerary_messages_prod'));
			$query->columns($db->quoteName($columns));
			$query->values( '"'.implode('","',$newData).'"');
			 
			$db->setQuery($query);
			
			$db->execute();
			
			ItineraryHelper::setItineraryPinsFieldInProduction($this, $idParcours);
			ItineraryHelper::putImageInProduction($newData['illustrations']);
		}
	}	
	
	public function removeMsgInProduction($removedMsgId=null)
	{
		if($removedMsgId!=null)
		{		
			$idParcours=JFactory::getApplication()->getUserState('idParcours');
			
			$db = ItineraryHelper::connectToAnotherDB();

			$query = $db->getQuery(true);
			$query->select('illustrations');
			$query->from('itinerary_messages_prod');
			$query->where("clef =".(int)$removedMsgId);
			$db->setQuery($query);

			$illustration = $db->loadResult();

			if($illustration!=null)
			{
				ItineraryHelper::removeImageFromProduction($illustration, "Message");
			};
			
			//$db = $this->getDbo();
			$db = ItineraryHelper::connectToAnotherDB();
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('itinerary_messages_prod'));
			$query->where('clef='.(int)$removedMsgId);
			 
			$db->setQuery($query);
			 
			$result = $db->execute();
			
			ItineraryHelper::setItineraryPinsFieldInProduction($this, $idParcours);
		}
	}	
	
	public function delete(&$pks)
	{
		$result=parent::delete($pks);
		
		if($result&&$this->checkSubscription()===true&&$this->getParcoursActivation()==1)
		{
			$db = ItineraryHelper::connectToAnotherDB();
			$config['dbo'] = $db;
			$table = $this->getTable('message_prod','ItineraryTable',$config);
			
			foreach($pks as $pk)
			{
				if (!$table->delete($pk))
				{
					$this->setError($table->getError());

					return false;
				}
			}
			
			$idParcours=JFactory::getApplication()->getUserState('idParcours');
			ItineraryHelper::setItineraryPinsFieldInProduction($this, $idParcours);			
		}
	}
}