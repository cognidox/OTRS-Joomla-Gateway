<?php
/**
 * Ticket model
 * 
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );

require_once( JPATH_COMPONENT.DS.'helpers'.DS.'userhelper.php' );
 
/**
 * Ticket Model
 *
 * @package    Joomla
 * @subpackage OTRS Gateway
 */
class OTRSGatewayModelTicket extends JModel
{
    var $_userID = null;
    var $_customerIDs = null;
    var $_gateway = null;

    function __construct()
    {
        parent::__construct();
        $userData = OTRSGatewayUserHelper::getOTRSUserData();
        if ($userData != null)
        {
            $this->_userID = $userData[0];
            $this->_customerIDs = $userData[1];
        }
        $this->_gateway = new OTRSGatewayRPCHelper();
    }

    function getTicket( $id = false )
    {
        if ( !$this->_userID )
        {
            return null;
        }
        if ( !$id )
        {
            return null;
        }
        $tickets = array();
        $vars = array(
            'TicketID' => array( $id, XSD_STRING ),
            'CustomerUserLogin' => array( $this->_userID, XSD_STRING ),
            'CustomerUserID' => array( $this->_userID, XSD_STRING ),
            'Permission' => array( 'ro', XSD_STRING )
            );
        $result = null;
        if ( $err = $this->_gateway->callOTRS(
                        'JoomlaGatewayObject', 'GetTicket', $vars,
                        $result ) )
        {
            // Failed to find anything
            return null;
        }
        return $result;
    }

    /*
     * Reply to a ticket
     */
    function reply( $id = false, $text = '', $priority = false, $state = false, $token = false )
    {
        if ( !$this->_userID )
        {
            return array( 'error' => 'Permission denied' );
        }
        if ( !$id )
        {
            return array( 'error' => 'No ticket provided' );
        }
        $vars = array(
            'TicketID' => array( $id, XSD_STRING ),
            'CustomerUserID' => array( $this->_userID, XSD_STRING ),
            'Body' => array( $text, XSD_STRING ),
            'PriorityID' => array( $priority, XSD_INTEGER ),
            'StateID' => array( $state, XSD_INTEGER )
            );

        // Get the attachments
        if ( $token )
        {
            $atm =& JModel::getInstance( 'attachment', 'otrsgatewaymodel' );
            $attachments = $atm->getAll( $token );
            $vars['Attachments'] = array( $attachments, SOAP_ENC_OBJECT );
            $atm->removeAll( $token );
        }
        $result = null;
        if ( $err = $this->_gateway->callOTRS(
                        'JoomlaGatewayObject', 'TicketReply', $vars,
                        $result ) )
        {
            // Failed to find anything
            return array( 'error' => $err );
        }
        if ( isset( $result->error ) )
        {
            return array( 'error' => $result->error );
        }
        return array();
    }

    function submit( $text = '', $subject = '', $priority = null, $queue = null, $token = false )
    {
        if ( !$this->_userID )
        {
            return array( 'error' => 'Permission denied' );
        }
        if (! strlen($subject) )
        {
            return array( 'error' => 'Please provide a subject' );
        }
        if (! strlen($text) )
        {
            return array( 'error' => 'Please provide a message' );
        }
        $vars = array(
            'CustomerUserID' => array( $this->_userID, XSD_STRING ),
            'Body' => array( $text, XSD_STRING ),
            'Subject' => array( $subject, XSD_STRING ),
            'PriorityID' => array( $priority, XSD_INTEGER ),
            'Dest' => array( $queue, XSD_STRING )
            );
        if ( $this->_customerIDs != null && is_array( $this->_customerIDs ) )
        {
            $vars['CustomerID'] = array( $this->_customerIDs[0], XSD_STRING );
        }
        if ( $token )
        {
            $atm =& JModel::getInstance( 'attachment', 'otrsgatewaymodel' );
            $attachments = $atm->getAll( $token );
            $vars['Attachments'] = array( $attachments, SOAP_ENC_OBJECT );
            $atm->removeAll( $token );
        }
        $result = null;
        if ( $err = $this->_gateway->callOTRS(
                        'JoomlaGatewayObject', 'TicketSubmit', $vars,
                        $result ) )
        {
            // Failed to find anything
            return array( 'error' => $err );
        }
        if ( isset($result->id) )
        {
            return array( 'id' => $result->id );
        }
        return array( 'error' => $result->error );
    }
}

