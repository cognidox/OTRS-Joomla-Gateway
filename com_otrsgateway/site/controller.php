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
 * OTRS Gateway Component Controller
 *
 * @package     Joomla
 * @subpackage  OTRS Gateway
 * @since 1.5
 */
class OTRSGatewayController extends JController
{
    function __construct()
    {
        global $mainframe;
        parent::__construct();
        $this->registerDefaultTask( 'display' );
        $this->registerTask( 'replyForm', 'replyForm' );
        $this->registerTask( 'reply', 'reply' );
        $this->registerTask( 'submitForm', 'submitForm' );
        $this->registerTask( 'addAttachment', 'addAttachment' );
        $this->registerTask( 'delAttachment', 'delAttachment' );
        $this->registerTask( 'cleanAttachments', 'cleanAttachments' );
    }

    function display()
    {
        $doDisplay = true;

        // Set a default view if none exists
        if ( ! JRequest::getCmd( 'view' ) )
        {
            JRequest::setVar( 'view', 'tickets' );
        }

        // Handle displaying an attachment
        if ( JRequest::getCmd( 'view' ) == 'attachment' )
        {
            $model = $this->getModel( 'attachment' );
            $result = $model->download( JRequest::getVar( 'ArticleID', '', '', 'integer'), JRequest::getVar( 'AtmID', '', '', 'integer') );
            if ( array_key_exists( 'data', $result ) )
            {
                $document = &JFactory::getDocument();
                $doc =& JDocument::getInstance('raw');
                $document = $doc;
                JResponse::clearHeaders();
                $document->setMimeEncoding( $result['data']->ContentType );
                JResponse::setHeader( 'Content-length', $result['data']->FilesizeRaw, true );
                $fn = preg_replace( '/"/', '\"', $result['data']->Filename);
                JResponse::setHeader( 'Content-disposition', sprintf( 'attachment;filename="%s"', $fn ), true );
                $document->render();
                echo $result['data']->Content;
                $doDisplay = false;
            }
        }
        if ( $doDisplay )
        {
            parent::display();
        }
    }

    function replyForm()
    {
        JRequest::setVar( 'view', 'ticket' );
        JRequest::setVar( 'layout', 'reply' );
        JRequest::setVar( 'tmpl', 'component' );
        parent::display();
    }

    function submitForm()
    {
        JRequest::setVar( 'view', 'ticket' );
        JRequest::setVar( 'layout', 'submit' );
        JRequest::setVar( 'format', 'html' );
        parent::display();
    }

    function reply()
    {
        JRequest::setVar( 'view', 'ticket' );
        JRequest::setVar( 'layout', 'reply_result' );
        JRequest::setVar( 'format', 'raw' );
        parent::display();
    }

    function addAttachment()
    {
        JRequest::setVar( 'view', 'attachment' );
        JRequest::setVar( 'layout', 'add' );
        JRequest::setVar( 'format', 'raw' );
        parent::display();
    }

    function delAttachment()
    {
        JRequest::setVar( 'view', 'attachment' );
        JRequest::setVar( 'layout', 'delete' );
        JRequest::setVar( 'format', 'raw' );
        parent::display();
    }

    function cleanAttachments()
    {
        JRequest::setVar( 'view', 'attachment' );
        JRequest::setVar( 'layout', 'delete' );
        JRequest::setVar( 'format', 'raw' );
        parent::display();
    }
}

