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

jimport( 'joomla.application.component.view' );
jimport('joomla.html.editor');
require_once( JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'fieldhelper.php' );

/**
 * Class to view a single ticket
 *
 * @static
 * @package     Joomla
 * @subpackage  OTRS Gateway
 * @since 1.5
 */
class OTRSGatewayViewTicket extends JViewLegacy
{
    function display( $tpl = null )
    {
        $model = &$this->getModel();

		$doc = JFactory::getDocument();
		$doc->addStyleSheet( JUri::root() . 'components/com_otrsgateway/assets/otrsgateway.css' );

		$jinput = JFactory::getApplication()->input;
		switch ( $jinput->get( 'task', null, null ) )
        {
            case 'submitForm':
                $this->_submitForm( $model, $tpl, '', '', '', '' );
                break;
            case 'submit':
                $this->_submit( $model, $tpl );
                break;
            default:
                $this->_displayTicket( $model, $tpl );
        }
        parent::display( $tpl );
    }

    function _displayTicket( $model, $tpl )
    {
		$jinput = JFactory::getApplication()->input;
		$ticketID = $jinput->get( 'ticketID', null, null );
        if ( $ticketID )
        {
            $ticket = $model->getTicket( $ticketID );
            $allowedTags = '<p><em><i><span><a><b><u><ul><li><pre><ol><strike><br><tt><hr><div><strong>';
            $this->allowedTags=$allowedTags;
            $this->ticket=$ticket;

            $doc = JFactory::getDocument();
            $doc->setTitle( sprintf( '[%s] %s', $ticket->TicketNumber, $ticket->Title ) );
            $delAttLink = JRoute::_( 'index.php?option=com_otrsgateway&task=cleanAttachments&format=raw' );
            $this->delAttLink=$delAttLink;
			
            //Replyform
            $priorityList = OTRSGatewayFieldHelper::getOTRSTicketPriorities( $ticketID, false );
            $this->priorityList=$priorityList;
            $stateList = OTRSGatewayFieldHelper::getOTRSTicketStates( $ticketID );
            $this->stateList=$stateList;
			
            $conf = JFactory::getConfig();
            $editor_conf = $conf->get('editor');
            $editor = JEditor::getInstance($editor_conf);
			
            if ( is_object( $editor ) )
            {
				$this->editor=$editor;
            }
			
            // Generate a token allowing form attachments to be tracked
            $token = JApplication::getHash( uniqid() );
            $this->formToken=$token;
        }
    }

    function _submitForm( $model, $tpl, $dest = '', $priority = '', $subject = '', $text = '' )
    {
		$conf = JFactory::getConfig();
		$editor_conf = $conf->get('editor');
		$editor = JEditor::getInstance($editor_conf);
		
        if ( is_object( $editor ) )
        {
			$this->editor=$editor;
        }

        $priorityList = OTRSGatewayFieldHelper::getOTRSTicketPriorities( false, true );
		$this->priorityList=$priorityList;

        if ( $priorityList )
        {
            if ( $priority != '' )
            {
                $defaultPriority = $priority;
            }
            else
            {
                $defaultPriority = OTRSGatewayFieldHelper::getOTRSDefaultPriority();
            }
			$this->defaultPriority=$defaultPriority;
        }
		$this->defaultDest=$dest;
		$this->defaultSubject=$subject;
		$this->defaultText=$text;

        // Generate a token allowing form attachments to be tracked
		$token = JApplication::getHash( uniqid() );
		$this->formToken=$token;

        // Get the allowed queues
        $queueList = OTRSGatewayFieldHelper::getOTRSTicketQueues();
		$this->queues=$queueList;
		
        // Get the types for the ticket
        $ticketTypes = OTRSGatewayFieldHelper::getOTRSTicketTypes();
        if ( !empty( $ticketTypes ) )
        {
			$this->ticketTypes=$ticketTypes;
        }
    }

    /*
     * _submit
     * Actually submit a ticket
     */
    function _submit( $model, $tpl )
    {
        $this->setLayout( 'submit' );
        $result = array();
		$jinput = JFactory::getApplication()->input;
		$text = $jinput->get( 'otrsmessage', null, 'RAW' );
		
		$conf = JFactory::getConfig();
		$editor_conf = $conf->get('editor');
		$editor = JEditor::getInstance($editor_conf);
		
        if ( !is_object( $editor ) || 
		    $editor->get('_name') == 'none' ||
            $editor->get('_name') == 'codemirror' )
        {
            $text = '<span style="white-space:pre">' . htmlspecialchars( $text ) . '</span>';
        }
		
		//convert carriage return when no editor is used
		$params = JComponentHelper::getParams( 'com_otrsgateway' );
		if ( $params->get('otrsgateway_editor') == "1" )
		    $text = nl2br($text);
	
		$ticketType = $jinput->get( 'typeID', null, null );
        $priority = $jinput->get( 'priorityID', null, null );
        $queue = $jinput->get( 'Dest', null, null );
        $subject = $jinput->get( 'Subject', null, null );
        $token = $jinput->get( 'formtoken', null, null );
		
		if ( JSession::checkToken() )
        {
            $result = $model->submit( $text, $subject, $priority, $queue, $ticketType, $token );
            if ( array_key_exists( 'id', $result ) )
            {
                // redirect to the ticket
                $link = JRoute::_( 'index.php?option=com_otrsgateway&view=ticket&ticketID=' . $result['id'], false );
                $app = JFactory::getApplication();
                $app->redirect($link);
            }
            else
            {
                JError::raiseWarning( 501, JText::_( $result['error'] ) );
            }
        }
        else
        {
            JError::raiseWarning( 501, 'Your form submission failed a security check. Please re-submit the ticket.' );
        }
        $this->_submitForm( $model, $tpl, $queue, $priority, $subject, $text );
    }
}

