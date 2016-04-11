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

jimport( 'joomla.application.component.controller' );

/**
 * OTRS Gateway Component Controller
 *
 * @package     Joomla
 * @subpackage  OTRS Gateway
 * @since 1.5
 */
class OTRSGatewayController extends JControllerLegacy
{	
    function __construct()
    {
        global $mainframe;
        parent::__construct();
        $this->registerDefaultTask( 'display' );
        $this->registerTask( 'reply', 'reply' );
        $this->registerTask( 'submitForm', 'submitForm' );
        $this->registerTask( 'addAttachment', 'addAttachment' );
        $this->registerTask( 'delAttachment', 'delAttachment' );
        $this->registerTask( 'cleanAttachments', 'cleanAttachments' );
    }

    function display($cachable = false, $urlparams = array())
    {
        $jinput = JFactory::getApplication()->input;
        $doDisplay = true;

        // Set a default view if none exists
        if ( ! $jinput->getCmd( 'view' ) )
        {
            $jinput->set( 'view', 'tickets' );
        } 

        // Handle displaying an attachment
        if ( $jinput->getCmd( 'view' ) == 'attachment' )
        {
            $view = $this->getView( 'attachment', 'raw' );
            $model = $this->getModel( 'attachment' );
            $result = $model->download( $jinput->get( 'ArticleID', null, null), $jinput->get( 'AtmID', null, null) );
            if ( array_key_exists( 'data', $result ) )
            {
                $view->displayAttachment( $result['data'] );
                $doDisplay = false;
            }
        }
        if ( $doDisplay )
        {
            parent::display();
        }
    }

    function submitForm()
    {
        $jinput = JFactory::getApplication()->input;
        $jinput->set( 'view', 'ticket' );
        $jinput->set( 'layout', 'submit' );
        $jinput->set( 'format', 'html' );
        parent::display();
    }

    function reply()
    {
        $jinput = JFactory::getApplication()->input;
        $jinput->set( 'view', 'ticket' );
        $jinput->set( 'layout', 'reply_result' );
        $jinput->set( 'format', 'raw' );
        parent::display();
    }

    function addAttachment()
    {
        $jinput = JFactory::getApplication()->input;
        $jinput->set( 'view', 'attachment' );
        $jinput->set( 'layout', 'add' );
        $jinput->set( 'format', 'raw' );
        parent::display();
    }

    function delAttachment()
    {
        $jinput = JFactory::getApplication()->input;
        $jinput->set( 'view', 'attachment' );
        $jinput->set( 'layout', 'delete' );
        $jinput->set( 'format', 'raw' );
        parent::display();
    }

    function cleanAttachments()
    {
        $jinput = JFactory::getApplication()->input;
        $jinput->set( 'view', 'attachment' );
        $jinput->set( 'layout', 'delete' );
        $jinput->set( 'format', 'raw' );
        parent::display();
    }
}

