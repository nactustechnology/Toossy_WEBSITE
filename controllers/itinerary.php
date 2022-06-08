<?php
//No direct access to the file
defined('_JEXEC') or die;

class ItineraryControllerItinerary extends JControllerForm
{
	public function __construct()
	{
		parent::__construct();
		
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php' );
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$this->registerTask('deleteimage','deleteimage');
		$this->registerTask('itineraryApplyCloseToMessages', 'itineraryApplyCloseToMessages');
		$this->registerTask('checkItinerarySubscription','checkItinerarySubscription');
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
		JFactory::getApplication()->setUserState('idParcours',null);
		
		return parent::add();
	}
	
	protected function allowEdit($data = array(), $key='clef')
	{
		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}
	
	public function deleteImage()
	{		
		$app = JFactory::getApplication();
		
		$user = JFactory::getUser()->get('id');		
		$idParcours = $app->input->get('jform');
		$idParcours = $idParcours[0];
				
		$model = $this -> getModel('itinerary');
		$folderPath = JPATH_BASE . DS . "images" . DS . "com_itinerary" . DS . $user . DS . $idParcours;
		
		$test=ItineraryHelper::viderLeDossier($folderPath,"image");	
		ItineraryHelper::deleteImagePath($model,"parcours","image",$user,$idParcours,null,"edition");
		
		if($model->getParcoursActivation($model)==1)
		{
			$folderPath2 = DS . $user . DS . $idParcours . DS . 'leurre.png';
			
			ItineraryHelper::deleteImagePath($model,"parcours","image",$user,$idParcours,null,"production");
			ItineraryHelper::removeImageFromProduction($folderPath2, "Itinerary");
		}
	
		$this->setRedirect(
			JRoute::_('index.php?option=com_itinerary&view=itinerary&layout=edit&clef='.$idParcours, false)
		);
	}
	
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		//Create Applicatiion object
		$app = JFactory::getApplication();
		//Create Model object
		$model = $this -> getModel('itinerary');

                $file = $app->input->files->get('jform');
		$file = $file["illustrations"];
		$file = $file["name"];
		
		if($file!=null)
		{
                    ItineraryHelper::uploadFile($app,$model,'parcours','image',$validData);
		}
		
  
		if($model->checkSubscription()===true&&$model->getParcoursActivation()==1)
		{
			$newData=$validData;
			
			$model->updateDataInProduction($newData,'Itinerary_prod');
		}
		
		if($model->checkSubscription()===true&&ItineraryHelper::getParcoursPriceAccess()==1)
		{
			ItineraryHelper::createUserAccount();
		}
		
		
		
		return parent::postSaveHook($model, $validData = array());
	}
	
	public function itineraryApplyCloseToMessages()
	{
		$this -> execute('apply');
		
		$this -> view_list = 'messages';
		
		$this -> execute('cancel');
	}
	
	public function setMessage($text, $type = 'message')
	{
		
		if(!is_null(JFactory::getApplication()->getUserState('noSubscription')))
		{
			$text = $text.' '.JFactory::getApplication()->getUserState('noSubscription');
		}
		
		JFactory::getApplication()->setUserState('noSubscription',null);
		
		return parent::setMessage($text, $type);
	}

}