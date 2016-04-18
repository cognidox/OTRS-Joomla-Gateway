<?php
/**
* @version     $Id$
* @package     Joomla
* @subpackage  OTRSGateway
* @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

class OTRSGatewayViewSummary
{
    var $_userID = null;
    var $_customerIDs = null;
    var $_gateway = null;

    function __construct()
    {
        $userData = OTRSGatewayUserHelper::getOTRSUserData();
        if ($userData != null)
        {
            $this->_userID = $userData[0];
            $this->_customerIDs = $userData[1];
        }
        $this->_gateway = new OTRSGatewayRPCHelper();
    }
    
    function getMyOpenTickets()
    {
        return $this->getTickets('Open');
    }

    function getMyTickets()
    {
        return $this->getTickets();
    }

    function getCompanyTickets()
    {
        return $this->getTickets( false, false );
    }

    function getCompanyOpenTickets()
    {
        return $this->getTickets( 'Open', false );
    }

    function getTickets( $state = false, $userOnly = true )
    {
        if ( !$this->_userID )
        {
            return null;
        }
        $tickets = array();
        $vars = array(
            'Result' => array( 'ARRAY', XSD_STRING ),
            'CustomerUserID' => array( $this->_userID, XSD_STRING ),
            'Permission' => array( 'ro', XSD_STRING )
            );
        if ( $state )
        {
            $vars['StateType'] = array( $state, XSD_STRING );
        }
        if ( !$userOnly )
        {
            $vars['CustomerID'] = array ( $this->_customerIDs[0], XSD_STRING );
        }
        else
        {
            $vars['CustomerUserLogin'] = array( $this->_userID, XSD_STRING );
        }
        $result = null;
        
        if ( $err = $this->_gateway->callOTRS(
                        'JoomlaGatewayObject', 'TicketSearch', $vars,
                        $result ) )
        {
            // Failed to find anything
            return null;
        }
        return $result;
    }
}
 
?>