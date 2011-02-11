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
 * Class to view ticket lists
 *
 * @static
 * @package     Joomla
 * @subpackage  OTRS Gateway
 * @since 1.5
 */
class OTRSGatewayViewTickets extends JView
{
    function display($tpl = null)
    {
        global $mainframe;
        $model = &$this->getModel();
        $listType = JRequest::getVar( 'listtype' );
        $session = JFactory::getSession();
        $showClosed = false;
        if ( JRequest::getVar( 'closed' ) )
        {
            $showClosed = true;
            $session->set( 'showclosed', true, 'otrsgateway' );
        }
        else if ( JRequest::getVar( 'open' ) )
        {
            $session->clear( 'showclosed', 'otrsgateway' );
        }
        else if ( $session->has( 'showclosed', 'otrsgateway' ) )
        {
            $showClosed = true;
        }
        $tickets = null;
        switch ( $listType )
        {
            case "company":
                $tickets = $showClosed ? $model->getCompanyTickets() : $model->getCompanyOpenTickets();
                break;
            default:
                $tickets = $showClosed ? $model->getMyTickets() : $model->getMyOpenTickets();
        }
        $this->assignRef( 'tickets', $tickets );
        $this->assignRef( 'listType', $listType );

        $uri = JFactory::getURI();
        $toggleLinkText = '';
        if ( $showClosed )
        {
            $uri->setVar( 'open', 1 );
            $uri->setVar( 'closed', '' );
            $toggleLinkText = JText::_( 'COM_OTRSGATEWAY_HIDE_CLOSED' );
        }
        else
        {
            $uri->setVar( 'open', '' );
            $uri->setVar( 'closed', 1 );
            $toggleLinkText = JText::_( 'COM_OTRSGATEWAY_SHOW_CLOSED' );
        }
        $url = $uri->toString();
        $this->assignRef( 'toggleLink', $url );
        $this->assignRef( 'toggleLinkText', $toggleLinkText );

        parent::display($tpl);
    }
}

