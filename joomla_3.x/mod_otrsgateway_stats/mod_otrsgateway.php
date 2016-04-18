<?php
/**
* @version     $Id$
* @package     Joomla
* @subpackage  OTRSGateway
* @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
*/
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( dirname(__FILE__).'/helper.php' );

if ($params->get('otrsgateway_summary_dia') || $params->get('otrsgateway_summary_stats') ) {
    $ObjViewSum = new OTRSGatewayViewSummary;
    $jinput = JFactory::getApplication()->input;
    $listType = $jinput->get( 'listtype', null, null );

    $tickets = null;
    switch ( $listType )
    {
        case "company":
            $tickets = $ObjViewSum->getCompanyTickets();
            break;
        default:
            $tickets = $ObjViewSum->getMyTickets();
    }
    
    if ( $tickets && count($tickets) ) {
        $stat_all = 0;
        $stat_new = 0;
        $stat_successful = 0;
        $stat_unsuccessful = 0;
        $stat_pending = 0;
        $max_bar_height = 210;
        $tickets_year = 0;
    
        $date = array();
        $bars = array();
        for($i=1; $i<13; $i++) {
            $date[$i] = $i;
            $bars[$i] = "<td class='bar_column'>";
        }
    
        foreach ($tickets as $t)
        {
            //Max 48 Chars for title
            if ( strlen(htmlspecialchars($t->Title)) > 48 ) {
                $CutTitle = substr( htmlspecialchars($t->Title),0,48 )."...";
            } else {
                $CutTitle = htmlspecialchars($t->Title);
            }
            
            //Build some stats here
            $stat_all++;
            switch ($t->State) {
                case "new":
                    $stat_new++;
                    break;
                case "pending reminder":
                    $stat_pending++;
                    break;
                case "pending auto close-":
                    $stat_pending++;
                    break;
                case "pending auto close+":
                    $stat_pending++;
                    break;
                case "closed successful":
                    $stat_successful++;
                    break;
                case "closed unsuccessful":
                    $stat_unsuccessful++;
                    break;
                case "open":
                    $stat_pending++;
                    break;
                default:
                    $stat_pending++;
            }
                
            //fill chart bars with tickets
            foreach ($date as $d) {
                if (date('Y.' . str_pad($d, 2 ,'0', STR_PAD_LEFT)) === date( 'Y.m',htmlspecialchars($t->CreateTimeUnix) )) {
                    $bars[$d] .= "<a href='".JRoute::_('index.php?view=ticket&ticketID='.$t->TicketID)."'><div class='bar' title='".$CutTitle."'></div></a>";
                    $tickets_year++;
                }
            }
            
        }
        
        //set the size the tickets should have in the chart bar
        $max_ticket_height = round($max_bar_height/$tickets_year);
        echo '
            <style type="text/css">
                .bar {
                    height:'.$max_ticket_height.'px;
                }
            </style>
        ';
        
        //close chart bars
        for($i=1; $i<13; $i++) {
            $month = date('Y') . "-" . str_pad($i, 2 ,'0', STR_PAD_LEFT) . "-" . "01";
            $bars[$i] .= date('M',strtotime($month))."</td>";
        }
        
        echo "<div class='ticket-mod-summary'>";
        
        //print stats
        if ($params->get('otrsgateway_summary_stats')) {
            echo "<div id='ticket-stats'><h4>" . JText::_( 'COM_OTRSGATEWAY_STAT_LABEL' ) . "</h4><table height='220' width='100%' cellpadding='0' cellspacing='0'>";
            echo "<tbody";
            echo "<tr><td><h6>" . JText::_( 'MOD_OTRSGATEWAY_STAT_ALL' ) . ":</h5></td><td><h5>" . $stat_all . "</h6></td></tr>";
            echo "<tr><td><h6>" . JText::_( 'MOD_OTRSGATEWAY_STAT_NEW' ) . ":</h5></td><td><h5>" . $stat_new . "</h6></td></tr>";
            echo "<tr><td><h6>" . JText::_( 'MOD_OTRSGATEWAY_STAT_OPEN' ) . ":</h5></td><td><h5>" . $stat_pending . "</h6></td></tr>";
            echo "<tr><td><h6>" . JText::_( 'MOD_OTRSGATEWAY_STAT_SUCCESSFUL' ) . ":</h5></td><td><h5>" . $stat_successful . "</h6></td></tr>";
            echo "<tr><td><h6>" . JText::_( 'MOD_OTRSGATEWAY_STAT_UNSUCCESSFUL' ) . ":</h5></td><td><h5>" . $stat_unsuccessful . "</h6></td></tr>";
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        }

        //print chart
        if ($params->get('otrsgateway_summary_dia')) {
            echo "<div id='ticket-dia'><h4>" . $tickets_year . " Tickets in " . date('Y') . ":</h4><table height='220' width='100%' cellpadding='0' cellspacing='0' align='right'><tbody><tr valign='bottom' height='210'>";
            foreach ($bars as $b) {
                echo $b;  
            }
            echo "</tr></tbody></table></div>";
        }
        
        echo "<div style='clear:both; padding: 10px;'>&nbsp;</div></div>";
       
    } 
?>
<style type="text/css">
.ticket-mod-summary .bar {
    margin-right: 1px;
    margin-left: 1px;
    z-index: 1;
    background-color: #2E9AFE;
    font-size: 0.8em;
    overflow: hidden;
}

.ticket-mod-summary .bar_column {
    text-align: center;
    border: 1px solid #e3e3e3;
    font-size: 12px;
    min-width: 37px;
}

.ticket-mod-summary .bar:hover {
    margin-right: 1px;
    z-index: 1;
    background-color: #ff5151;
}

.ticket-mod-summary #ticket-stats table tbody tr {
    border: 1px solid #e3e3e3;
}

.ticket-mod-summary #ticket-stats table tbody tr td {
    padding-left: 5px;
}

<?php 
    if ($params->get('otrsgateway_summary_stats') && $params->get('otrsgateway_summary_dia')) {
        echo '
            .ticket-mod-summary #ticket-stats {
                width: 30%;
                float: left;
                padding-right: 5px;
            }

            .ticket-mod-summary #ticket-dia {
                width: auto;
                float: right;
                padding-left: 5px;
            }

            @media (max-width: 979px) {
                .ticket-mod-summary #ticket-stats {
                    width: 100%;
                    float: none;
                    padding: 0;
                }

                .ticket-mod-summary #ticket-dia {
                    width: 100%;
                    float: none;
                    padding: 0;
                }
            }
        ';
    } else {
        if ($params->get('otrsgateway_summary_stats')) {
            echo '
                .ticket-mod-summary #ticket-stats {
                    width: 100%;
                }
            ';
        }
        
        if ($params->get('otrsgateway_summary_dia')) {
            echo '
                .ticket-mod-summary #ticket-dia {
                    width: 100%;
                }
            ';
        }
    }
}
?>
</style>