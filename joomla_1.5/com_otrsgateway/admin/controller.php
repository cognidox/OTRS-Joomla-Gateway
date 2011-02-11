<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * @package     Joomla
 * @subpackage  OTRS Gateway
 */
class OTRSGatewayController extends JController
{
    /**
     * Show OTRS Gateway admin page
     */
    function display()
    {
        $view   =& $this->getView( 'Admin' );
        $view->display();
    }
}

