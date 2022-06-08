<?php
//No direct access to the file
defined('_JEXEC') or die;
class ItineraryControllerPrices extends JControllerAdmin
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getModel($name = 'Prices', $prefix = 'ItineraryModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		
		return $model;
	}
}