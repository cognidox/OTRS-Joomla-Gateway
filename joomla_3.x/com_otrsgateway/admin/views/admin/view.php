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
require_once( JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'rpchelper.php' );

/**
 * @package     Joomla
 * @subpackage  OTRS Gateway
 * @since 1.5
 */
class OTRSGatewayViewAdmin extends JViewLegacy
{
    function display($tpl=null)
    {
        JToolBarHelper::title( JText::_( 'COM_OTRSGATEWAY' ), '' );
        JToolBarHelper::preferences( 'com_otrsgateway', '350' );

        $document = & JFactory::getDocument();
        $document->setTitle( JText::_('COM_OTRSGATEWAY') );

        // Work out the current status of the module
        $summary = array( JText::_( 'COM_OTRSGATEWAY_UNCONFIGURED' ) );
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
                        $summary[] = JText::_( 'COM_OTRSGATEWAY_CONNECTED' );
                    }
                    else
                    {
                        $summary[] = JText::_( 'COM_OTRSGATEWAY_AUTH' );
                    }
                    if ( !function_exists( 'json_encode' ) )
                    {
                        $summary[] = JText::_( 'COM_OTRSGATEWAY_NEED_JSON' );
                    }
                }
            }

        }
        else
        {
            $summary[] = JText::_( 'COM_OTRSGATEWAY_NO_SOAP' );
        }

		$this->summary=$summary;

        parent::display($tpl);
    }
}

