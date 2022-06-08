<?php
//No direct access to the file
defined('_JEXEC') or die;

class ItineraryControllerMessages extends JControllerAdmin
{
	public function __construct()
	{
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php' );
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		parent::__construct();
	}
	
	public function getModel($name = 'Message', $prefix = 'ItineraryModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		
		return $model;
	}
	
	public function saveOrderAjax()
	{
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid',array(),'array');
		$order = $input->post->get('order',array(),'array');

		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);
		
		$model = $this->getModel();
		
		$return = $model->saveorder($pks, $order);

		if($return)
		{
			echo "1";
		}
		
		ItineraryHelper::setLatLngParcours($model,$idParcours);
		
		if(ItineraryHelper::checkSubscription())
		{
			$modelMessages=$this->getModel('Messages');
			
			$modelMessages->updateDataInProduction($pks,$order);
		}
		
		JFactory::getApplication()->close();
	}
	
	
	protected function postDeleteHook(JModelLegacy $model, $cid= null )
	{
		$user = JFactory::getUser() -> id;
		$app= JFactory::getApplication();
		$idParcours = $app-> getUserState('idParcours');
		
		$folderPath = "images" . DS . "com_itinerary" . DS . $user . DS . $idParcours . DS;
	
		foreach($cid AS $message)
		{
			ItineraryHelper::viderLeDossier($folderPath.$message,"image");
			
			if(JFolder::exists($folderPath.$message))
			{
				JFolder::delete($folderPath.$message);
			}
		}
			
		$model = $this->getModel($name = 'Messages', $prefix = 'ItineraryModel', $config = array('ignore_request' => true));

		ItineraryHelper::setNbMsgParcours($model,$idParcours);
		
		ItineraryHelper::setLatLngParcours($model,$idParcours);
		
		parent::postDeleteHook($model, $cid);
	}
}