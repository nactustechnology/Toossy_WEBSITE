<?php
//No direct access to the file
defined('_JEXEC') or die;

class ItineraryControllerMessage extends JControllerForm
{
	public function __construct()
	{
		parent::__construct();
		
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php' );
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$this->registerTask('deleteimage','deleteimage');
	}

	protected function allowAdd($data = array())
	{
		$model = $this->getModel('message');
		$idParcours = JFactory::getApplication()->getUserState('idParcours');
		
		if(!is_null($model) && !empty($idParcours))
		{
			$numberMsg=ItineraryHelper::getNbMsgParcours($model, $idParcours);
			$numberMsg=$numberMsg+1;
			
			$numberMsgLimit=30;
			
			if(!is_null($numberMsg) && !is_null($numberMsgLimit) && $numberMsg<=$numberMsgLimit)
			{
				return parent::allowAdd($data);
			}
			else
			{
				JFactory::getApplication()->setUserState('limitExceeded',JText::_('COM_ITINERARY_MSG_LIMIT_EXCEEDED'));
			}
		}
	}
	
	protected function allowEdit($data = array(), $key='clef')
	{
		return parent::allowEdit($data, $key);
	}
	
	
	public function deleteImage()
	{
		$app = JFactory::getApplication();
		
		$user = JFactory::getUser()->get('id');		
		$idParcours = $app->input->get('jform');
		$idParcours = $idParcours[2];			
		
		$message = $app->input->get('jform');
		$message = $message[0];
		
		$model = $this -> getModel('message');

		$folderPath = JPATH_BASE . DS . "images" . DS . "com_itinerary" . DS . $user . DS . $idParcours . DS . $message;
		
		ItineraryHelper::viderLeDossier($folderPath,"image");
		JFolder::delete($folderPath);
		
		ItineraryHelper::deleteImagePath($model,"message","image",$user,$idParcours,$message,'edition');
		
		if($model->getParcoursActivation()==1)
		{
			$folderPath2 = DS . $user . DS . $idParcours . DS . $message . DS . 'leurre.png';
			
			ItineraryHelper::deleteImagePath($model,"message","image",$user,$idParcours,$message,'production');
			ItineraryHelper::removeImageFromProduction($folderPath2, "Message");
		}
		
		$this->setRedirect(			
			JRoute::_('index.php?option=com_itinerary&view=message&layout=edit&clef='.$message, false)
		);
	}

	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		//Create Applicatiion object
		$app = JFactory::getApplication();
		//Create Model object
		$model = $this -> getModel('message');
		
		$file = $app->input->files->get('jform');
		$file = $file["illustrations"];
		$file = $file["name"];
		
		if($model->checkSubscription()===true&&$model->getParcoursActivation()==1)
		{
			$newData=$validData;
			
			if($newData['activation_planificateur'])
			{
				if(!empty($newData['clef']))
				{
					if($model->isMsgInProduction($newData['clef']))
					{
						$model->updateDataInProduction($newData,'Message_prod');
					}
					else
					{
						$model->putMsgInProduction($newData);
					}
				}
				else
				{
					$model->putMsgInProduction($newData);
				}
			}
			else
			{
				if(!empty($newData['clef']))
				{
					$model->removeMsgInProduction($newData['clef']);
				}
			}
		}
		
		if($file!=null)
		{
			ItineraryHelper::uploadFile($app,$model,'message','image',$validData);
		}
		
		if( isset($validData['clef_parcours']) && !empty($validData['clef_parcours']) && empty($validData['clef']))
		{
			$idParcours=$validData['clef_parcours'];
		
			$model = $this->getModel($name = 'Message', $prefix = 'ItineraryModel', $config = array('ignore_request' => true));
				
			ItineraryHelper::numeroterMessage($model,$idParcours);
			
			ItineraryHelper::setNbMsgParcours($model,$idParcours);
			
			ItineraryHelper::setLatLngParcours($model,$idParcours);
		}
		elseif(isset($validData['clef_parcours']) && !empty($validData['clef_parcours']))
		{
			$idParcours=$validData['clef_parcours'];
		
			$model = $this->getModel($name = 'Message', $prefix = 'ItineraryModel', $config = array('ignore_request' => true));

			ItineraryHelper::setLatLngParcours($model,$idParcours);
		}

		return parent:: postSaveHook($model, $validData = array());
	}
	
	public function setMessage($text, $type = 'message')
	{
		if(!is_null(JFactory::getApplication()->getUserState('limitExceeded')))
		{
			$text = JFactory::getApplication()->getUserState('limitExceeded');
		}
		
		JFactory::getApplication()->setUserState('limitExceeded',null);
		
		return parent::setMessage($text, 'message');
	}
}
	
	
	