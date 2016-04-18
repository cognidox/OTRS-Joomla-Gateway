<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controller.php' );
$jinput = JFactory::getApplication()->input;

$controller = new OTRSGatewayController();
$controller->execute( $jinput->getCmd( 'task' ) );
$controller->redirect();