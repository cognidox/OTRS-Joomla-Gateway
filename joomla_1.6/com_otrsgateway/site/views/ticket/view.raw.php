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
        $result = array();
        if ( JRequest::checkToken() )
        {
            $ticketID = JRequest::getVar( 'ticketID' );
            if ( $ticketID )
            {
                $ticket = $model->getTicket( $ticketID );
                $this->assignRef( 'ticket', $ticket );

                if ($ticket && JRequest::getVar( 'task' ) == 'reply') {

                    $text = JRequest::getVar( 'otrsReplyText', '', 'POST', 'string', JREQUEST_ALLOWHTML );
                    // Determine if we should auto-escape the content
                    // of the ticket
                    $editor = JFactory::getEditor();
                    if ( !is_object( $editor ) || 
                         $editor->get('_name') == 'none' ||
                         $editor->get('_name') == 'codemirror' )
                    {
                        $text = '<span style="white-space:pre">' . htmlspecialchars( $text ) . '</span>';
                    }

                    $priority = JRequest::getVar( 'priorityID' );
                    $state = JRequest::getVar( 'StateID' );
                    $token = JRequest::getVar( 'formtoken' );
                    $result = $model->reply( $ticketID, $text, $priority, $state, $token );
                }
                else
                {
                    $result['error'] = JText::_( 'COM_OTRSGATEWAY_TICKET_ACCESS_DENIED' );
                }
            }
            else
            {
                $result['error'] = JText::_( 'COM_OTRSGATEWAY_TICKET_ACCESS_DENIED ');
            }
        }
        else
        {
            $result['error'] = JText::_( 'COM_OTRSGATEWAY_UNABLE_TO_PROCESS_REQUEST' );
        }
        $this->assignRef( 'result', $result );
        parent::display( $tpl );
    }
}

