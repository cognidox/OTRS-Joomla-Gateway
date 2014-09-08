<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the com_content helper library
jimport( 'joomla.application.component.controller' );
$jinput = JFactory::getApplication()->input;

// Set up a default task in the submit form
//if ( !$task && JRequest::getVar( 'layout' ) == 'submit' )
if ( !$task && $jinput->get( 'layout', null, null ) == 'submit' )
{
    //JRequest::setVar( 'task', 'submitForm' );
	$jinput->set( 'task', 'submitForm' );
}
// Create the controller
$controller = JControllerLegacy::getInstance( 'OTRSGateway' );

//$controller->execute( JRequest::getCmd( 'task' ) );
$controller->execute( $jinput->getCmd( 'task' ) );

// Redirect if set by the controller
$controller->redirect();

