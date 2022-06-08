<?php

defined('_JEXEC') or die('Restricted access');

class ItineraryModelFeedbacks extends JModelList
{
    protected $text_prefix = 'COM_ITINERARY';

    public function __construct($config = array())
    {		
        //require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php' );

        /*if(empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(  );
        }*/
        parent::__construct($config);
    }



    protected function populateState($ordering = null, $direction = null)
    {				
            parent::populateState('date_creation','desc');
    }



    protected function getListQuery($tabName='evaluation')
    {
        $user = JFactory::getUser()->id;

        $app= JFactory::getApplication();
        $idParcours = $app-> getUserState('idParcours');

        if (!empty($user)&& !empty($idParcours))
        {
            $orderCol = $this->state->get('list.ordering','e.date_creation');
            $orderDirn = $this->state->get('list.direction','desc');
            
            /*if($tabName=='evaluation')
            {*/

            /*}
            else 
            {
                $fieldsList = 'e.date_creation, e.commentaire';
                $$tableName = '#__itinerary_signalement_parcours';
            }*/

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('e.date_creation, e.commentaire, e.note');
            $query->from($db->quoteName('#__itinerary_evaluation_parcours') . ' AS e');
            $query->where('e.clef_parcours = '.(int) $idParcours);
            $query->order($db->escape($orderCol.' '.$orderDirn));			

            return $query;
        }
        else
        {
                return false;
        }
    }
    
    public function getReportList()
    {
        $user = JFactory::getUser()->id;

        $app= JFactory::getApplication();
        $idParcours = $app-> getUserState('idParcours');

        if (!empty($user)&& !empty($idParcours))
        {
            $orderCol = $this->state->get('list.ordering','e.date_creation');
            $orderDirn = $this->state->get('list.direction','desc');

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('s.date_creation, s.commentaire, m.ordering, m.titre');
            $query->from($db->quoteName('#__itinerary_signalement_parcours') . ' AS s') 
                ->join('INNER', $db->quoteName('#__itinerary_messages') . ' AS m ON m.clef = s.clef_message');
            $query->where('m.clef_parcours = '.(int) $idParcours);
            $query->order($db->escape($orderCol.' '.$orderDirn));			
            $db->setQuery($query);
            
            $result=$db->loadAssocList();
            
            return $result;
        }
        else
        {
                return null;
        }
    }
}