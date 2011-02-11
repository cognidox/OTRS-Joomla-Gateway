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

// Set up a default task in the submit form
if ( !$task && JRequest::getVar( 'layout' ) == 'submit' )
{
    JRequest::setVar( 'task', 'submitForm' );
}
// Create the controller
$controller = JController::getInstance( 'OTRSGateway' );

$controller->execute( JRequest::getCmd( 'task' ) );

// Redirect if set by the controller
$controller->redirect();

