<?php
defined('_JEXEC') or die;

class ItineraryModelPrices extends JModelList
{
	public function __construct($config = array())
	{
		if(empty($config['filter_fields']))
		{
			$config['filter_fields'] = array();
		}
		//'nombre_commentaires',
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{		
		parent::populateState('p.clef_size','asc');
	}
	
	protected function getListQuery()
	{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			
			$query->select('p.clef,p.clef_size,p.duree,p.tarif');
			$query->from($db->quoteName('#__itinerary_pricing').' AS p');
			
			$query->select('s.name,s.min,s.max')
				->join('LEFT', $db->quoteName('#__itinerary_parcours_size') . ' AS s ON s.clef = p.clef_size');

			
			$orderCol = $this->state->get('list.ordering');
			$orderDirn = $this->state->get('list.direction');
			
			$query->order($db->escape($orderCol.' '.$orderDirn));
			
			return $query;
	}
	
	public function getItinerarySizeList()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('clef,min,max');
		$query->from($db->quoteName('#__itinerary_parcours_size'));
		$query->order('clef ASC');
		
		$db->setQuery($query);
		$result=$db->loadAssocList();
		
		return $result;
	}
	
	public function getItineraryDureeList()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT duree');
		$query->from($db->quoteName('#__itinerary_pricing'));
		$query->order('duree ASC');
		
		$db->setQuery($query);
		$result=$db->loadColumn();
		
		return $result;
	}
}