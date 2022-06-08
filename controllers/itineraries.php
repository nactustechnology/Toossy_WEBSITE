<?php
//No direct access to the file
defined('_JEXEC') or die;
class ItineraryControllerItineraries extends JControllerAdmin
{
	public function __construct()
	{
		parent::__construct();
		
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php' );
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
	}
	
	public function getModel($name = 'Itinerary', $prefix = 'ItineraryModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		
		return $model;
	}
	
	protected function postDeleteHook(JModelLegacy $model, $id = null)
	{
		$user = JFactory::getUser() -> id;
		$app= JFactory::getApplication();
		$idParcours = $app-> getUserState('idParcours');
		
		$folderPath = "images" . DS . "com_itinerary" . DS . $user . DS . $idParcours . DS;
		
		ItineraryHelper::viderLeDossier($folderPath,"image");
			
		if(JFolder::exists($folderPath))
		{
			JFolder::delete($folderPath);
		}
		
		$model = $this->getModel('Itineraries', 'ItineraryModel', array('ignore_request' => true));

		if(is_array($id))
		{
			foreach($id as $idParcours)
			{
				$msgList=$model->getItineraryMsgList($idParcours);
				
				if($msgList!=false && is_array($msgList))
				{
					JFactory::getApplication()->input->set('cid',$msgList);
					
					JLoader::import('messages', JPATH_ROOT . '/components/com_itinerary/controllers');
					
					$controller = new ItineraryControllerMessages;

					$controller->execute('delete');
				}
			}
		}
	}
}