<?php
//No direct access to the file
defined('_JEXEC') or die;

class ItineraryControllerOrder extends JControllerForm
{
    public function __construct()
    {
        $_SESSION=array();
        parent::__construct();
            
            //require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php' );
    }
    
    protected function allowAdd($data = array())
    {		
            $allow = null;
            
            $allow = JFactory::getUser()->authorise('core.create', 'com_itinerary');

            if ($allow === null)
            {                        
                    // In the absence of better information, revert to the component permissions.
                    return parent::allowAdd($data);
            }
            else
            {
                    return $allow;
            }
    }
        
    public function add()
    {
            return parent::add();
    }
    
    protected function allowEdit($data = array(), $key='clef')
    {
            // Since there is no asset tracking, revert to the component permissions.
            return parent::allowEdit($data, $key);
    }
    
    
}