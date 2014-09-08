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
class OTRSGatewayViewTicket extends JViewLegacy
{
    function display( $tpl = null )
    {
        global $mainframe;
        $model = &$this->getModel();
        $result = array();
        //if ( JRequest::checkToken() )
		$jinput = JFactory::getApplication()->input;
		if ( JSession::checkToken() )
        {
            //$ticketID = JRequest::getVar( 'ticketID' );
			$ticketID = $jinput->get( 'ticketID', null, null );
            if ( $ticketID )
            {
                $ticket = $model->getTicket( $ticketID );
                //$this->assignRef( 'ticket', $ticket );
				$this->ticket=$ticket;

                //if ($ticket && JRequest::getVar( 'task' ) == 'reply') {
				if ($ticket && $jinput->get( 'task', null, null ) == 'reply') {

                    //$text = JRequest::getVar( 'otrsreplytext', '', 'POST', 'string', JREQUEST_ALLOWHTML );
					$text = $jinput->get( 'otrsreplytext', null, null );
                    // Determine if we should auto-escape the content
                    // of the ticket
					
                    //$editor = JFactory::getEditor();
					$conf = JFactory::getConfig();
					$editor_conf = $conf->get('editor');
					$editor = JEditor::getInstance($editor_conf);
					
                    if ( !is_object( $editor ) || 
                         $editor->get('_name') == 'none' ||
                         $editor->get('_name') == 'codemirror' )
                    {
                        $text = '<span style="white-space:pre">' . htmlspecialchars( $text ) . '</span>';
                    }

                    // $priority = JRequest::getVar( 'priorityID' );
                    // $state = JRequest::getVar( 'StateID' );
                    // $token = JRequest::getVar( 'formtoken' );
					$priority = $jinput->get( 'priorityID', null, null );
                    $state = $jinput->get( 'StateID', null, null );
                    $token = $jinput->get( 'formtoken', null, null );
					
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
        //$this->assignRef( 'result', $result );
		$this->result=$result;
        parent::display( $tpl );
        jexit();
    }
}

