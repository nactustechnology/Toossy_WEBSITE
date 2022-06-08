<?php//No direct access to the filedefined('_JEXEC') or die('Restricted access');class ItineraryModelSubscriptions extends JModelList{	protected $text_prefix = 'COM_ITINERARY';	public function __construct($config = array())	{		            require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php' );            $_SESSION['parcoursMsgMax']=30;                        if(empty($config['filter_fields']))            {                $config['filter_fields'] = array(                    'clef', 's.clef',                    'date_creation','s.date_creation',                    'clef_parcours', 's.clef_parcours',                    'clef_planificateur', 's.clef_planificateur',                    'clef_parcours_size', 's.clef_parcours_size',                    'date_debut','s.date_debut',                    'date_fin','s.date_fin',                    'prix','s.prix',                    'tva_rate','s.tva_rate',                    'z.max','p.titre','p.illustrations','p.nombre_messages','p.langue','t.taux','l.title', 'l.image'                );            }            parent::__construct($config);	}	protected function populateState($ordering = null, $direction = null)	{						parent::populateState('s.commandeNb','asc');	}		protected function getListQuery()	{		$user = JFactory::getUser()->id;		if (!empty($user))		{			$db = $this->getDbo();			$query = $db->getQuery(true);			$query->select('s.clef,s.date_creation,s.commandeNb,s.clef_parcours,s.clef_planificateur,s.date_debut,s.prixHT,s.date_fin,s.duree,p.titre,p.illustrations,p.nombre_messages,p.langue,l.title AS titre_langue, l.image AS image_langue');                        $query->from($db->quoteName('#__itinerary_subscriptions') . ' AS s');			$query->join('INNER', $db->quoteName('#__itinerary_parcours') . ' AS p ON (' . $db->quoteName('s.clef_parcours') . ' = ' . $db->quoteName('p.clef') . ')');			$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON (' . $db->quoteName('p.langue') . ' = ' . $db->quoteName('l.lang_code') . ')');			$query->where('s.clef_planificateur = '.(int) $user, 'AND');						$orderCol = $this->state->get('list.ordering','s.date_creation');			$orderDirn = $this->state->get('list.direction','desc');			$query->order($db->escape($orderCol.' '.$orderDirn));						return $query;		}		else		{			return false;		}	}}