<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die('Restricted access');
require_once( JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'fieldhelper.php' );

JHtml::_('behavior.framework');
JHTML::_('behavior.modal');

$attachmentIndex = array();
?>
<script type="text/javascript">
<!--
function closeReply(err, token) {
    cancelReply(token);
    if (err) {
        alert(err);
    } else {
        var loc = window.location;
        loc.hash = '#endticket';
        window.location.assign(loc.toString());
        window.location.reload(true);
    }
}
function cancelReply(token) {
    SqueezeBox.close();
    new Request({
        url: '<?php echo $this->delAttLink; ?>' + '&formtoken=' + token, 
        method: 'get',
        async: false}).send();
}
 -->
</script>

<h1 id="otrs-ticket-heading" class="componentheading">[<?php echo htmlspecialchars(trim($this->ticket->TicketNumber)); ?>]
<?php echo htmlspecialchars(trim($this->ticket->Title)); ?></h1>

<div class="contentpaneopen otrs-ticket-status">
<div class="article-tools"><div class="article-meta">

<table class="small" id="otrs-ticket-status-table">
<tr>
<td class="otrs-ticket-status-col1"><b><?php echo JText::_('COM_OTRSGATEWAY_CREATED'); ?>:</b> <?php echo htmlspecialchars($this->ticket->Created); ?></td>
<td><b><?php echo JText::_('COM_OTRSGATEWAY_UPDATED');?>:</b> <?php echo htmlspecialchars($this->ticket->Changed); ?></td></tr>
<tr>
<td class="otrs-ticket-status-col1"><b><?php echo JText::_('COM_OTRSGATEWAY_STATE');?>:</b> <?php 
echo translateOTRSTicketState(htmlspecialchars($this->ticket->State));
?>
</td>
<td><b><?php echo JText::_('COM_OTRSGATEWAY_PRIORITY');?>:</b> <?php echo htmlspecialchars($this->ticket->Priority); ?></td></tr>
<tr>
<td colspan="2">
<b><?php echo JText::_('COM_OTRSGATEWAY_FROM');?>:</b> <?php echo htmlspecialchars($this->ticket->Main->From); ?></td></tr>
<tr>
<td colspan="2">
<b><?php echo JText::_('COM_OTRSGATEWAY_QUEUE');?>:</b> <?php echo htmlspecialchars($this->ticket->Queue); ?></td>
</tr>
</table>

<div id="otrs-ticket-top-reply">
<a class="modal button" href="index.php?option=com_otrsgateway&task=replyForm&ticketID=<?php echo $this->ticket->TicketID; ?>" rel="{handler:'iframe',size:{x:650,y:450},ajaxOptions:{}}"><?php echo JText::_('COM_OTRSGATEWAY_REPLY');?></a>
</div>
</div></div></div>


<?php if (! empty($this->ticket->Attachments) )
{
?>
<div class="small otrs-attachment-box" id="otrs-top-attachments">
<span><b><?php echo JText::_('COM_OTRSGATEWAY_ATTACHMENTS'); ?>:</b></span><br />
<?php
    foreach ($this->ticket->Attachments as $atm)
    {  
        $atmsHtml = '';
        foreach ($atm->Atms as $aid => $a)
        {
            $link = JRoute::_('index.php?option=com_otrsgateway&view=attachment&ArticleID=' . $atm->ArticleID . '&AtmID=' . $aid);
            $atmsHtml .= '<a href="' . $link . '">' .
                         htmlspecialchars($a->Filename) . '</a> (' .
                         htmlspecialchars($a->Filesize) . ')<br />';
        }
        echo $atmsHtml;
        $attachmentIndex['a_' . $atm->ArticleID] = $atmsHtml;
    }
?>
</div>
<?php } ?>


<h2><?php echo JText::_('COM_OTRSGATEWAY_DESCRIPTION'); ?></h2>
<div id="otrs-ticket-description" class="contentpaneopen otrs-comment-text">
<?php
    if ($this->ticket->Main->Type == 'text/html') {
        echo strip_tags($this->ticket->Main->Body, $this->allowedTags);
    } else {
        echo '<div class="otrs-monospace-text">' . htmlspecialchars($this->ticket->Main->Body) . '</div>';
    }
?>
</div>


<?php
if ( ! empty( $this->ticket->ArticleIndex ) )
{
    $user =& JFactory::getUser();
?>
<hr />
<h2><?php echo JText::_('COM_OTRSGATEWAY_COMMENTS'); ?></h2>
<div class="contentpaneopen" id="otrs-ticket-comments">
<?php
    $aid = 1;
    foreach ($this->ticket->ArticleIndex as $article)
    {
        $extraclass = 'otrs-reply-self';
        if ( preg_match( '/^.+?<([^>]+)>/', $article->From, $matches ) )
        {
            if ( strtolower( $matches[1] ) != strtolower( $user->email ) )
            {
                $extraclass = 'otrs-reply-other';
            }
        }
        echo '<div class="otrs-ticket-comment ' . $extraclass . '"><div class="article-tools"><div class="article-meta otrs-comment-from">' .
             '<b>' . htmlspecialchars($article->From) . '</b> ' .
             htmlspecialchars($article->Created) . '</div>' .
             '</div>';
        echo sprintf( '<div class="otrs-comment-text" id="otrs-comment-%s">', $aid);
        if ($article->Type == 'text/html') {
            echo strip_tags($article->Body, $this->allowedTags);
        } else {
            echo '<div class="otrs-monospace-text">' . htmlspecialchars($article->Body) . '</div>';
        }
        echo '</div>';
        if (array_key_exists( 'a_' . $article->ArticleID, $attachmentIndex) )
        {
            echo '<div class="otrs-attachment-box">' . $attachmentIndex['a_' . $article->ArticleID] .
                 '</div>';
        }
        echo '</div>';
        $aid += 1;
    }
?>
</div>
<?php } ?>
<div class="contentpaneopen">
<a name="endticket"></a>
<hr />
<a class="modal button" href="index.php?option=com_otrsgateway&task=replyForm&ticketID=<?php echo $this->ticket->TicketID; ?>" rel="{handler:'iframe',size:{x:650,y:450},ajaxOptions:{}}"><?php echo JText::_('COM_OTRSGATEWAY_REPLY'); ?></a>
</div>



