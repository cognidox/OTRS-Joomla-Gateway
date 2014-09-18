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

$result_dia = "";
$max_ticket_height = 0;

$stat_all = 0;
$stat_new = 0;
$stat_successful = 0;
$stat_unsuccessful = 0;
$stat_pending = 0;

if ($params->get('otrsgateway_summary_dia') || $params->get('otrsgateway_summary_stats') ) {
    if ( $tickets && count($tickets) ) {
        $max_bar_height = 210;
        $max_ticket_height = round($max_bar_height/count($tickets));
        $tickets_year = 0;
        
        for($i=1; $i<13; $i++) {
            $barHeight = 0;
            $date= "Y.".str_pad($i, 2 ,'0', STR_PAD_LEFT);
            $tickets_month = 0;
            $result_dia .= "<td class='bar_column'>";
            foreach ($tickets as $t) {
                
                //Build some stats here
                if ($i === 1) {
                    $stat_all++;
                    switch ($t->State) {
                        case "new":
                            $stat_new++;
                            break;
                        case "pending reminder":
                            $stat_pending++;
                            break;					break;
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
                }
                
                if ( date($date) === date( 'Y.m',htmlspecialchars($t->CreateTimeUnix) ) ) {
                
                    //Max 48 Chars for title
                    if ( strlen(htmlspecialchars($t->Title)) > 48 ) {
                        $CutTitle = substr( htmlspecialchars($t->Title),0,48 )."...";
                    } else {
                        $CutTitle = htmlspecialchars($t->Title);
                    }
                    
                    $tickets_month++;
                    $tickets_year++;
                    $result_dia .= "<a href='".JRoute::_('index.php?view=ticket&ticketID=' . $t->TicketID)."'><div class='bar' title='" . $CutTitle . "'></div></a>";
                }   
            }
            $month = date('Y') . "-" . str_pad($i, 2 ,'0', STR_PAD_LEFT) . "-" . "01";
            $result_dia .= date('M',strtotime($month))."</td>";
        }
        $max_ticket_height = round($max_bar_height/$tickets_year);
    } else {
        for($i=1; $i<13; $i++) {
            $month = date('Y') . "-" . str_pad($i, 2 ,'0', STR_PAD_LEFT) . "-" . "01";
            $result_dia .= "<td class='bar_column'>" . date('M',strtotime($month)) . "</td>";
        }
    }
}
?>
<style type="text/css">
.bar {
    margin-right: 1px;
    margin-left: 1px;
    z-index: 1;
    background-color: #2E9AFE;
    font-size: 0.8em;
    overflow: hidden;
    height: <?php echo $max_ticket_height."px"; ?> ;
}

.bar_column {
    text-align: center;
    border: 1px solid #e3e3e3;
    font-size: 12px;
    min-width: 37px;
}

.bar:hover {
    margin-right: 1px;
    z-index: 1;
    background-color: #ff5151;
}

<?php 
if ($params->get('otrsgateway_summary_stats') && $params->get('otrsgateway_summary_dia')) {
    echo '
        #ticket-stats {
            width: 30%;
            float: left;
            padding: 5px;
        }

        #ticket-dia {
            width: auto;
            float: right;
            padding: 5px;
        }
        
        #ticket-stats table tbody tr {
            border: 1px solid #e3e3e3;
        }
        
        #ticket-stats table tbody tr td {
            padding-left: 5px;
        }

        @media (max-width: 979px) {
            #ticket-stats {
                width: 100%;
                float: none;
                padding: 0;
            }

            #ticket-dia {
                width: 100%;
                float: none;
                padding: 0;
            }
        } 
    ';
} else {
    if ($params->get('otrsgateway_summary_stats')) {
        echo '
            #ticket-stats {
                width: 100%;
                font-size: 12px;
            }
            
            #ticket-stats table tbody tr {
                border: 1px solid #e3e3e3;
            }
            
            #ticket-stats table tbody tr td {
                padding-left: 5px;
            }
        ';
    }
    
    if ($params->get('otrsgateway_summary_dia')) {
        echo '
            #ticket-dia {
                width: 100%;
            }
        ';
    }
}
?>
</style>
<div class="ticket-summary">
    <?php 
        if ($params->get('otrsgateway_summary_stats')) {
            echo "<div id='ticket-stats'><h4>" . JText::_( 'MOD_OTRSGATEWAY_STAT_LABEL' ) . "</h4><table height='220' width='100%' cellpadding='0' cellspacing='0'>";
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
        
        if ($params->get('otrsgateway_summary_dia')) {
            echo "<div id='ticket-dia'><h4>" . $tickets_year . " Tickets in " . date('Y') . ":</h4><table height='220' width='100%' cellpadding='0' cellspacing='0' align='right'><tbody><tr valign='bottom' height='210'>";
            echo $result_dia;
            echo "</tr></tbody></table></div>";
        }
    ?>
</div>
<div style="clear:both; padding: 15px;">&nbsp;</div>