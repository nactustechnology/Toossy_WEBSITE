<?php

defined('_JEXEC') or die;

class ItineraryControllerFeedbacks extends JContollerAdmin
{
    public function getModel($name = 'Feedbacks', $prefix = 'ItineraryModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
    
}