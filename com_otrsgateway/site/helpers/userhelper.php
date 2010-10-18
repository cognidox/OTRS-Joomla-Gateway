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
require_once( JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_otrsgateway'.DS.'helpers'.DS.'rpchelper.php' );

/**
 * OTRS Gateway User Helper
 *
 * @package     Joomla
 * @subpackage  OTRS Gateway
 * @since 1.5
 */
class OTRSGatewayUserHelper
{
    /**
     * getOTRSUserData
     *
     * Return the OTRS User ID for the currently active user
     * Retruns null if unable to lookup the user, or this is the anon user
     */
    function getOTRSUserData()
    {
        $user =& JFactory::getUser();
        if ( !$user || $user->get('guest') )
        {
            return null;
        }
        $session = JFactory::getSession();
        if ( $session->has('user', 'otrsgateway') )
        {
            return array( $session->get('user', '', 'otrsgateway'),
                          $session->get('customerIDs', array(), 'otrsgateway') );
        }
        $params = &JComponentHelper::getParams( 'com_otrsgateway' );
        $vars = null;
        if ( $params->get( 'otrsgateway_auth_map' ) == 0 )
        {
            $vars = array( 'UserLogin' => 
                            array( $user->get('username'), XSD_STRING ) );
        }
        else if ( $params->get( 'otrsgateway_auth_map' ) == 1 )
        {
            $vars = array( 'UserEmail' =>
                           array( $user->get('email'), XSD_STRING ) );
        }
        else
        {
            return null;
        }
        $result = null;
        $gateway = new OTRSGatewayRPCHelper();
        if ( $err = $gateway->callOTRS(
                        'JoomlaGatewayObject', 'GetCustomerUserData', $vars,
                        $result ) )
        {
            // Failed to find anything
            return null;
        }
        else
        {
            if ( is_array( $result ) && count( $result ) > 1 )
            {
                $session->set( 'user', $result[0], 'otrsgateway' );
                $session->set( 'customerIDs', $result[1], 'otrsgateway' );
                return $result;
            }
        }
        return null;
    }

    /**
     * authenticateOTRSUser
     * Return a email, full name for an active user account given the 
     * user credentials
     */
    function authenticateOTRSUser ( $login = '', $pwd = '' )
    {
        $vars = array( 'User' => array( $login, XSD_STRING ),
                       'Pw' => array( $pwd, XSD_STRING ) );
        $result = null;
        $gateway = new OTRSGatewayRPCHelper();
        if ( $err = $gateway->callOTRS(
                        'JoomlaGatewayObject', 'AuthenticateOTRSUser', $vars,
                        $result ) )
        {
            // Failed to find anything
            return null;
        }
        else
        {
            if ( is_array( $result ) && count( $result ) > 1 )
            {
                return $result;
            }
        }
        return null;
    }
}

