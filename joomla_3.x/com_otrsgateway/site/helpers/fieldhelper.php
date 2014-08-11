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

// Get the OTRS RPC stuff
require_once( JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'rpchelper.php' );

/**
 * OTRS Gateway Field Helper
 *
 * @package     Joomla
 * @subpackage  OTRS Gateway
 * @since 1.5
 */
class OTRSGatewayFieldHelper
{
    /**
     * getOTRSTicketPriorities
     *
     * Return the OTRS priorities available for a ticket
     */
    function getOTRSTicketPriorities( $id = false, $new = false )
    {
        $userData = OTRSGatewayUserHelper::getOTRSUserData();
        if ( !$userData || ! is_array( $userData ) )
        {
            return null;
        }
        $vars = array (
            'CustomerUserID' => array( $userData[0], XSD_STRING ),
            'NewTicket' => array( $new, XSD_BOOLEAN )
            );
        if ( $id )
        {
            $vars['TicketID'] = array( $id, XSD_STRING );
        }
        $result = null;
        $gateway = new OTRSGatewayRPCHelper();
        if ( $err = $gateway->callOTRS(
                        'JoomlaGatewayObject', 'PriorityList', $vars,
                        $result ) )
        {
            // Failed to find anything
            return null;
        }
        $priorities = array();
        while (count($result) > 1)
        {
            $key = array_shift($result);
            $val = array_shift($result);
            $priorities[ $key ] = $val;
        }
        asort($priorities);
        return $priorities;
    }

    /**
     * getOTRSDefaultPriority
     *
     * Return the default priority for a new ticket
     */
    function getOTRSDefaultPriority()
    {
        $vars = array();
        $result = null;
        $gateway = new OTRSGatewayRPCHelper();
        if ( $err = $gateway->callOTRS(
                        'JoomlaGatewayObject', 'PriorityDefault', $vars,
                        $result ) )
        {
            // Failed to find anything
            return null;
        }
        return $result;
    }

    /**
     * getOTRSTicketTypes
     *
     * Return a list of ticket types if OTRS is configured
     * for them to be set by the user
     */
    function getOTRSTicketTypes()
    {
        $vars = array();
        $result = null;
        $gateway = new OTRSGatewayRPCHelper();
        if ( $err = $gateway->callOTRS(
                        'JoomlaGatewayObject', 'TicketTypeList', $vars,
                        $result ) )
        {
            // Failed to find anything
            return null;
        }
        return $result;
    }

    /**
     * getOTRSTicketStates
     *
     * Return the OTRS states available for a ticket
     */
    function getOTRSTicketStates( $id = false )
    {
        $userData = OTRSGatewayUserHelper::getOTRSUserData();
        if ( !$userData || ! is_array( $userData ) )
        {
            return null;
        }
        $vars = array (
            'Action' => array( 'CustomerTicketZoom', XSD_STRING ),
            'CustomerUserID' => array( $userData[0], XSD_STRING )
            );
        if ( $id )
        {
            $vars['TicketID'] = array( $id, XSD_STRING );
        }
        $result = null;
        $gateway = new OTRSGatewayRPCHelper();
        if ( $err = $gateway->callOTRS(
                        'TicketObject', 'StateList', $vars,
                        $result ) )
        {
            // Failed to find anything
            return null;
        }
        $states = array();
        while (count($result) > 0)
        {
            $key = array_shift($result);
            $val = array_shift($result);
			$val = translateOTRSTicketState($val);
            $states[ $key ] = $val;
        }
        return $states;
    }
	
    /**
     * getOTRSTicketQueues
     *
     * Return the OTRS queues available
     */
    function getOTRSTicketQueues()
    {
        $userData = OTRSGatewayUserHelper::getOTRSUserData();
        if ( !$userData || ! is_array( $userData ) )
        {
            return null;
        }
        $vars = array (
            'CustomerUserID' => array( $userData[0], XSD_STRING )
            );
        $gateway = new OTRSGatewayRPCHelper();
        if ( $err = $gateway->callOTRS(
                        'JoomlaGatewayObject', 'GetTicketQueues', $vars,
                        $result ) )
        {
            // Failed to find anything
            return null;
        }
        if (! is_array($result) )
        {
            return null;
        }
        asort($result);
        return $result;
    }
}

/**
 * translateOTRSTicketState
 *
 * Return the OTRS state from language file
 */
function translateOTRSTicketState($state)
{
	switch ($state) {
			case "new":
				$state = JText::_('COM_OTRSGATEWAY_STATE_NEW');
				break;
			case "pending reminder":
				$state = JText::_('COM_OTRSGATEWAY_STATE_PENDING_REMINDER');
				break;
			case "pending auto close-":
				$state = JText::_('COM_OTRSGATEWAY_STATE_PENDING_AUTO-');
				break;
			case "pending auto close+":
				$state = JText::_('COM_OTRSGATEWAY_STATE_PENDING_AUTO+');
				break;
			case "closed successful":
				$state = JText::_('COM_OTRSGATEWAY_STATE_CLOSED_SUCCESSFUL');
				break;
			case "closed unsuccessful":
				$state = JText::_('COM_OTRSGATEWAY_STATE_CLOSED_UNSUCCESSFUL');
				break;
			case "open":
				$state = JText::_('COM_OTRSGATEWAY_STATE_OPEN');
				break;
			default:
				$state = " - ";
	}
	return $state;
}

