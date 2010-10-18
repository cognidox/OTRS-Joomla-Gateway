<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'rpchelper.php' );

/**
 * @package     Joomla
 * @subpackage  OTRS Gateway
 * @since 1.5
 */
class OTRSGatewayViewAdmin extends JView
{
    function display($tpl=null)
    {
        JToolBarHelper::title( JText::_( 'OTRS_GATEWAY' ), '' );
        JToolBarHelper::preferences( 'com_otrsgateway', '350' );

        $document = & JFactory::getDocument();
        $document->setTitle( JText::_('OTRS_GATEWAY') );

        // Work out the current status of the module
        $summary = array( JText::_( 'OTRS_UNCONFIGURED' ) );
        $config =& JComponentHelper::getParams( 'com_otrsgateway' );
        if ( class_exists( 'SoapClient' ) )
        {
            if ( $config->get('otrsgateway_rpc_url') &&
                 $config->get('otrsgateway_rpc_user') &&
                 $config->get('otrsgateway_rpc_password') )
            {
                $summary = array();
                $result = null;
                $vars = array( 'SystemID' => array( false, XSD_BOOLEAN ) );
                $gateway = new OTRSGatewayRPCHelper();
                if ( $err = $gateway->callOTRS(
                                'TimeObject', 'CurrentTimestamp', $vars, 
                                $result ) )
                {
                    $summary[] = $err;
                }
                else
                {
                    if ( $result )
                    {
                        $summary[] = JText::_( 'OTRS_CONNECTED' );
                    }
                    else
                    {
                        $summary[] = JText::_( 'OTRS_GATEWAY_AUTH' );
                    }
                    if ( !function_exists( 'json_encode' ) )
                    {
                        $summary[] = JText::_( 'OTRS_GATEWAY_NEED_JSON' );
                    }
                }
            }

        }
        else
        {
            $summary[] = JText::_( 'OTRS_NO_SOAP' );
        }

        $this->assignRef( 'summary', $summary );

        parent::display($tpl);
    }
}

