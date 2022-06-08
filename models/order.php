<?php
//No direct access to the file
defined('_JEXEC') or die('Restricted access');

class ItineraryModelOrder extends JModelAdmin
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
            }

            return $data;
	}
        
        
	
	protected function populateState()
	{	
              parent::populateState();
	}
	
	public function getItem($pk = null)
	{
            $item=parent::getItem($pk);

            $user=JFactory::getUser()->id;
            $idParcours=JFactory::getApplication()->getUserState('idParcours');
            
            /*$dureeArray=array(1,3,6,12);
            $priceArray=null;
            
            foreach($dureeArray as $duree)
            {
                $tarif=$this->getAmount($duree,'Tarif');
                $priceArray[]=array('duree'=>$duree,'tarif'=>$tarif,'max'=>$_SESSION['parcoursMsgMax'],'number'=>$_SESSION['itineraryCategoryNb']);
            }
            
            $item->prices = $priceArray;*/
            
            $userCountry=$this->getUserCountry();
            
            $item->tva_rate = $this->getTvaRate($userCountry);
            $item->currency = $this->getCurrency($userCountry);
            

            return $item;
	}
        
        public function getItineraryList(){
            $user=JFactory::getUser()->id;
            
            if(!is_null($user)&&!isset($_SESSION['renewal']))
            {
                $today = date('Y-m-d');
                
                $db=$this->getDbo();
                $query = $db->getQuery(true);
                $query->select('p.clef as clef,p.titre');
                $query->from('#__itinerary_parcours as p');
                $query->join('left', '#__itinerary_subscriptions AS sb ON p.clef = sb.clef_parcours ');
                $query->where('p.clef_planificateur ='.(int)$user, 'AND');
                $query->where('sb.clef_planificateur IS NULL OR sb.date_fin < "'.$today.'"');
                $query->group('p.clef');
                
                $db->setQuery($query);
                $result=$db->loadAssocList();
                
                if(!empty($result))
                {
                    return $result;
                }
            }
            else if(!is_null($user)&&isset($_SESSION['renewal']))
            {
                $db=$this->getDbo();
                
                $itineraryIdArray=array();
                
                foreach($_SESSION['renewal'] as $renewal)
                {
                    $renewalArray=explode('_',$renewal);
                    
                    array_push($itineraryIdArray,$renewalArray[0]);
                }
                
                
                $query = $db->getQuery(true);
                $query->select('clef,titre');
                $query->from('#__itinerary_parcours');
                $query->where('clef_planificateur ='.(int)$user, 'AND');
                $query->where('clef IN('.implode(",",$itineraryIdArray).')');

                
                $db->setQuery($query);
                $result=$db->loadAssocList();
                
                if(!empty($result))
                {
                    return $result;
                }
            }

            return array();
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
        
        public function countSubscriptionsGroupByDuration()
        {
            $user = JFactory::getUser()->id;
            $today = date("Y-m-d");
            
            if (!empty($user))
            {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query -> select('duree, COUNT(clef) as subscriptionQty');
                $query -> from($db->quoteName('#__itinerary_subscriptions'));
                $query -> where('clef_planificateur ='.(int)$user,'AND');
                $query -> where('date_debut <= "'.$today.'"','AND');
                $query -> where('date_fin >= "'.$today.'"','AND');
                $query -> group('duree');          
                $db -> setQuery($query);
                $result = $db -> loadAssocList();
                
                if(empty($result))
                {
                    return array();
                }
                else
                {
                    return $result;
                }
            }
            
            return null;
        }
        
        public function getPricing()
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query -> select('clef_size, duree, tarif');
            $query -> from($db->quoteName('#__itinerary_pricing'));    

            $db -> setQuery($query); 
            $result = $db -> loadAssocList();
                
            if(empty($result))
            {
                return null;
            }
            else
            {   
                
                return $result;
            }
        }
}