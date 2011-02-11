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

/**
 * OTRS Gateway RPC Helper
 *
 * @package     Joomla
 * @subpackage  OTRS Gateway
 * @since 1.5
 */
class OTRSGatewayRPCHelper
{
    var $_valid = false;
    var $_error = false;
    var $_client = null;
    var $_baseArgs = array();
    
    function __construct()
    {
        $this->_valid = false;
        $this->_error = false;
        $this->_client = null;
        $params = JComponentHelper::getParams( 'com_otrsgateway' );
        $url = $params->get( 'otrsgateway_rpc_url' );
        $user = $params->get( 'otrsgateway_rpc_user' );
        $pass = $params->get( 'otrsgateway_rpc_password' );
        if ( empty( $url ) || empty( $user ) || empty( $pass ) )
        {
            $this->_error = "Not configured";
        }
        else
        {
            if ( !class_exists( "SoapClient" ) )
            {
                $this->_error = "No SoapClient Class";
            }
            else
            {
                $this->_client = new SoapClient(NULL,
                        array(
                                'location' => $url,
                                'style'    => SOAP_RPC,
                                'uri'      => '/Core#Dispatch',
                                'use'      => SOAP_ENCODED,
                                'trace'    => TRUE,
                                'features' => SOAP_SINGLE_ELEMENT_ARRAYS
                        ) );
                $this->_valid = true;
                $this->_baseArgs[] = new SoapParam($user, "User");
                $this->_baseArgs[] = new SoapParam($pass, "Password");
            }
        }
    }

    function callOTRS ( $object, $method, &$vars, &$result )
    {
        if ( !$this->_valid )
            return $this->_error;

        $args = $this->_baseArgs;
        $args[] = new SoapParam($object, "Object");
        $args[] = new SoapParam($method, "Method");
        foreach ( $vars as $key => $data )
        {
            $args[] = new SoapVar( $key, XSD_STRING );
            if ( is_array( $data ) )
            {
                $val = $data[0];
                $type = $data[1];
                if ( is_array( $val ) )
                {
                    $arr = array();
                    foreach ( $val as $item )
                    {
                        $arr[] = new SoapVar( $item, $type );
                    }
                    $args[] = new SoapVar( $arr, SOAP_ENC_ARRAY );
                }
                else
                {
                    $args[] = new SoapVar( $val, $type );
                }
            }
        }
        try
        {
            $result = $this->_client->__soapCall( "Dispatch", $args );
        }
        catch (SoapFault $fault)
        {
            return "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
        }
        return false;
    }

    function lastResponse()
    {
        if ( !$this->_valid || !$this->_client )
            return null;
        return $this->_client->__getLastResponse();
    }

    function makeHashArray( &$data )
    {
        if ( is_array( $data ) )
        {
            $result = array();
            $keys = array_keys($data);
            while ( count( $keys ) )
            {
                $el1 = array_shift($keys);
                $el2 = array_shift($keys);
                $key = $data[$el1];
            }
            $data = $result;
        }
    }

    function getRawValue( $key )
    {
        $xml = $this->_client->__getLastResponse();
        $re = '/<s-gensym\d+ xsi:type="xsd:string">' . htmlspecialchars($key) .
              '<\/s-gensym\d+><s-gensym\d+ xsi:type="[\w:]+">([^<]*)</';
        preg_match( $re, $xml, $matches );
        if ( count($matches) )
        {
            return $matches[1];
        }
        return null;
    }
}

