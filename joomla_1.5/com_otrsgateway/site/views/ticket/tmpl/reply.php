<?php 
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die('Restricted access'); 
JHTML::_('behavior.mootools');
$editorJS = "function validateEditor(form){
var content = '';
";
if ( isset( $this->editor ) ) {
    $this->editor->save( 'otrsreplytext' );
    $editorJS .= "content = " . $this->editor->getContent( 'otrsreplytext' ) . "\n";
} else {
    $editorJS .= "content = form.otrsreplytext.value.trim();\n";
}
$editorJS .= "return content;\n}\n";
$doc =& JFactory::getDocument();
$doc->addStyleSheet( 'templates/system/css/system.css' );
$doc->addStyleSheet( 'templates/system/css/general.css' );
$doc->addScriptDeclaration($editorJS);


?>
<script type="text/javascript">
<!--
var locked = false;
// -->
</script>

<div id="otrs-reply-form" class="contentpaneopen">

<form action="index.php" method="post" id="otrsReplyForm" name="otrsReplyForm">
<table class="adminform" style="vertical-align:top;width:600px">
    <tr>
        <td width="100">
            <label for="otrsreplytext">
                <strong><?php echo JText::_( 'OTRS_REPLY' ); ?>:</strong>
            </label>
        </td>
        <td width="500">
<?php 
if ( isset( $this->editor ) )
{
    echo $this->editor->display('otrsreplytext', '' , '450', '200', '75', '', false, array('mode'=>'simple', 'advimg' => 0, 'theme' => 'simple'));
}
else
{
    echo '<textarea name="otrsreplytext" id="otrsreplytext" cols="60" rows="10" style="height:auto!important"></textarea>';
}
?>
        </td>
    </tr>
<?php
if ( ! empty($this->priorityList) )
{
?>
    <tr>
        <td>
            <label for="priorityID">
                <strong><?php echo JText::_( 'OTRS_PRIORITY' ); ?>:</strong>
            </label>
        </td>
        <td>
            <select name="priorityID" id="priorityID">
<?php
    foreach ($this->priorityList as $key => $val)
    {
        echo '<option value="' . htmlspecialchars($key) . '"' .
             ($key == $this->ticket->PriorityID ? ' selected="selected"' : '') .
             '>' . htmlspecialchars($val) . '</option>';
    }
?>
            </select>
        </td>
    </tr>
<?php } ?>
    <tr>
        <td>
            <label for="StateID">
                <strong><?php echo JText::_( 'OTRS_NEXT_STATE' ); ?>:</strong>
            </label>
        </td>
        <td>
            <select name="StateID" id="StateID">
<?php
    foreach ($this->stateList as $key => $val)
    {
        echo '<option value="' . htmlspecialchars($key) . '"' .
             ($key == $this->ticket->StateID ? ' selected="selected"' : '') .
             '>' . htmlspecialchars($val) . '</option>';
    }
?>
            </select>
        </td>
    </tr>
</table>
<input type="hidden" name="ticketID" value="<?php echo htmlspecialchars($this->ticket->TicketID);?>" />
<input type="hidden" name="option" value="com_otrsgateway" />
<input type="hidden" name="task" value="reply" />
<input type="hidden" name="view" value="ticket" />
<input type="hidden" name="format" value="raw" />
<input type="hidden" name="formtoken" value="<?php echo $this->formToken; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<table class="adminform" style="vertical-align:top;width:100%">
    <tr style="vertical-align:top">
        <td width="100">
            <strong><?php echo JText::_( 'OTRS_ATTACHMENTS' ); ?>:</strong>
        </td>
        <td width="500">
<ul id="attachmentlist"></ul>
<form enctype="multipart/form-data" method="post" action="index.php" id="attform" name="attform" target="attpost">
<input type="file" name="attachment" /> 
<input type="submit" value="<?php echo JText::_( 'OTRS_ADD' ); ?>" class="button"  />
<input type="hidden" name="option" value="com_otrsgateway" />
<input type="hidden" name="task" value="addAttachment" />
<input type="hidden" name="format" value="raw" />
<input type="hidden" name="formtoken" value="<?php echo $this->formToken; ?>" />
</form>
<iframe id="attpost" name="attpost" style="display:none;width:1px;height:1px"></iframe>
        </td>
    </tr>
</table>

<form action="index.php" id="delattform" name="delattform" method="post">
<input type="hidden" name="option" value="com_otrsgateway" />
<input type="hidden" name="task" value="delAttachment" />
<input type="hidden" name="format" value="raw" />
<input type="hidden" name="fileID" value="" />
<input type="hidden" name="formtoken" value="<?php echo $this->formToken; ?>" />
</form>

<form action="#" method="get" onsubmit="return false">
<input name="submit" class="button" type="button" value="<?php echo JText::_('OTRS_SUBMIT'); ?>" onclick="submitbutton('submit');" />
<a href="#" onclick="parent.cancelReply(document.forms['otrsReplyForm'].elements['formtoken'].value);"><?php echo JText::_( 'OTRS_CANCEL' ); ?></a>
</form>

</div>
    
<script language="javascript" type="text/javascript">
<!--
    function submitbutton(pressbutton) {
        var form = document.otrsReplyForm;
        if (locked) { return false; }
        locked = true;
        if (pressbutton == 'submit') {
            <?php if ( isset( $this->editor ) ) {
                echo $this->editor->save('otrsreplytext');
            } ?>
            if (!validateEditor(form)) {
                locked = false;
                alert('<?php echo JText::_( 'OTRS_ALERT_PROVIDE_REPLY' ); ?>');
            } else {
                <?php if ( isset( $this->editor ) ): ?>
                if (!form.otrsreplytext.value) {
                    form.otrsreplytext.value = <?php echo $this->editor->getContent('otrsreplytext'); ?>
                }
                <?php endif; ?>

                $('otrsReplyForm').send({
                    onSuccess: function(resp){ var obj = Json.evaluate(resp); parent.closeReply(obj.error,document.forms['otrsReplyForm'].elements['formtoken'].value);},
                    onFailure: function(resp){ alert("<?php echo JText::_('OTRS_ALERT_SUBMISSION_FAILED');?>"); parent.closeReply(null,document.forms['otrsReplyForm'].elements['formtoken'].value);}
                });
            }
        }
    }

    function addAttachment(error, id, name) {
        if (!error) {
            // Looks OK
            // Add the file inside the attachmentlist list
            var newEl = new Element('li', { 'id': 'att-' + id  });
            newEl.appendText(name + ' ');
            var newLink = new Element('a', { 'href': 'javascript:delAttachment("' + id + '")', 'onclick':'delAttachment("' + id + '")', 'class': 'small button' });
            newLink.appendText('<?php echo JText::_( 'OTRS_REMOVE' ); ?>');
            newLink.inject(newEl);
            newEl.inject($('attachmentlist'));
            document.forms['attform'].reset();
        }
    }

    function delAttachment( id ) {
        document.forms['delattform'].elements['fileID'].value = id;
        $('delattform').send({
            onSuccess: function(){
                            var el = $('att-' + id);
                            if (el) { el.remove(); }
                        }
        });
    }
//-->
</script>
