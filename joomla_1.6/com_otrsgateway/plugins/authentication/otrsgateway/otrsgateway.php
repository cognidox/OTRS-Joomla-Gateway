<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	OTRSGateway
 * @copyright	Copyright (C) 2011 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * OTRS Authentication Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 1.5
 */
class plgAuthenticationOTRSGateway extends JPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param   array 	$credentials Array holding the user credentials
	 * @param 	array   $options     Array of extra options
	 * @param	object	$response	Authentication response object
	 * @return	boolean
	 * @since 1.5
	 */
	function onUserAuthenticate( $credentials, $options, &$response )
	{
		$message = '';
		$success = false;
        // Check the gateway component is installed
        $com_otrsgw = & JComponentHelper::getComponent( 'com_otrsgateway' );
        if ( $com_otrsgw )
        {
            // Check that it's configured
            include_once( JPATH_SITE . DS . 'components' . DS .
                          "com_otrsgateway" . DS . "helpers" . DS . 
                          "userhelper.php" );
            if (strlen($credentials['username']) && 
                strlen($credentials['password']))
            {
                $gateway = new OTRSGatewayUserHelper();
                $authData = $gateway->authenticateOTRSUser(
                                trim($credentials['username']),
                                $credentials['password']);
                if (is_array($authData))
                {
                    $success = true;
			        $response->email 	= $authData[0];
			        $response->fullname = $authData[1];
                }
                else
                {
                    $message = JText::_( 'PLG_AUTHENTICATION_OTRSGATEWAY_ACCESS_DENIED' );
                }
            }
            else
            {
                $message = JText::_( 'PLG_AUTHENTICATION_OTRSGATEWAY_BLANK_CREDS' );
            }
        }
        else
        {
            $message = JText::_( 'PLG_AUTHENTICATION_OTRSGATEWAY_NOT_INSTALLED' );
        }

		if ($success)
		{
			$response->status 	     = JAUTHENTICATE_STATUS_SUCCESS;
			$response->error_message = '';
		}
		else
		{
			$response->status = JAUTHENTICATE_STATUS_FAILURE;
			$response->error_message = JText::sprintf( 'PLG_AUTHENTICATION_OTRSGATEWAY_AUTH_FAIL', $message );
		}
	}
}
