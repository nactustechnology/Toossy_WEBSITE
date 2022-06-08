<?php
//No direct access to the file
defined('_JEXEC') or die('Restricted access');

class ItineraryModelSubscription extends JModelAdmin
{
	protected $text_prefix = 'COM_ITINERARY';
	
	public function __construct($config = array())
	{
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php' );
		
                $_SESSION['parcoursMsgMax']=30;
                
		parent::__construct($config);
	}
	
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		
		return $user->authorise('core.edit.own','com_itinerary');
	}	
	
	public function getTable($type='Subscription',$prefix='ItineraryTable',$config=array())
	{
		$table=JTable::getInstance($type,$prefix,$config);
			
		return $table;
	}
	
	public function getForm($data = array(), $loadData = true)
	{		
		$form = $this->loadForm('com_itinerary.subscription','subscription',array('control'=>'jform','load_data'=>$loadData));
		
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_itinerary.edit.subscription.data', array());
		
		if(empty($data))
		{
			$data = $this->getItem();
			$data -> clef_planificateur = JFactory::getUser()->id;
			$data -> clef_parcours = JFactory::getApplication()->getUserState('idParcours');
		}

		return $data;
	}
	
	protected function populateState()
	{	
            $app = JFactory::getApplication();

            if(!empty($app->input->post->get('clefParcours')))
            {
                $app->setUserState('idParcours',(int)$app->input->post->get('clefParcours'));

                $date_debut=$app->input->post->get('dateDebut');
                $date_fin=$app->input->post->get('dateFin');
                $_SESSION['clefSubscription']=(int)$app->input->post->get('clefSubscription');

                if(!empty($app->input->post->get('clefTailleParcours')))
                {
                    $_SESSION['clefTailleParcours']=(int)$app->input->post->get('clefTailleParcours');
                    $_SESSION['dateDebut']=$date_debut;

                    $_SESSION['clefTailleParcours']=$_SESSION['clefTailleParcours']+1;
                    $_SESSION['dateFin']=$date_fin;
                    $_SESSION['parcoursMsgMax']=$this->getParcoursMsgMax($_SESSION['clefTailleParcours']);
                }

                $date_fin=strtotime('+ 1 day',strtotime($date_fin));
                $_SESSION['renewalDate']=date('Y-m-d',$date_fin);
            }

            if(!empty($app->input->post->get('clefParcours')))
            {
                $app->setUserState('idParcours',(int)$app->input->post->get('clefParcours'));
            }

            parent::populateState();
	}
	
	public function getItem($pk = null)
	{
            $item=parent::getItem($pk);

            $user=JFactory::getUser()->id;
            $idParcours=JFactory::getApplication()->getUserState('idParcours');
            
            $dureeArray=array(1,3,6,12);
            $priceArray=null;
            
            foreach($dureeArray as $duree)
            {
                $tarif=$this->getAmount($duree,'Tarif');
                $priceArray[]=array('duree'=>$duree,'tarif'=>$tarif,'max'=>$_SESSION['parcoursMsgMax'],'number'=>$_SESSION['itineraryCategoryNb']);
            }
            
            $item->prices = $priceArray;

            $userCountry=$this->getUserCountry();
            
            $item->tva_rate = $this->getTvaRate($userCountry);
            $item->currency = $this->getCurrency($userCountry);
            //$item->currency_exchange_rate = $this->getCurrencyExchangeRate($item->currency);

            return $item;
	}
        
        public function getUserCountry()
        {
            $user=JFactory::getUser()->id;
            
            if(!is_null($user))
            {
                $db=$this->getDbo();
                $query = $db->getQuery(true);
                $query->select('profile_value');
                $query->from('#__user_profiles');
                $query->where('user_id ='.(int)$user,'AND');
                $query->where('profile_key = "profile.country"');

                $db->setQuery($query);
                
                return $db->loadAssoc();
            }
            else
            {
                return null;
            }
        }
        
        
        
        
        public function getTvaRate($userCountry=null)
        {
            if(!is_null($userCountry))
            {               
                $userCountry=$userCountry["profile_value"];

                $db=$this->getDbo();
                $query = $db->getQuery(true);
                $query->select('taux');
                $query->from('#__itinerary_tva');
                $query->where('pays ='.$userCountry.'');

                $db->setQuery($query);
                $result=$db->loadAssoc();

                if($result)
                {
                    return floatval($result["taux"]);
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
        
        
        
        public function getCurrency($userCountry=null)
        {
            if(!is_null($userCountry))
            {                
                $userCountry=$userCountry["profile_value"];;

                $db=$this->getDbo();
                $query = $db->getQuery(true);
                $query->select('currency');
                $query->from('#__itinerary_currency');
                $query->where('pays ='.$userCountry.'');

                $db->setQuery($query);
                $result=$db->loadAssoc();

                if($result)
                {
                    return $result["currency"];
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
        
        public function getCurrencyExchangeRate($userCurrency)
        {            
            if(!is_null($userCurrency))
            {
                $db=$this->getDbo();
                $query = $db->getQuery(true);
                $query->select('exchange_rate');
                $query->from('#__itinerary_currency_exchange_rate');
                $query->where('currency = "'.$userCurrency.'"');

                $db->setQuery($query);
                $result=$db->loadAssoc();

                if($result)
                {
                    return floatval($result["exchange_rate"]);
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
	
	
	public function getSubscriptionMsgLimit($idParcours=null)
	{
            return 30;
	}
	
	public function updateDataInProduction($newData=null,$tableName=null)
	{		
            if(!empty($newData)&&!empty($tableName)&&$this->getParcoursActivation()==1)
            {
                $table=$this->getTable($tableName);

                $key = $table->getKeyName();
                $pk = (!empty($newData[$key])) ? $newData[$key] : (int) $this->getState($this->getName() . '.id');

                $newMsgId=null;

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

                    if(!is_null($newMsgId))
                    {
                        $table->clef=$newMsgId;
                    }

                    // Clean the cache.
                    $this->cleanCache();
                }
                else
                {
                    $user=JFactory::getUser()->id;
                    $idParcours=JFactory::getApplication()->getUserState('idParcours');

                    $db = $this->getDbo();

                    $query = $db -> getQuery(true);
                    $query->select('clef,clef_parcours,ordering,titre,texte,titre_illustrations,illustrations,latitude,longitude');
                    $query->from('#__itinerary_messages');
                    $query->where('clef_planificateur = '.(int) $user,'AND');
                    $query->where('clef_parcours = '.(int) $idParcours,'AND');
                    $query->order('date_creation DESC');

                    $db->setQuery($query);
                    $newData=$db->loadAssoc();

                    $newData['titre'] = htmlspecialchars($newData['titre'], ENT_QUOTES);
                    $newData['texte'] = htmlspecialchars($newData['texte'], ENT_QUOTES);
                    $newData['titre_illustrations'] = htmlspecialchars($newData['titre_illustrations'], ENT_QUOTES);

                    $columns=array('clef','clef_parcours','ordering','titre','texte','titre_illustrations','illustrations','latitude','longitude');

                    $db = $this->getDbo();
                    $query = $db->getQuery(true);
                    $query->insert($db->quoteName('#__itinerary_messages_prod'));
                    $query->columns($db->quoteName($columns));
                    $query->values( '"'.implode('","',$newData).'"');

                    $db->setQuery($query);

                    $db->execute();
                }
            }
	}
	
	
	public function getUserCusId()
	{
            $user=JFactory::getUser()->id;

            if($user!=null)
            {
                $db=$this->getDbo();
                $query=$db->getQuery(true);
                $query->select('id_cus_u');
                $query->from('#__users');
                $query->where('id ='.(int)$user);

                $db->setQuery($query);
                $result=$db->loadResult();

                return $result;
            }
            else
            {
                return null;
            }
	}
	
	

	public function getUserReference()
	{
            $user=JFactory::getUser()->id;

            if($user!=null)
            {
                $db=$this->getDbo();
                $query=$db->getQuery(true);
                $query->select('ref_com_u');
                $query->from('#__users');
                $query->where('id ='.(int)$user);			

                $db->setQuery($query);

                $result=$db->loadResult();

                return $result;
            }
            else
            {
                return null;
            }
	}


	public function setUserCusId($cusId=null)
	{
            $user=JFactory::getUser()->id;		

            if(!empty($user) && !empty($cusId))
            {
                $db = $this->getDbo();
                $query = $db -> getQuery(true);
                $query->update('#__users');
                $query->set('id_cus_u = "'.$cusId.'"');
                $query->where('id ='.(int)$user);

                $db->setQuery($query);
                $db->execute();
            }
	}
	
	public function setUserReference()
	{
            $user=JFactory::getUser()->id;
            $referenceUser = (int)(date('Y').$user)+11111111;

            if(!empty($user) && !empty($referenceUser))
            {
                $db = $this->getDbo();
                $query = $db -> getQuery(true);
                $query->update('#__users');
                $query->set('ref_com_u = "'.$referenceUser.'"');
                $query->where('id ='.(int)$user);

                $db->setQuery($query);
                $db->execute();
            }

            return $referenceUser;
	}
	
	private function setRenewed()
	{	
		$user=JFactory::getUser()->id;
		
		if(!empty($user) && !empty($_SESSION['clefSubscription']))
		{
			$db = $this->getDbo();
			$query = $db -> getQuery(true);
			$query->update('#__itinerary_subscriptions');
			$query->set('renewed = 1');
			$query->where('clef ='.(int)$_SESSION['clefSubscription']);
			
			$db->setQuery($query);
			$db->execute();
		}
	}
	
	/*private function setIncreased()
	{	
		$user=JFactory::getUser()->id;
		
		if(!empty($user) && !empty($_SESSION['clefSubscription']))
		{
			$db = $this->getDbo();
			$query = $db -> getQuery(true);
			$query->update('#__itinerary_subscriptions');
			$query->set('increased = 1');
			$query->where('clef ='.(int)$_SESSION['clefSubscription'],'AND');
			$query->where('clef_planificateur ='.(int)$user);
			
			$db->setQuery($query);
			$db->execute();
		}
	}*/

	
	private function getInitialSubscription()
	{
            $user=JFactory::getUser()->id;

            if(isset($_SESSION['clefSubscription'])&&!empty($user))
            {
                $db=$this->getDbo();
                $query=$db->getQuery(true);
                $query->select('initialsubscription');
                $query->from('#__itinerary_subscriptions');
                $query->where('clef_planificateur ='.(int)$user,'AND');
                $query->where('clef ='.(int)$_SESSION['clefSubscription']);

                $db->setQuery($query);
                $result=$db->loadResult();

                if(!empty($result))
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
	
	private function getDureeInitialSubscription($initialSubscription=null)
	{
            $user=JFactory::getUser()->id;

            if(!empty($initialSubscription)&&!empty($user))
            {
                $db=$this->getDbo();
                $query=$db->getQuery(true);
                $query->select('date_debut,date_fin');
                $query->from('#__itinerary_subscriptions');
                $query->where('clef_planificateur ='.(int)$user,'AND');
                $query->where('clef ='.(int)$initialSubscription);

                $db->setQuery($query);
                $result=$db->loadAssoc();

                if(!empty($result))
                {
                    $result=(int)date('m',strtotime($result['date_fin'])-strtotime($result['date_debut']));

                    return $result;
                }
                else
                {
                    return null;
                }
            }	
	}
	
	private function getParcoursMsgMax($parcoursSize=null)
	{
		if(!empty($parcoursSize))
		{
			$db=$this->getDbo();
			$query = $db->getQuery(true);
			$query->select('s.max');
			$query->from('#__itinerary_parcours_size AS s');
			$query->where('s.clef ='.(int)$parcoursSize);;
			$db->setQuery($query);
			
			$result=$db->loadResult();
			
			return $result;
		}
	}
	
	/*private function getTarif($parcoursSize=null, $duree=null)
	{
		if(!empty($parcoursSize)&&!empty($duree))
		{
			$db=$this->getDbo();
			$query = $db->getQuery(true);
			$query->select('p.tarif');
			$query->from('#__itinerary_pricing AS p');
			$query->where('p.clef_size ='.(int)$parcoursSize,'AND');
			$query->where('p.duree ='.(int)$duree);
			$db->setQuery($query);
			
			$result=$db->loadResult();
			
			return $result;
		}
		else
		{
			return null;
		}
	}*/

	public function getAmount($duree=null,$dataType='Amount')
	{
            if(!empty($duree))
            {
                $subscriptionNb = (int)$this->userSubscriptionQuantity($duree)+1;
                
                $_SESSION['itineraryCategoryNb']=$subscriptionNb;
                
                if( $subscriptionNb > 5 )
                {
                    $subscriptionNb = 5;
                }
                
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('p.tarif');
                $query->from('#__itinerary_pricing AS p');
                $query->where('p.clef_size ='.(int)$subscriptionNb,'AND');
                $query->where('p.duree ='.(int)$duree);
                $db->setQuery($query);
                $result = $db->loadAssoc();
                
                $userCountry = $this->getUserCountry();
                $userCountryTva = $this->getTvaRate($userCountry);
                
                if(is_null($result) || is_null($userCountryTva))
                {
                    return null;
                }
                else
                {
                    if($dataType=='Tarif')
                    {
                        $result=$result['tarif']; 
                    }
                    else
                    {
                        $result=$result['tarif']*$duree*(1+$userCountryTva); 
                    }
                        

                    return $result;
                }		
            }

            return null;
	}

    public function registerTransaction($registrationType="Invoice")
    {
        $clef_parcours=JFactory::getApplication()->getUserState('idParcours');

        $clef_planificateur=JFactory::getUser()->id;

        if(empty($_SESSION['renewalDate']))
        {
                $date_debut=date('Y-m-d');
        }
        elseif(!empty($_SESSION['renewalDate']))
        {
            $date_debut=$_SESSION['renewalDate'];

            $this->setRenewed();
        }

        $initialSubscription=null;

        $nombreMois='+'.$_SESSION['dureeAbonnement'].' month - 1 days';
        
        $date_fin=date('Y-m-d',strtotime($nombreMois, strtotime($date_debut)));
        
        $prix=$_SESSION['amount']/100;

        $userCountry=$this->getUserCountry();

        $tva_rate = $this->getTvaRate($userCountry);
        
        $currency = $this->getCurrency($userCountry);
        //$tva_rate=0.20;

        $cgv_approval=$_SESSION['cgv_approval'];

        if(!empty($clef_parcours) && !empty($clef_planificateur) && !empty($_SESSION['dureeAbonnement']) && !empty($_SESSION['amount']))
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);		

            if($registrationType=="Invoice")
            {
                $invoiceNb=$this->defineInvoiceNb();
            }
            else
            {
                $invoiceNb='""';
            }

            $orderNb = $this->defineOrderNb();

            $columns = array('clef_parcours','clef_planificateur','commandeNb','factureNb','date_debut','date_fin','duree','currency');
            $values = array($clef_parcours,$clef_planificateur,$orderNb,$invoiceNb,$db->quote($date_debut),$db->quote($date_fin),$_SESSION['dureeAbonnement'],$db->quote($currency)); 
             

            $query->insert($db->quoteName('#__itinerary_subscriptions'));
            $query->columns($db->quoteName($columns));
            $query->values(implode(',', $values));

            $db->setQuery($query);

            if($db->execute())
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
        
        protected function defineOrderNb()
        {
            $user=JFactory::getUser()->id;
            $idParcours=JFactory::getApplication()->getUserState('idParcours');
        
            if(!empty($user) && !empty($idParcours))
            {
                $today = date('Y-m-d');

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('MAX(commandeNb) as orderNb');
                $query->from($db->quoteName('#__itinerary_subscriptions'));
                $db->setQuery($query);
                $result=$db->loadResult();
                
                if(!empty($result))
                {
                    $commandeNb = (int)$result+1;        
                }
                else
                {
                    $commandeNb = 29810611;
                }
                
                return $commandeNb;
            }
            
            return 'null';
        }
        
        protected function setOrderNb()
        {
            $user=JFactory::getUser()->id;
            $idParcours=JFactory::getApplication()->getUserState('idParcours');
        
            if(!empty($user) && !empty($idParcours))
            {
                $today = date('Y-m-d');

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('MAX(commandeNb) as orderNb');
                $query->from($db->quoteName('#__itinerary_subscriptions'));
                $db->setQuery($query);
                $result=$db->loadResult();
                
                if(!empty($result))
                {
                    $commandeNb = (int)$result+1;        
                }
                else
                {
                    $commandeNb = 29810611;
                }
                
                $query = $db->getQuery(true);
                $query->update('#__itinerary_subscriptions');
                $query->set('commandeNb = "'.$commandeNb.'"');
                $query -> where('clef_parcours ='.(int)$idParcours,'AND');
                $query -> where('clef_planificateur ='.(int)$user,'AND');
                $query -> where('date_debut <= "'.$today.'"','AND');
                $query -> where('date_fin >= "'.$today.'"'); 
                $db -> setQuery($query);
                $db->execute();
            }
                
        }
        
        protected function getOrderNb()
	{
		$user = JFactory::getUser()->id;
		$idParcours = JFactory::getApplication()->getUserState('idParcours');

		if (!empty($user) && !empty($idParcours))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('commandeNb');
			$query->from($db->quoteName('#__itinerary_subscriptions'));
			$query->where('clef_planificateur = '.(int) $user, 'AND');
			$query->where('clef_parcours = '.(int) $idParcours);
			$query->order('clef DESC');
			$db->setQuery($query);
			$result=$db->loadResult();
                        
                        //$result = date("Y").date("m").sprintf('%04d',$result);
                        
			return $result;
		}
		else
		{
			return false;
		}
	}
        
        protected function defineInvoiceNb()
        {
            $user=JFactory::getUser()->id;
            $idParcours=JFactory::getApplication()->getUserState('idParcours');
        
            if(!empty($user) && !empty($idParcours))
            {
                $today = date('Y-m-d');

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('MAX(factureNb) as invoiceNb');
                $query->from($db->quoteName('#__itinerary_subscriptions'));
                $db->setQuery($query);
                $result=$db->loadResult();
                
                if(!empty($result))
                {
                    $factureNb = (int)$result+1;        
                    
                }
                else
                {
                    $factureNb = 29860729;
                }
                
                return $factureNb;
            }
            
            return 'null';
        }
        
        protected function setInvoiceNb()
        {
            $user=JFactory::getUser()->id;
            $idParcours=JFactory::getApplication()->getUserState('idParcours');
        
            if(!empty($user) && !empty($idParcours))
            {
                $today = date('Y-m-d');

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('MAX(factureNb) as invoiceNb');
                $query->from($db->quoteName('#__itinerary_subscriptions'));
                $db->setQuery($query);
                $result=$db->loadResult();
                
                if(!empty($result))
                {
                    $factureNb = (int)$result+1;        
                    
                }
                else
                {
                    $factureNb = 29860729;
                }
                
                $query = $db->getQuery(true);
                $query->update('#__itinerary_subscriptions');
                $query->set('factureNb = "'.$factureNb.'"');
                $query -> where('clef_parcours ='.(int)$idParcours,'AND');
                $query -> where('clef_planificateur ='.(int)$user,'AND');
                $query -> where('date_debut <= "'.$today.'"','AND');
                $query -> where('date_fin >= "'.$today.'"'); 
                $db -> setQuery($query);
                $db->execute();
            }  
        }
        
        protected function getInvoiceNb()
	{
            $user = JFactory::getUser()->id;
            $idParcours = JFactory::getApplication()->getUserState('idParcours');

            if (!empty($user) && !empty($idParcours))
            {
                    $db = $this->getDbo();
                    $query = $db->getQuery(true);
                    $query->select('s.factureNb');
                    $query->from($db->quoteName('#__itinerary_subscriptions').' AS s');
                    $query->where('s.clef_planificateur = '.(int) $user, 'AND');
                    $query->where('s.clef_parcours = '.(int) $idParcours);
                    $query->order('s.clef DESC');
                    $db->setQuery($query);
                    $result=$db->loadResult();

                    //$result = date("Y").date("m").sprintf('%04d',$result);

                    return $result;
            }
            else
            {
                    return false;
            }
	}
        
        private function userSubscriptionQuantity($duree=null)
        {
            
            if(isset($_SESSION['renewalDate']))
            {
                $researchedDate = $_SESSION['renewalDate'];
            }
            else
            {
                $researchedDate = date('Y-m-d');
            }
            
            
            
            $user = JFactory::getUser()->id;
            
            if (!empty($user) && !empty($duree) )
            {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query -> select('COUNT(clef) as subscriptionQty');
                $query -> from($db->quoteName('#__itinerary_subscriptions'));
                $query -> where('clef_planificateur ='.(int)$user,'AND');
                $query -> where('date_debut <= "'.$researchedDate.'"','AND');
                $query -> where('date_fin >= "'.$researchedDate.'"','AND'); 
                $query -> where('duree ='.(int)$duree); 
                $query -> order('clef DESC');
                $db -> setQuery($query);
                $result = $db -> loadResult();
                
                if(empty($result))
                {
                    return 0;
                }
                else
                {
                    return $result;
                }
            }
            
            return 0;
        }
	
	
	public function getDocument($documentType="Invoice")
	{
                $cpyName=JText::_('COM_ITINERARY_CPY_NAME');
		$cpyAddress1=JText::_('COM_ITINERARY_CPY_ADRESS_1');
		$cpyPostalCode=JText::_('COM_ITINERARY_CPY_ADRESS_2');
		$cpyCity=JText::_('COM_ITINERARY_CPY_ADRESS_3');
		$cpyCountry=JText::_('COM_ITINERARY_CPY_ADRESS_4');
		$cpySIRET=JText::_('COM_ITINERARY_CPY_SIRET');
		$cpyTVA=JText::_('COM_ITINERARY_CPY_NB_TVA');

		$clientProfile=ItineraryHelper::getUserProfile();		

		$clientName=JFactory::getUser()->username;
		$clientAddress1=$clientProfile['profile.address1'];
		$clientPostalCode=$clientProfile['profile.postal_code'];
		$clientCity=$clientProfile['profile.city'];
		$clientCountry=JText::_($clientProfile['profile.country']);
		

		$commandeDateLabel=JText::_('COM_ITINERARY_ORDER_DATE').' :';
		$todayDate=date('d/m/Y');

		$clientReferenceLabel=JText::_('COM_ITINERARY_INVOICE_CLIENT_REFERENCE').' :';

		if($this->getUserReference())
		{
			$clientReference=$this->getUserReference();
		}
		else
		{
			$clientReference=$this->setUserReference();
		}
		
		$idParcours=JFactory::getApplication()->getUserState('idParcours');
		
		$productIdentifiantLabel=JText::_('COM_ITINERARY_FIELD_PARCOURS_ID_LABEL').' :';
		$productIdentifiant=(4*(int)$idParcours+426957183);
		
		$commandeNbLabel=JText::_('COM_ITINERARY_ORDER_NUMBER').' :';
		$commandeNb=$this->getOrderNb();
                $commandeNb='C-'.$commandeNb;

		$factureNbLabel=JText::_('COM_ITINERARY_INVOICE_NUMBER').' :';
		$factureNb=$this->getInvoiceNb();
		$factureNb='F-'.$factureNb;
		
                

		$productReferenceLabel=JText::_('COM_ITINERARY_INVOICE_PRODUCT_REFERENCE').' :';
		$productReference='PA-'.$_SESSION['dureeAbonnement'].'-'.$_SESSION['parcoursMsgMax'];
		$productDescriptionLabel=JText::_('COM_ITINERARY_INVOICE_PRODUCT_DESCRIPTION_LABEL').' :';
		
		
                $date_debut=date('Y-m-d');

                if(!empty($_SESSION['renewalDate']))
                {
                        $date_debut=$_SESSION['renewalDate'];
                }

                $nombreMois='+'.$_SESSION['dureeAbonnement'].' month - 1 days';
                $date_fin=date('d/m/Y',strtotime($nombreMois,strtotime($date_debut)));

                $date_debut=date('d/m/Y',strtotime($date_debut));

                if($documentType=="Invoice")
                { 
                    $productDescription=sprintf(JText::_('COM_ITINERARY_INVOICE_PRODUCT_SUBSCRIPTION_DESCRIPTION'), $_SESSION['dureeAbonnement'], $_SESSION['parcoursMsgMax'], $date_debut, $date_fin);
                }
                else
                {
                    $productDescription=sprintf(JText::_('COM_ITINERARY_INVOICE_PRODUCT_SUBSCRIPTION_DESCRIPTION_NO_DATE'), $_SESSION['dureeAbonnement'], $_SESSION['parcoursMsgMax']);
                }
                
		
		$line1=JText::_('COM_ITINERARY_CPY_NAME').' - ';
		$line1.=JText::_('COM_ITINERARY_CPY_ADRESS_1').' - ';
		$line1.=JText::_('COM_ITINERARY_CPY_ADRESS_2').' '.JText::_('COM_ITINERARY_CPY_ADRESS_3').' - ';
		$line1.=JText::_('COM_ITINERARY_CPY_ADRESS_4');

		$line2=JText::_('COM_ITINERARY_CPY_SIRET').' - '.JText::_('COM_ITINERARY_CPY_NB_TVA');
		$line3=JText::_('COM_ITINERARY_CPY_FORME_CAPITAL_SOCIAL');
		$line4=JText::_('COM_ITINERARY_CPY_TEL').' - '.JText::_('COM_ITINERARY_CPY_SITE_WEB');
		
		setlocale(LC_MONETARY,"fr_FR");
                $userCountry = $this->getUserCountry();
                $tvaRate = $this->getTvaRate($userCountry);
                $currency = $this->getCurrency($userCountry);
                //$currency = "EUR";
                
                if($documentType=="Invoice")
                { 
                    $priceTTCLabel=JText::_('COM_ITINERARY_FIELD_PRIX_TTC_LABEL');
                }
                else
                {
                    $priceTTCLabel=JText::_('COM_ITINERARY_FIELD_TOTAL_AMOUNT_LABEL');
                }
                
		$priceTTC=$_SESSION['amount']/100;
                
		$priceHTLabel=JText::_('COM_ITINERARY_FIELD_PRIX_HT_LABEL');
                $priceHT=$priceTTC/(1+$tvaRate);
                
                $TVA=$priceTTC-$priceHT;

		$priceTTC=money_format('%.2n',$priceTTC);
		$priceHT=money_format('%.2n',$priceHT);
                
                
                $TVALabel=sprintf(JText::_("COM_ITINERARY_FIELD_TVA_LABEL"),$tvaRate*100);
		$TVA=money_format('%.2n',$TVA);
                
                /*$priceTTC += " " . $currency;
                $priceHT += " " . $currency;
                $TVA += " " . $currency;*/
                
                if($documentType=="Invoice")
                { 
                    $typePaiementLabel=JText::_('COM_ITINERARY_INVOICE_PAYMENT_TYPE_CARD');
                }
                else
                {
                    $typePaiementLabel=JText::_('COM_ITINERARY_ORDER_PAYMENT_TYPE_IBAN');
                }

		//remplissage modèle facture		
		//Including PHPExcel library and creation of its object
		define('__ROOT__',$_SERVER['DOCUMENT_ROOT'] );
		require_once(__ROOT__.'/libraries/PHPExcel/PHPExcel.php');

		$temp=str_replace('www','temp',__ROOT__).'/';

		$titreSheet=JText::_('COM_ITINERARY_INVOICE_LABEL');
                
                if($documentType=="Invoice")
                { 
                    $titreSpreadSheet=JText::_('COM_ITINERARY_INVOICE_TITLE').'-'.$factureNb;
                }
                else
                {
                    $titreSpreadSheet=JText::_('COM_ITINERARY_ORDER_TITLE').'-'.$commandeNb;
                }

		$phpExcel = new PHPExcel;

		// Setting font to Arial Black
		$phpExcel->getDefaultStyle()->getFont()->setName('Tahoma');

		// Setting font size to 14
		$phpExcel->getDefaultStyle()->getFont()->setSize(14);
		$phpExcel ->getProperties()->setTitle(JText::_('COM_ITINERARY_INVOICE_TITLE'));
		$phpExcel ->getProperties()->setCreator("NACTECH");

		$sheet = $phpExcel ->getActiveSheet();
		$sheet->setTitle($titreSheet);

		$cpyArray = array($cpyName,$cpyAddress1,$cpyPostalCode." ".$cpyCity,$cpyCountry,$cpySIRET,$cpyTVA);
		$cpyArray = array_chunk($cpyArray, 1);
		$sheet->fromArray($cpyArray,NULL,'A6');

		$clientArray = array($clientName,$clientAddress1,$clientPostalCode." ".$clientCity,$clientCountry);
		$clientArray = array_chunk($clientArray, 1);
		$sheet->fromArray($clientArray,NULL,'B13');
                
                if($documentType=="Invoice")
                { 
                    $referenceLabelArray = array(JText::_('COM_ITINERARY_INVOICE_LABEL'),$clientReferenceLabel,$productIdentifiantLabel,$commandeDateLabel,$commandeNbLabel,$factureNbLabel);
                }
                else
                {
                    $referenceLabelArray = array(JText::_('COM_ITINERARY_ORDER_LABEL'),$clientReferenceLabel,$productIdentifiantLabel,$commandeDateLabel,$commandeNbLabel,'');
                }
                    
		
                $referenceLabelArray = array_chunk($referenceLabelArray, 1);
		$sheet->fromArray($referenceLabelArray,NULL,'A18');

		$referenceArray = array('',$clientReference,$productIdentifiant,$todayDate,$commandeNb,$factureNb);
                
                if($documentType=="Invoice")
                { 
                    $referenceArray = array('',$clientReference,$productIdentifiant,$todayDate,$commandeNb,$factureNb);
                }
                else
                {
                    $referenceArray = array('',$clientReference,$productIdentifiant,$todayDate,$commandeNb,'');
                }
                
		$referenceArray = array_chunk($referenceArray, 1);
		$sheet->fromArray($referenceArray,NULL,'B18');

                if($documentType=="Invoice")
                {
                    $dataLabelArray = array($productReferenceLabel,$productDescriptionLabel,$priceHTLabel,$TVALabel,$priceTTCLabel);
                    $dataArray = array($productReference,$productDescription,$priceHT,$TVA,$priceTTC);
                }
                else
                {
                    $dataLabelArray = array($productReferenceLabel,$productDescriptionLabel,'','',$priceTTCLabel);
                    $dataArray = array($productReference,$productDescription,'','',$priceTTC);
                }
		
		$dataLabelArray = array_chunk($dataLabelArray, 1);
		$sheet->fromArray($dataLabelArray,NULL,'A25');

		
		$dataArray = array_chunk($dataArray, 1);
		$sheet->fromArray($dataArray,NULL,'B25');

		$sheet->getCell('B31')->setValue($typePaiementLabel);
		
		$dataFooter = $line1.' '.$line2.' '.$line3.' '.$line4;
		
		$sheet->getStyle('A1:B32')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$sheet->getStyle('A1:B32')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$sheet->getStyle('B13:B16')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyle('B25')->getAlignment()->setWrapText(true);
		$sheet->getStyle('A6')->getFont()->setBold('true');
		$sheet->getStyle('A18')->getFont()->setBold('true');
		$sheet->getStyle('B13')->getFont()->setBold('true');
		$sheet->getStyle('B18:B23')->getFont()->setBold('true');
		$sheet->getStyle('B25:B29')->getFont()->setBold('true');
		
		$sheet->getStyle('A32:B32')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('A32:B32')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
		$sheet->getStyle('A32:B32')->getAlignment()->setWrapText(true);
		$sheet->mergeCells('A32:B32');
		
		$sheet->getCell('A32')->setValue($dataFooter);
		
		$sheet->setShowGridLines(false);
		
		$sheet->getStyle('A18')->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
		$sheet->getStyle('A24:B28')->getBorders()->applyFromArray(
					array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM,'color' => array('rgb' => '000000'))));

		$sheet->getStyle('A2:B2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('a61f21');
		$sheet->getStyle('A3:B3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('bf383a');

		//#a61f21 -83.58
		//#bf383a -62.4

		$sheet->getColumnDimension('A')->setWidth(35);
		$sheet->getColumnDimension('B')->setWidth(65);

		$sheet->getRowDimension('2')->setRowHeight(42);
		$sheet->getRowDimension('3')->setRowHeight(31);
	
		$sheet->getRowDimension('25')->setRowHeight(27);
		$sheet->getRowDimension('26')->setRowHeight(48);
		$sheet->getRowDimension('27')->setRowHeight(27);
		$sheet->getRowDimension('28')->setRowHeight(27);
		$sheet->getRowDimension('29')->setRowHeight(27);
		$sheet->getRowDimension('32')->setRowHeight(210);
		
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('Logo');
		$objDrawing->setDescription('Logo');
		$objDrawing->setPath('/images/logos/Toossy_logo.jpg',false);
                //$objDrawing->setPath('/images/logos/Toossy_logo.svg',false);
		$objDrawing->setHeight(100);
		$objDrawing->setWorksheet($sheet);
		$objDrawing->setCoordinates('A2');

		$printOptions=$sheet->getPageSetup();
		$printOptions->setFitToWidth(1);
		$printOptions->setFitToHeight(0);
		$printOptions->setHorizontalCentered(true);
		$printOptions->setVerticalCentered(false);	
		
		require_once __ROOT__ . '/libraries/PDF/mPDF/mpdf.php';


		//set PDF renderer
		$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
		$rendererLibrary = 'mPDF';
		$rendererLibraryPath = __ROOT__.'/libraries/PDF/' . $rendererLibrary;

		if(!PHPExcel_Settings::setPdfRenderer($rendererName,$rendererLibraryPath))
		{
			die('Please set the $rendererName and $rendererLibraryPath values' .
			PHP_EOL .' as appropriate for your directory structure');
		}

                
		$pdfWriter = new PHPExcel_Writer_PDF($phpExcel,'PDF');
		$pdfWriter->setSheetIndex(0);
                
		$pdfWriter->save($temp.$titreSpreadSheet.'.pdf');

		$phpExcel->disconnectWorksheets();

		unset($phpExcel);

		return array($temp,$titreSpreadSheet.'.pdf');
	}

        
        
	public function sendDocument($document=null, $emailType="Invoice")
	{            
		if(!is_null($document))
		{                    
			$mailer = JFactory::getMailer();			

			$sender=array('noreply@nactustechnology.com','Nactus Technology');	

			$mailer->setSender($sender);

			$user = JFactory::getUser();

			$recipient = $user->email;

			$mailer->addRecipient($recipient);
			$mailer->addBcc('comptabilite@nactustechnology.com');

			/*preg_match('#F-[0-9]*#',$document[1],$numberFacture);
                        preg_match('#C-[0-9]*#',$document[1],$numberCommande);*/
                        
                        $commandeNb=$this->getOrderNb();
                        $factureNb=$this->getInvoiceNb();
                        
                        if($emailType=="Invoice")
                        {
                            $mailer->setSubject(JText::_('COM_ITINERARY_INVOICE_EMAIL_TITLE').'-'.$factureNb);
			
                            $body = JText::_('COM_ITINERARY_INVOICE_EMAIL_CONTENT');
                            $mailer->setBody($body);		
                        }
                        else
                        {
                            $mailer->setSubject(JText::_('COM_ITINERARY_ORDER_EMAIL_TITLE').'-'.$commandeNb);
			
                            $body = JText::_('COM_ITINERARY_ORDER_EMAIL_CONTENT');
                            $mailer->setBody($body);		
                        }
				

			// Optional file attached
			$CGV='Nactus_Technology-CGV.pdf';
                        $notice='TOOSSY-Notice_d\'utilisation.pdf';
                        
                        $attachment=array($document[0].$document[1],$document[0].$CGV,$document[0].$notice);

			$mailer->addAttachment($attachment);


			$send = $mailer->Send();		

			unlink($document[0].$document[1]);
		}
	}
	
}