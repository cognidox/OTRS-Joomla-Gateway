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

jimport( 'joomla.application.component.view');
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'fieldhelper.php' );

/**
 * Class to view a single ticket
 *
 * @static
 * @package     Joomla
 * @subpackage  OTRS Gateway
 * @since 1.5
 */
class OTRSGatewayViewTicket extends JView
{
    function display( $tpl = null )
    {
        global $mainframe;
        $model = &$this->getModel();

        JHTML::_( 'stylesheet', 'otrsgateway.css', 'components/com_otrsgateway/assets/' );

        switch ( JRequest::getVar( 'task' ) )
        {
            case 'replyForm':
                $this->_replyForm( $model, $tpl );
                break;
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
        $ticketID = JRequest::getVar( 'ticketID' );
        if ( $ticketID )
        {
            $ticket = $model->getTicket( $ticketID );
            $allowedTags = '<p><em><i><span><a><b><u><ul><li><pre><ol><strike><br><tt><hr><div><strong>';
            $this->assignRef( 'allowedTags', $allowedTags );
            $this->assignRef( 'ticket', $ticket );

            $doc = JFactory::getDocument();
            $doc->setTitle( sprintf( '[%s] %s', $ticket->TicketNumber, $ticket->Title ) );
            $delAttLink = JRoute::_( 'index.php?option=com_otrsgateway&task=cleanAttachments&format=raw' );
            $this->assignRef( 'delAttLink', $delAttLink );
        }
    }

    function _replyForm( $model, $tpl )
    {
        $ticketID = JRequest::getVar( 'ticketID' );
        if ( $ticketID )
        {
            $ticket = $model->getTicket( $ticketID );
            $this->assignRef( 'ticket', $ticket );

            $editor = JFactory::getEditor();
            // Make sure the editor is active
            $plugin =& JPluginHelper::getPlugin( 'editors', $editor->_name );
            if ( is_object( $plugin ) )
            {
                $this->assignRef( 'editor', $editor );
            }

            $priorityList = OTRSGatewayFieldHelper::getOTRSTicketPriorities( $ticketID, false );
            $this->assignRef( 'priorityList', $priorityList );
            $stateList = OTRSGatewayFieldHelper::getOTRSTicketStates( $ticketID );
            $this->assignRef( 'stateList', $stateList );

            // Generate a token allowing form attachments to be 
            // tracked
            $token = JUtility::getHash( uniqid() );
            $this->assignRef( 'formToken', $token );
        }
    }

    function _submitForm( $model, $tpl, $dest = '', $priority = '', $subject = '', $text = '' )
    {
        $queues = array();
        $this->assignRef( 'queues', $queues );

        $editor = JFactory::getEditor();
        // Make sure the editor is active
        $plugin =& JPluginHelper::getPlugin( 'editors', $editor->_name );
        if ( is_object( $plugin ) )
        {
            $this->assignRef( 'editor', $editor );
        }

        $priorityList = OTRSGatewayFieldHelper::getOTRSTicketPriorities( false, true );
        $this->assignRef( 'priorityList', $priorityList );

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
            $this->assignRef( 'defaultPriority', $defaultPriority );
        }
        $this->assignRef( 'defaultDest', $dest );
        $this->assignRef( 'defaultSubject', $subject );
        $this->assignRef( 'defaultText', $text );

        // Generate a token allowing form attachments to be tracked
        $token = JUtility::getHash( uniqid() );
        $this->assignRef( 'formToken', $token );

        $queueList = OTRSGatewayFieldHelper::getOTRSTicketQueues();
        $this->assignRef( 'queues', $queueList );

        $ticketTypes = OTRSGatewayFieldHelper::getOTRSTicketTypes();
        if ( !empty( $ticketTypes ) )
        {
            $this->assignRef( 'ticketTypes', $ticketTypes );
        }
    }

    /*
     * _submit
     * Actually submit a ticket
     */
    function _submit( $model, $tpl )
    {
        global $mainframe;
        $this->setLayout( 'submit' );
        $result = array();
        $text = JRequest::getVar( 'otrsmessage', '', 'POST', 'string', JREQUEST_ALLOWHTML );
        $editor = JFactory::getEditor();
        // Make sure the editor is active
        $plugin =& JPluginHelper::getPlugin( 'editors', $editor->_name );
        if ( !is_object( $plugin ) )
        {
            $text = '<span style="white-space:pre">' . htmlspecialchars( $text ) . '</span>';
        }

        $ticketType = JRequest::getVar( 'typeID', '', 'POST', 'int' );
        $priority = JRequest::getVar( 'priorityID', '', 'POST', 'int' );
        $queue = JRequest::getVar( 'Dest', '', 'POST', 'string' );
        $subject = JRequest::getVar( 'Subject', '', 'POST', 'string' );
        $token = JRequest::getVar( 'formtoken', '', 'POST', 'string' );
        if ( JRequest::checkToken() )
        {
            $result = $model->submit( $text, $subject, $priority, $queue, $ticketType, $token );
            if ( array_key_exists( 'id', $result ) )
            {
                // redirect to the ticket
                $link = JRoute::_( 'index.php?option=com_otrsgateway&view=ticket&ticketID=' . $result['id'], false );
                $mainframe->redirect($link);
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

