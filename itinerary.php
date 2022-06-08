<?php
//No direct access to the file
defined('_JEXEC') or die('Restricted access');

/*$document = JFactory::getDocument();
$cssFile = "./media/com_folio/css/site.stylesheet.css";
$document->addStyleSheet($cssFile);*/

$controller = JControllerLegacy::getInstance('Itinerary');
$input = JFactory::getApplication()->input;

$controller->execute($input->getCmd('task'));

$controller->redirect();