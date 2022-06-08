<?php
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view');

class itineraryViewitinerarygmap extends JViewLegacy
{
	protected $latitude;
	protected $longitude;
	protected $type;
	protected $p;
	
	public function display($tpl = null) {
		
		$document = JFactory::getDocument();
		$document->addCustomTag( "<style type=\"text/css\"> \n" 
			." html,body, .contentpane{overflow:hidden;background:#ffffff;} \n" 
			." </style> \n");
		
		parent::display($tpl);
	}
}