<?php

defined('_JEXEC') or die;

class ItineraryHelper
{
	private function __construct($config = array())
	{
		if(empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'clef', 'm.clef', 'a.clef',
				'clef_planificateur', 'm.clef_planificateur', 'a.clef_planificateur',
				'clef_parcours', 'm.clef_parcours',
				'clef_planificateur','clef_parcours','clef_message','image_claire','image_cryptee'
			);
		}
		
		parent::__construct($config);
	}

	private static function isItineraryPlanificateur($model,$idParcours)
	{
		$user = JFactory::getUser()->id;
		$result=null;
		
		if(!empty($user)&&!empty($idParcours))
		{
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			$query -> select('a.clef');
			$query -> from($db->quoteName('#__itinerary_parcours').' AS a');
			$query -> where('a.clef_planificateur = '.(int) $user, 'AND');
			$query -> where('a.clef = '.(int) $idParcours);
			
			$db->setQuery($query);
			
			$result=$db->loadResult();

			if(!is_null($result))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	private static function isMessagePlanificateur($model,$idParcours,$message)
	{
		$user = JFactory::getUser() -> id;
		$result=null;
		
		if($message==null)	
		{
			return true;
		}
		else
		{
			if(!empty($user)&&!empty($idParcours)&&!empty($message))
			{
				$db = $model -> getDbo();
				
				$query = $db -> getQuery(true);
				$query -> select('m.clef');
				$query -> from($db->quoteName('#__itinerary_messages').' AS m');
				$query -> where('m.clef_planificateur = '.(int) $user, 'AND');
				$query -> where('m.clef_parcours = '.(int) $idParcours, 'AND');
				$query -> where('m.clef = '.(int) $message);
				
				$db->setQuery($query);
				
				$result=$db->loadResult();
				
				if(is_null($result))
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			else
			{
				return false;
			}
		}
	
	}
	
	public static function deleteImagePath($model,$leveltype,$mediatype,$user,$idParcours,$message,$table_type)
	{
		if( !is_null($model) && ( $mediatype=='image' || $mediatype=='audio' ) )
		{
			if($leveltype=='message')
			{
				$table = '#__itinerary_messages';
			}
			elseif($leveltype=='parcours')
			{
				$table = '#__itinerary_parcours';
			}
			else
			{
				return false;
			}
			
			if($mediatype=='image')
			{
				$fields = 'titre_illustrations = "", illustrations = ""';
			}
			elseif($mediatype=='audio')
			{
				$fields = 'titre_audio = "", audio = ""';
			}
			else
			{
				return false;
			}
			
			
			if($table_type=='production')
			{
				$table=str_replace('#__','',$table).'_prod';
			}
			
			if($table_type=='production')
			{
				$db = ItineraryHelper::connectToAnotherDB();
			}
			else
			{
				$db = $model -> getDbo();
			}

			$query = $db->getQuery(true);
			$query->update($table);
			$query->set($fields);
			
			if($table_type!='production')
			{
				$query -> where('clef_planificateur = '.(int) $user, 'AND');
			}
			
			if($leveltype=='message')
			{
				$query->where('clef_parcours = '.(int) $idParcours, 'AND');
				$query->where('clef = '.(int) $message);
			}
			else
			{
				$query->where('clef = '.(int) $idParcours);
			}

			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		else
		{
			return false;
		}
		
	}
	
	private static function getParcoursActivation($model)
	{
		$user=JFactory::getUser()->id;
		$idParcours=JFactory::getApplication()->getUserState('idParcours');
		
		if(!empty($user) && !empty($idParcours))
		{
			$db = JFactory::getDbo();
			$query = $db -> getQuery(true);
			$query->select('activation_planificateur');
			$query->from($db->quoteName('#__itinerary_parcours'));
			$query->where('clef ='.(int)$idParcours, 'AND');
			$query->where('clef_planificateur ='.(int)$user);

			$db->setQuery($query);
			$result=$db->loadResult();

			if(!is_null($result))
			{
				return $result;
			}
			else
			{
				return null;
			}
		}
	}
	
	public static function viderLeDossier($folderPath,$mediatype)
	{		
		if($mediatype=='image') //si le dossier existe alors on regarde s'il existe déjà une image dedans et on la supprime
		{
			$folderImage = null;
			$folderImage = glob($folderPath.'/*.jpg'); //get all image names
			
			foreach($folderImage as $image)
			{
				if(is_file($image))
					JFile::delete($image); //delete image
			}
			
			$folderImage = null;
			$folderImage = glob($folderPath.'/*.jpeg'); //get all image names
			
			foreach($folderImage as $image)
			{
				if(is_file($image))
					JFile::delete($image); //delete image
			}
			
			$folderImage = null;
			$folderImage = glob($folderPath.'/*.png'); //get all image names
			
			foreach($folderImage as $image)
			{
				if(is_file($image))
					JFile::delete($image); //delete image
			}
			
			return true;
		}
		elseif($mediatype=='audio')
		{
			$folderAudio = null;
			$folderAudio = glob($folderPath.'/*.mpg'); //get all audio names
			
			foreach($folderAudio as $audio){
				if(is_file($audio))
					JFile::delete($audio); //delete audio
			}
			
			$folderAudio = null;
			$folderAudio = glob($folderPath.'/*.wav'); //get all audio names
			
			foreach($folderAudio as $audio){
				if(is_file($audio))
					JFile::delete($audio); //delete audio
			}
			
			$folderAudio = null;
			$folderAudio = glob($folderPath.'/*.wma'); //get all audio names
			
			foreach($folderAudio as $audio){
				if(is_file($audio))
					JFile::delete($audio); //delete audio
			}
			
			
			return true;
		}
		else
		{
			return false;
		}
		
	}
	
	private static function selectLastItineraryId($model)
	{
		$user = JFactory::getUser() -> id;
		
		$db = $model -> getDbo();

		$query = $db -> getQuery(true);
		$query -> select('p.clef');
		$query -> from($db->quoteName('#__itinerary_parcours').' AS p');
		$query -> where('p.clef_planificateur = '.(int) $user);
		$query -> order('p.date_creation DESC');
		
		$db->setQuery($query);
		
		$result=$db->loadResult();
				
		if(is_null($result))
		{
			return false;
		}
		else
		{
			return $result;
		}
	}
	
	private static function selectLastMessageId($model,$idParcours)
	{
		$user = JFactory::getUser() -> id;
		
		$db = $model -> getDbo();

		$query = $db -> getQuery(true);
		$query -> select('m.clef');
		$query -> from($db->quoteName('#__itinerary_messages').' AS m');
		$query -> where('m.clef_planificateur = '.(int) $user, 'AND');
		$query -> where('m.clef_parcours = '.(int) $idParcours);
		$query -> order('m.clef DESC');
				
		$db->setQuery($query);
		
		$result=$db->loadResult();
				
		if(is_null($result))
		{
			return false;
		}
		else
		{
			return $result;
		}
	}

	private static function saveImagePathAndName($model,$leveltype,$mediatype,$titre_illustrations_audio,$illustrations_audio,$idParcours,$message,$table_type)
	{
		$user = JFactory::getUser() -> id;
		
		if($table_type=='production')
		{
			$db = ItineraryHelper::connectToAnotherDB();
		}
		else
		{
			$db = $model -> getDbo();
		}
		
		$query = $db -> getQuery(true);
		
		
		if($leveltype=='message')
		{
			$table = '#__itinerary_messages';
		}
		elseif($leveltype=='parcours')
		{
			$table = '#__itinerary_parcours';
		}
		else
		{
			return false;
		}
		
		if($table_type=='production')
		{
			$table=str_replace('#__','',$table).'_prod';
		}
		
		if($mediatype=='image')
		{
			$fields = 'titre_illustrations = "'.$titre_illustrations_audio.'", illustrations = "'.$illustrations_audio.'"';
		}
		elseif($mediatype=='audio')
		{
			$fields = 'titre_audio = "'.$titre_illustrations_audio.'", audio = "'.$illustrations_audio.'"';
		}
		else
		{
			return false;
		}
		
		
		$query -> update($table);
		$query->set($fields);
		
		if($table_type!='production')
		{
			$query -> where('clef_planificateur = '.(int) $user, 'AND');
		}

		if($leveltype=='message')
		{
			$query -> where('clef_parcours = '.(int) $idParcours, 'AND');
			$query -> where('clef = '.(int) $message);
		}
		else
		{
			$query -> where('clef = '.(int) $idParcours);
		}

		$db->setQuery($query);
		$db->execute();
	}

	
	private static function ejectiondusite()
	{		
		$componentUrl = JUri::root().'gestion-des-parcours';
		$urlLogin = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($componentUrl));
		
		//get the session object
		$session = JSession::getInstance('database', array()) ;
		$session -> destroy();
		
		$app = JFactory::getApplication();
		
		$app -> redirect($urlLogin);
		$app -> logout();
		exit;
	}
	
	public static function uploadFile($app,$model,$leveltype,$mediatype,$validData)
	{
		//Retrieve file details from uploaded file, sent from upload form
		$file = $app->input->files->get('jform');
		$file = $file['illustrations'];
		
		//get the file type
		$filetype = $file['type'];
		
		//get the file's size
		$filesize = $file['size'];
		
		//Set up the source of the file
		$src = $file['tmp_name'];
		
		//get the true content type file
		$trueContentType = mime_content_type($src);
		
		//Clean up filename to get rid of strange characters like spaces etc
		$filename = JFile::makeSafe($file['name']);
		$encryptedFileName = JUserHelper::genRandomPassword(32);
		
		//get the true extension file
		$trueExtension = strtolower(JFile::getExt($filename));

		//get the user's, requester's, itinerary's and message's id
		$user = JFactory::getUser()->id;
		
		//set problem variable
		$problemRequest=false;
		
		if($leveltype=='message')
		{
			$idParcours = $app->getUserState('idParcours');
		
		}
		elseif($leveltype=='parcours')
		{
			if(isset($validData['clef']) && !empty($validData['clef']))
			{
				$idParcours = $validData['clef'];
			}
		}
		else
		{
			$problemRequest=true;
		}
		
		if(empty($idParcours))
		{
			$idParcours = ItineraryHelper::selectLastItineraryId($model);
			$app->setUserState('idParcours',$idParcours);
		}
		
		$message = $app->input->post->get('jform');
		$message = $message[0];
		
		if(empty($message))
		{
			$message = ItineraryHelper::selectLastMessageId($model,$idParcours);
		}
		
		$requester = $app->input->get('jform');
		$requester = $requester[0];
		
		//set the problem variable if true then the user is ejected	
		if(empty($user) || (empty($idParcours) && $leveltype=='message'))
		{
			$problemRequest=true;
		}
		elseif(!empty($idParcours) && ItineraryHelper::isItineraryPlanificateur($model,$idParcours)==false)
		{//Le parcours existe et le user n'en est pas l'auteur
			$problemRequest=true;
		}
		elseif($leveltype=='message' && !empty($message) && ItineraryHelper::isMessagePlanificateur($model,$idParcours,$message)==false)
		{
			$problemRequest=true;
		}
		elseif($user != $requester)//Check if the requester is hidding his real id
		{
			$problemRequest=true;
		}
		elseif($trueContentType != $filetype)//check if the user try to send a fake file
		{
			$problemRequest=true;
		}
		elseif(empty($idParcours) || (!empty($idParcours) && empty($idParcours)))
		{
			$problemRequest=true;
		}
			
			

		//Eject the user if something does not look honest
		if($problemRequest==true)
		{
			//ItineraryHelper::ejectiondusite();
		}
		
		//Check if the Planificateur's folder exist
		$folderPath = JPATH_BASE . DS . 'images' . DS . 'com_itinerary' . DS . $user;

		if(!JFolder::exists($folderPath))
		{
			JFolder::create($folderPath);
			
			//JFile::copy(JPATH_SITE.'/media/index.html');
		}
		
		//Check if the Itenerary's folder exist
		$folderPath = $folderPath . DS . $idParcours;
		
		if(!JFolder::exists($folderPath))
		{
			JFolder::create($folderPath);
			
			//JFile::copy(JPATH_SITE.'/media/index.html');
		}
		elseif($leveltype=='parcours')
		{
			//si le fichier existe alors il est vidé
			ItineraryHelper::viderLeDossier($folderPath,$mediatype);
		}
                
                if(!JFile::exists($folderPath . DS . "index.html"))
                {
                    $index = fopen($folderPath . DS . "index.html", "w+");
                    fwrite($index, '<!DOCTYPE html><title></title>');
                    fclose($index);
                }
		
                
		if($leveltype=='message')
		{
			//Check if the Message'sfolder exist if leveltype = message
			$folderPath = $folderPath . DS . $message;
			
			if(!JFolder::exists($folderPath))
			{
				JFolder::create($folderPath);
				
                                
                                
				//JFile::copy(JPATH_SITE.'/media/index.html');
			}
			else
			{
				//si le fichier existe alors il est vidé
				ItineraryHelper::viderLeDossier($folderPath,$mediatype);
				
			}
		}

                if(!JFile::exists($folderPath . DS . "index.html"))
                {
                    $index = fopen($folderPath . DS . "index.html", "w+");
                    fwrite($index, '<!DOCTYPE html><title></title>');
                    fclose($index);
                }

	
		//Set up the destination of the file
		$dest = $folderPath . DS . 'main_' . $encryptedFileName . '.' . $trueExtension;
		

		if(!empty($file['tmp_name']))
		{
			//First check if the file has the right extension
			if ( ($trueExtension == 'jpg' || $trueExtension == 'png' || $trueExtension == 'jpeg') && $filesize <= 610000)
			{
				$handle = fopen($filename, 'r');
				$error = false;
				
				if ($handle)
				{
					while (!feof($handle))
					{
						$buffer = fgets($handle);
						
						switch (true)
						{
							case strstr($buffer,'<'):
								$error = true;
								break;
							case strstr($buffer,'>'):
								$error = true;
								break;
							case strstr($buffer,';'):
								$error = true;
								break;
							case strstr($buffer,'&'):
								$error = true;
								break;
							case strstr($buffer,'?'):
								$error = true;
								break;
						}
					}
				}
				
				fclose($handle);
				
				if(!$error)
				{
					if (JFile::upload($src,$dest,false,false,true))
					{
						if($trueExtension == 'jpg' || $trueExtension == 'jpeg')
						{
							$source = imagecreatefromjpeg($dest);
						}
						else
						{
							$source = imagecreatefrompng($dest);
						}
							
						
						$source_width = imagesx($source);
						$source_height = imagesy($source);
						
						if($source_width < $source_height)
						{
							$destination_width = 100 * $source_width / $source_height;
							$destination_height = 100;
						}
						elseif($source_width > $source_height)
						{
							$destination_width = 100;
							$destination_height = 100 * $source_height / $source_width;
						}
						elseif($source_width == $source_height)
						{
							$destination_width = 100;
							$destination_height = 100;
						}
						
						$destination = imagecreatetruecolor($destination_width,$destination_height);
						
						imagecopyresampled( $destination , $source , 0 , 0 , 0 , 0 , $destination_width , $destination_height , $source_width , $source_height );

						if($trueExtension == 'jpg' || $trueExtension == 'jpeg')
						{
							imagejpeg($destination, $folderPath . DS . 'thumb_' . $encryptedFileName . '.' . $trueExtension);
						
							
							/*if(filesize($dest) > 1048576)
							{
								$img = imagecreatefromjpeg($dest);
								
								imagejpeg($img,$dest,75);

								sleep(1);
								clearstatcache();							
							}*/
						}
						elseif($trueExtension == 'png')
						{
							imagepng($destination, $folderPath . DS . 'thumb_' . $encryptedFileName . '.' . $trueExtension);
							
							/*if(filesize($dest) > 1048576)
							{
								$img = imagecreatefrompng($dest);
								
								imagepng($img,$dest,3);
								
								sleep(1);
								clearstatcache();
							}*/
						}
						
						
						if($leveltype=='message')
						{
							$illustrations = $user . DS . $idParcours . DS . $message . DS . 'main_' . $encryptedFileName . '.' . $trueExtension;
						}
						else
						{
							$illustrations = $user . DS . $idParcours . DS . 'main_' . $encryptedFileName . '.' . $trueExtension;
						}
						
						$filename = $file['name'];
						
						$titre_illustrations = ucwords(JFile::stripExt($filename));
						$titre_illustrations = htmlspecialchars_decode($titre_illustrations,ENT_QUOTES);						
						
						ItineraryHelper::saveImagePathAndName($model,$leveltype,$mediatype,$titre_illustrations,$illustrations,$idParcours,$message,'edition');
						
						if(ItineraryHelper::getParcoursActivation($model)==1)
						{
							ItineraryHelper::saveImagePathAndName($model,$leveltype,$mediatype,$titre_illustrations,$illustrations,$idParcours,$message,'production');
							ItineraryHelper::putImageInProduction($illustrations);
						}
					}
					else
					{
						//Redirect and throw an error message
						$errSaveMsg = JText::_('COM_ITINERARY_IMAGE_NOT_LOADED');
						$app -> enqueueMessage($errSaveMsg);
					}
				}
				else
				{
					ItineraryHelper::ejectiondusite();
				}
			}
			else 
			{
			   //Redirect and notify user file is not right extension
				$errSaveMsg = JText::_('COM_ITINERARY_IMAGE_NOT_LOADED');
				$app -> enqueueMessage($errSaveMsg);
				$errSaveMsg = JText::_('COM_ITINERARY_IMAGE_CARACTERISTICS');
				$app -> enqueueMessage($errSaveMsg);
			}
		}
	}
	
	public static function numeroterMessage($model=null,$idParcours=null)
	{
		if (!empty($model) && !empty($idParcours))
		{
				$user = JFactory::getUser()->id;
			
				$db = JFactory::getDbo();
				
				$query = $db->getQuery(true);
				$query->select('MAX(m.ordering)');
				$query->from($db->quoteName('#__itinerary_messages').' AS m');
				$query->where('m.activation_administrateur = 1', 'AND');
				$query->where('m.activation_planificateur = 1', 'AND');
				$query->where('m.clef_planificateur = '.(int) $user, 'AND');
				$query->where('m.clef_parcours = '.(int) $idParcours);
				
				$db->setQuery($query);
			
				$maxOrdering =$db->loadResult();
				
				if($maxOrdering!=null)
				{
					$numeroMessage = (int)$maxOrdering + 1;
					ItineraryHelper::setNumNewMessage($model,$idParcours,$numeroMessage,'edition');
				}
				else
				{
					return false;
				}
		}
		else
		{
			return false;
		}
	}
	
	protected static function setNumNewMessage($model=null,$idParcours=null,$numeroMessage=null,$table_type=null)
	{
		if(!empty($model) && !empty($idParcours) && !empty($numeroMessage) && !empty($table_type))
		{
			$user = JFactory::getUser()->id;
			
			if($table_type=='edition')
			{
				$table='#__itinerary_messages';
			}
			else
			{
				$table='#__itinerary_messages_prod';
			}
			
			$db = $model -> getDbo();
					
			$query = $db -> getQuery(true);
			$query->select('MAX(m.date_creation)');
			$query->from($table.' AS m');
			
			if($table_type=='edition')
			{
				$query->where('m.clef_planificateur = '.(int) $user,'AND');
			}

			$query->where('m.clef_parcours = '.(int) $idParcours);

			$db->setQuery($query);
			$newMsg=$db->loadResult();
			
			if($newMsg)
			{
				$query = $db -> getQuery(true);
				$query->update($table.' AS m');
				$query->set('m.ordering = "'.$numeroMessage.'"');
				
				if($table_type=='edition')
				{
					$query->where('m.clef_planificateur = '.(int) $user,'AND');
				}
				
				$query->where('m.clef_parcours = '.(int) $idParcours,'AND');
				$query->where('m.date_creation = "'.$newMsg.'"');
				
				$db->setQuery($query);
				$db->execute();
			}
		}	
	}

	
	public static function setNbMsgParcours($model=null,$idParcours=null)
	{
		if($model!=null && $idParcours!=null)
		{
			$nombremessages = ItineraryHelper::getNbMsgParcours($model,$idParcours);		
			
			if(!is_null($nombremessages))
			{
				$user = JFactory::getUser()->id;
				
				$db = $model -> getDbo();
			
				$query = $db -> getQuery(true);
				$query->update('#__itinerary_parcours AS p');
				$query->set('p.nombre_messages = "'.(int)$nombremessages.'"');
				$query->where('p.clef_planificateur = '.(int)$user,'AND');
				$query->where('p.clef = '.(int)$idParcours);

				$db->setQuery($query);
				$db->execute();
			}
		}
	}
	
	public static function getNbMsgParcours($model=null, $idParcours=null)
	{				
		
		if($model!=null && $idParcours!=null)
		{	
			$user = JFactory::getUser()->id;
			
			$db = $model -> getDbo();

			$query = $db -> getQuery(true);
			$query -> select('COUNT(*)');
			$query -> from($db->quoteName('#__itinerary_messages').' AS m');
			//$query -> where('m.activation_administrateur = 1', 'AND');
			//$query -> where('m.activation_planificateur = 1', 'AND');
			$query -> where('m.clef_planificateur = '.(int)$user, 'AND');
			$query -> where('m.clef_parcours = '.(int)$idParcours);
					
			$db->setQuery($query);
			
			$result=$db->loadResult();
			
			
			
			if($result == null || $result==0)
			{				
				return 0;
			}
			else
			{
				return $result;
			}
		}
		else
		{
				return null;
		}
	}
	
	public static function setLatLngParcours($model=null,$idParcours=null)
	{
		
		if($idParcours==null)
		{
			$idParcours = JFactory::getApplication()->getUserState('idParcours');
		}

		if($model!=null && $idParcours!=null)
		{
			$user = JFactory::getUser()->id;
			
			$db = $model -> getDbo();
					
			$query = $db -> getQuery(true);
			$query->select('MIN(m.ordering)');
			$query->from('#__itinerary_messages AS m');
			$query->where('m.clef_planificateur = '.(int) $user,'AND');
			$query->where('m.clef_parcours = '.(int) $idParcours);

			$db->setQuery($query);
			$firstMsg=$db->loadResult();
			
			if($firstMsg!=null)
			{
				$db = $model -> getDbo();
				
				$query = $db -> getQuery(true);
				$query->select('m.latitude, m.longitude');
				$query->from('#__itinerary_messages AS m');
				$query->where('m.clef_planificateur = '.(int) $user,'AND');
				$query->where('m.clef_parcours = '.(int) $idParcours,'AND');
				$query->where('m.ordering = '.(int) $firstMsg);
				
				$db->setQuery($query);
				$latLngFirstMsg=$db->loadAssoc();			
				
				if($latLngFirstMsg!=null)
				{	
					$latitude = $latLngFirstMsg['latitude'];
					$longitude = $latLngFirstMsg['longitude'];	
					$query = $db -> getQuery(true);
					$query->update('#__itinerary_parcours AS p');
					$query->set('p.latitude = '.$latitude);
					$query->set('p.longitude = '.$longitude);
					$query->where('p.clef_planificateur = '.(int)$user,'AND');
					$query->where('p.clef = '.(int)$idParcours,'AND');

					$db->setQuery($query);
					$db->execute();
					
					if(ItineraryHelper::checkSubscription()===true)
					{
						$db = ItineraryHelper::connectToAnotherDB();
						$query = $db -> getQuery(true);
						$query->update('#__itinerary_parcours_prod AS p');
						$query->set('p.latitude = '.$latitude);
						$query->set('p.longitude = '.$longitude);
						$query->where('p.clef = '.(int)$idParcours,'AND');

						$db->setQuery($query);
						$db->execute();
					}
				}
			}
				
		}
	}
	
        public static function checkSubscription()
	{
		$user=JFactory::getUser()->id;
		$idParcours=JFactory::getApplication()->getUserState('idParcours');
		
		if(!empty($user) && !empty($idParcours))
		{
			$today = date('Y-m-d');
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query -> select('clef');
			$query -> from('#__itinerary_subscriptions');
			$query -> where('clef_parcours ='.(int)$idParcours,'AND');
			$query -> where('clef_planificateur ='.(int)$user,'AND');
			$query -> where('date_debut <= "'.$today.'"','AND');
			$query -> where('date_fin >= "'.$today.'"');
			
			$db -> setQuery($query);
			
			$result = $db->loadAssoc();
			
			if($result!=null)
			{

                            return "waiting";

			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	public static function getItinerarySizeMax($model)
	{
		$db=JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('MAX(clef)');
		$query->from('#__itinerary_parcours_size');
		$db->setQuery($query);
		
		$result=$db->loadResult();
		
		return $result;
	}
	
	public static function setItineraryPinsFieldInProduction($model=null, $idParcours=null)
	{
		if(!empty($idParcours) && !empty($model))
		{
			$user=JFactory::getUser()->id;
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('clef,titre,latitude,longitude');
			$query->from($db->quoteName('#__itinerary_messages'));
			$query->where('clef_planificateur = '.(int) $user, 'AND');
			$query->where('clef_parcours = '.(int) $idParcours, 'AND');
			$query->where('activation_planificateur = 1', 'AND');
			$query->where('activation_administrateur = 1');
			$query->order('ordering ASC');
			
			$db->setQuery($query);
			$messagesParcours=$db->loadAssocList();		
			
			if($messagesParcours!=null)
			{	
				//$value = serialize($messagesParcours);
				$value = json_encode($messagesParcours);
				
				$value = '"'.urlencode($value).'"';
			}
			else
			{
				$value = '""';
			}
			
			//$db = JFactory::getDbo();
			$db = ItineraryHelper::connectToAnotherDB();
			$query = $db->getQuery(true);
			$query->update($db->quoteName('itinerary_parcours_prod'));
			$query->set('itinerarypins ='.$value);
			$query->where('clef = '.(int)$idParcours);
			
			  
			$db->setQuery($query);
			
			$db->execute();
		}
	}
	
	public static function connectToAnotherDB()
	{
		$option = array(); //prevent problems

		$option['driver']   = 'mysql';            // Database driver name
		$option['host']     = 'vps433240.ovh.net:4332';    // Database host name
		$option['user']     = 'tranfertDataInProduction';       // User for database authentication
		$option['password'] = '/*acc0untUse2TransferDataInPr0ducti0n!*/';   // Password for database authentication
		$option['database'] = 'c0itinerary';      // Database name
		$option['prefix']   = '';             // Database prefix (may be empty)

		return JDatabaseDriver::getInstance( $option );
		
		/*Note, however, that the parameters must match exactly for this to happen. For example, if two calls were made to a MySQL database using 
		JDatabaseDriver->getInstance, with the first using a host name of 'db.myhost.com' and the second using 'db.myhost.com:3306', then two separate 
		connections would be made, even though port 3306 is the default port for MySQL and so the parameters are logically the same.*/
	}
	
	public static function putImageInProduction($illustration=null)
	{
		if(!empty($illustration))
		{
			$ftp_server = "vps433240.ovh.net";
			$port = 2675;
			$remoteRootDir = "/home/transfertimageinproduction";
                        //$remoteRootDir = "/var/www/toossy.net/images";
			$localRootDir = JPATH_BASE."/images/com_itinerary";
			// set up a connection or die
			$conn_id = ftp_connect($ftp_server) or die("Couldn't connect to $ftp_server"); 
			
			$ftp_user_name = "transfertimageinproduction";
			$ftp_user_pass = "acc0untUse2TransferImageInPr0ducti0n";
			
			$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
			
			// check connection
			if ((!$conn_id) || (!$login_result)) {
				die("test FTP connection has failed !");
			}
			
			ftp_pasv($conn_id,true);
			
			$illustrationArray=explode('/',$illustration);
			$nbItem = count($illustrationArray);
			$directoryPart=array_slice($illustrationArray,0,$nbItem-1);
			$filePart = $illustrationArray[$nbItem-1];			
			ItineraryHelper::ftp_mksubdirs($conn_id,$remoteRootDir,$directoryPart);

			$upload = ftp_put($conn_id, $filePart, $localRootDir."/".$illustration, FTP_BINARY);
			$upload = ftp_put($conn_id, str_replace('main_','thumb_',$filePart), str_replace('main_','thumb_',$localRootDir."/".$illustration), FTP_BINARY);
			$upload = ftp_put($conn_id, "index.html", $localRootDir."/".implode("/",$directoryPart). "/index.html", FTP_BINARY);
                        
			ftp_close($conn_id);
		}
	}
	
	private static function ftp_mksubdirs($conn_id,$homedir,$pathParts)
	{
		@ftp_chdir($conn_id, $homedir);
		
		
		foreach($pathParts as $part)
		{
			if(!@ftp_chdir($conn_id, $part))
			{
				ftp_mkdir($conn_id, $part);
				ftp_chdir($conn_id, $part);
                                
                                /*if(!JFile::exists("index.html"))
                                {
                                    $index = fopen("index.html", "w+");
                                    fwrite($index, '<!DOCTYPE html><title></title>');
                                    fclose($index);
                                }*/
				ftp_chmod($ftpcon, 0777, $part);
			}
		}
		
		foreach(ftp_nlist($conn_id, ".") as $fileToBeDeleted)
		{
			ftp_delete($conn_id, $fileToBeDeleted);
		}
		
	}
	
	public static function removeImageFromProduction($illustration=null, $leveltype=null)
	{
		if($illustration!=null && $leveltype!=null)
		{
			$ftp_server = "vps433240.ovh.net";
			$port = "2675";
			$remoteRootDir = "/home/transfertimageinproduction";
			// set up a connection or die
			$conn_id = ftp_connect($ftp_server) or die("Couldn't connect to $ftp_server"); 
			
			$ftp_user_name = "transfertimageinproduction";
			$ftp_user_pass = "acc0untUse2TransferImageInPr0ducti0n";
			
			$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
			
                        ftp_pasv($conn_id,true);
                        
			// check connection
			if ((!$conn_id) || (!$login_result))
                        {
				die("FTP connection has failed !");
			}
                        
			$illustrationArray = explode('/',$illustration);
			$nbItem = count($illustrationArray);
			$directoryPart = array_slice($illustrationArray,0,$nbItem-1);
                        $pathToFolder = "/".implode('/',$directoryPart);
                        ftp_chdir($conn_id,$pathToFolder);
                        $fileList = ftp_nlist($conn_id, ".");

			foreach($fileList as $fileToBeDeleted)
			{
				ftp_delete($conn_id, $fileToBeDeleted);
			}
			
			if($leveltype=="Message")
			{
				ftp_cdup($conn_id);
				
				$directoryLastFolderName=$illustrationArray[$nbItem-2];
				ftp_rmdir($conn_id,$directoryLastFolderName);
			}
			
			
			ftp_close($conn_id);
		}
	}
	
	public static  function isDestinationInProduction($user=null)
	{		
		if(!empty($user))
		{
			$db = ItineraryHelper::connectToAnotherDB();
			$query = $db->getQuery(true);
			$query->select('clef');
			$query->from('itinerary_destinations');
			$query->where('clef = '.(int) $user);
			$db->setQuery($query);
			
			$result = $db->execute();
			
			if($result)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	public static  function copyDestinationToProduction($user=null)
	{	
		if(!empty($user))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id_acc_u');
			$query->from($db->quoteName('#__users'));
			$query->where('id = '.(int) $user);
			
			$db->setQuery($query);
			$values=$db->loadAssoc();
			
			if(!empty($values))
			{				
				$columns=array('clef','id_acc_u');
				//$values = array_unshift($values, $user);
				//$db = $this->getDbo();
				$db = ItineraryHelper::connectToAnotherDB();
				$query = $db->getQuery(true);
				$query->insert($db->quoteName('itinerary_destinations'));
				$query->columns($db->quoteName($columns));
				$query->values( '"'.$user.'","'.$values['id_acc_u'].'"');
				$db->setQuery($query);
				
				if($db->execute())
				{		
					return true;
				}
				else
				{
					return false;
				}
				
			}
		}
	}

	
	public static  function deleteDestinationFromProduction($user=null)
	{		
		if(!empty($user))
		{
			$db = ItineraryHelper::connectToAnotherDB();
			$query = $db->getQuery(true);
			$query->delete('itinerary_destinations');
			$query->where('clef = '.(int) $user);
			$db->setQuery($query);
			
			$result = $db->execute();
			
			if($result)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	public static function createUserAccount()
	{		
		if(ItineraryHelper::getUserAccId()==null)
		{
			define('__ROOT__', $_SERVER['DOCUMENT_ROOT'] ); 
			
			//Include the Stripe Library
			require_once(__ROOT__.'/libraries/stripe-php/init.php');
			
			//Charges the client's card
			// Set your secret key: remember to change this to your live secret key in production
			// See your keys here: https://dashboard.stripe.com/account/apikeys
			\Stripe\Stripe::setApiKey("sk_test_z0J9ztS4FFBddItntsNbduUE");
			
			$countData = ItineraryHelper::getUserProfile();
			$user = JFactory::getUser();
			
			$account = \Stripe\Account::create(array(
				"country" => $countData['profile.country'],
				"type" => "custom",
				"email" => $user->email,
				"business_name"=> $user->name,
				"debit_negative_balances"=> false,
				
			));
			
			ItineraryHelper::setUserAccId($account->id);
			
			/*$accountUpdate = \Stripe\Account::retrieve($account->id);
			$accountUpdate->support_phone = $countData['profile.phone'];
			$accountUpdate->city = $countData['profile.city'];
			$accountUpdate->postal_code = $countData['profile.postal_code'];
			$accountUpdate->line1 = $countData['profile.address1'];
			$accountUpdate->display_name = "Toossy-".$countData['profile.country']."-".$countData['profile.postal_code']."-".$user->name;
			$accountUpdate->save();*/
			
			
		}	
	}
	
	public static function getUserAccId()
	{
		$user=JFactory::getUser()->id;

		if($user!=null)
		{
			$db=JFactory::getDbo();
			$query=$db->getQuery(true);
			$query->select('id_acc_u');
			$query->from('#__users');
			$query->where('id ='.(int)$user);

			$db->setQuery($query);
			$result=$db->loadResult();

			return $result;
		}
		else
		{
			return null;
		}
	}
	
	public static function setUserAccId($accId=null)
	{
		$user=JFactory::getUser()->id;		

		if(!empty($user) && !empty($accId))
		{
			$db = JFactory::getDbo();
			$query = $db -> getQuery(true);
			$query->update('#__users');
			$query->set('id_acc_u = "'.$accId.'"');
			$query->where('id ='.(int)$user);

			$db->setQuery($query);
			$db->execute();
		}
	}

	public static function getUserProfile()
	{
		$user=JFactory::getUser()->id;	

		if(!is_null($user))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('profile_key, profile_value');
			$query->from('#__user_profiles');
			$query->where('user_id ='.(int)$user);			

			$db->setQuery($query);
			$profile_key=$db->loadColumn(0);
                        
                        if(!is_null($profile_key))
                        {
                        
                            $db = JFactory::getDbo();
                            $db->setQuery($query);
                            $profile_value=$db->loadColumn(1);


                            $return=array_combine($profile_key,$profile_value);
                        
                        
                            $profile=array();

                            foreach($return as $i => $profileItem)
                            {
                                    $profile[$i]=str_replace('"','',$profileItem);
                            }

                            
                            if(isset($profile["profile.country"]))
                            {
                                $temp = ItineraryHelper::getUserCountryName($profile["profile.country"]);

                                $profile["profile.country"]= $temp;
                            }
                            else
                            {
                                $profile["profile.country"]="";
                            }
                            

                            return $profile;
                        }
                        else
                        {
                            return null;
                        }
		}
		else
		{
			return null;
		}	
	}
        
        private static function getUserCountryName($userCountry=null)
        {
            if(!is_null($userCountry))
            {               
                //$userCountry=$userCountry["profile_value"];

                $db=JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('pays_nom');
                $query->from('#__itinerary_tva');
                $query->where('pays = "'.$userCountry.'"');

                $db->setQuery($query);
                $result=$db->loadAssoc();

                if($result)
                {
                    return $result["pays_nom"];
                }
                else
                {
                    return null;
                }
            }
            else
            {
                return null;
            }
        }
	
	public static function getParcoursPriceAccess()
	{
		$user=JFactory::getUser()->id;
		$idParcours=JFactory::getApplication()->getUserState('idParcours');
		
		if(!empty($user) && !empty($idParcours))
		{
			$db = JFactory::getDbo();
			$query = $db -> getQuery(true);
			$query->select('payant');
			$query->from($db->quoteName('#__itinerary_parcours'));
			$query->where('clef ='.(int)$idParcours, 'AND');
			$query->where('clef_planificateur ='.(int)$user);

			$db->setQuery($query);
			$result=$db->loadResult();

			if(!is_null($result))
			{
				return $result;
			}
			else
			{
				return 0;
			}
		}
	}
	
        
}