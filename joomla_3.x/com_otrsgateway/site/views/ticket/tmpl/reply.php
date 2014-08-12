<?php 
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die('Restricted access'); 
JHtml::_('behavior.framework');
$editorJS = "function validateEditor(){
var content = '';
";
if ( isset( $this->editor ) ) {
    $this->editor->save( 'otrsreplytext' );
    $editorJS .= "content = " . $this->editor->getContent( 'otrsreplytext' ) . "\n";
} else {
    $editorJS .= "content = $('otrsreplytext').value.trim();\n";
}
$editorJS .= "return content;\n}\n";
$doc = JFactory::getDocument();
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
		<td>
		</td>
		<td>
			<p id="errormsg">&nbsp;</p>
		</td>
	</tr>
    <tr>
        <td width="100">
            <label for="otrsreplytext">
                <strong><?php echo JText::_( 'COM_OTRSGATEWAY_REPLY' ); ?>:</strong>
            </label>
        </td>
        <td width="500">
<?php 
// if ( isset( $this->editor ) )
// {
    // echo $this->editor->display('otrsreplytext', '' , '450', '200', '75', '10', false, 'otrsreplytext', null, null, array('mode'=>'simple', 'advimg' => 0, 'theme' => 'simple'));
// }
// else
// {
    echo '<textarea name="otrsreplytext" id="otrsreplytext" cols="60" rows="10" style="height:auto!important; width: 80%;"></textarea>';
// }
?>
        </td>
    </tr>
<?php
if ( ! empty($this->priorityList) )
{
?>
    <!--<tr>
        <td>
            <label for="priorityID">
                <strong><?php echo JText::_( 'COM_OTRSGATEWAY_PRIORITY' ); ?>:</strong>
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
    </tr>-->
<?php } ?>
    <tr>
        <td>
            <label for="StateID">
                <strong><?php echo JText::_( 'COM_OTRSGATEWAY_NEXT_STATE' ); ?>:</strong>
            </label>
        </td>
        <td>
            <select name="StateID" id="StateID" style="width: 40%;">
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
<input type="hidden" name="priorityID" value="3" />
<input type="hidden" name="ticketID" value="<?php echo htmlspecialchars($this->ticket->TicketID);?>" />
<input type="hidden" name="option" value="com_otrsgateway" />
<input type="hidden" name="task" value="reply" />
<input type="hidden" name="view" value="ticket" />
<input type="hidden" name="format" value="raw" />
<input type="hidden" name="formtoken" value="<?php echo $this->formToken; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<table class="adminform" style="vertical-align:top;width:100%">
    <tr style="vertical-align:baseline">
        <td width="100">
            <strong><?php echo JText::_( 'COM_OTRSGATEWAY_ATTACHMENTS' ); ?>:</strong>
        </td>
        <td width="500">
<ul id="attachmentlist"></ul>
<form enctype="multipart/form-data" method="post" action="index.php" id="attform" name="attform" target="attpost">
<input type="file" name="attachment" /> 
<input type="submit" value="<?php echo JText::_( 'COM_OTRSGATEWAY_ADD' ); ?>" class="button"  />
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

<form id="replyForm" action="<?php echo JRoute::_('index.php'); ?>" method="get" onsubmit="return false">
<div id="loading" style="display: none; float: left; padding: 0 10px;"><img src="<?php echo JURI::root(); ?>components/com_otrsgateway/views/img/ajax-loader.gif"/></div>
<input name="submit" id="submitButton" class="button" type="button" value="<?php echo JText::_('COM_OTRSGATEWAY_SUBMIT'); ?>" />
<a href="#" onclick="parent.cancelReply(document.forms['otrsReplyForm'].elements['formtoken'].value);" style="padding-left: 10px;"><?php echo JText::_( 'COM_OTRSGATEWAY_CANCEL' ); ?></a>
</form>

</div>
<script language="javascript" type="text/javascript">
<!--
	jQuery( document ).ready(function(e) {
		jQuery('#submitButton').click(function(e2) {
			jQuery('#loading').show();
			jQuery('#error').remove();
			if (locked) { return void(0); }
			locked = true;
			jQuery(this).css('opacity', '10%');
			jQuery(this).attr('disabled', 'disabled');
			<?php if ( isset( $this->editor ) ) { echo $this->editor->save('otrsreplytext'); } ?>
			if (!validateEditor()) {
				jQuery('#errormsg').append('<span id="error"><?php echo JText::_( 'COM_OTRSGATEWAY_ALERT_PROVIDE_REPLY' ); ?></span>');
				//alert('<?php echo JText::_( 'COM_OTRSGATEWAY_ALERT_PROVIDE_REPLY' ); ?>');
				jQuery(this).removeAttr('disabled');
				jQuery(this).css('opacity', '100%');
				locked = false;
				jQuery('#loading').hide();
			} else {
				<?php if ( isset( $this->editor ) ): ?>
					if (!jQuery('#otrsreplytext').val()) {
						jQuery('#otrsreplytext').val() = <?php echo $this->editor->getContent('otrsreplytext'); ?>
					}
				<?php endif; ?>
				
				$('otrsReplyForm').set('send', {
					noCache: true,
					onComplete: function(resp) {
						var obj = JSON.decode(resp);
						window.parent.closeReply(obj.error,document.forms['otrsReplyForm'].elements['formtoken'].value);
						jQuery('#loading').hide();
					},
					onFailure: function(resp) {
						alert("<?php echo JText::_('COM_OTRSGATEWAY_ALERT_SUBMISSION_FAILED');?>");
						window.parent.closeReply(null,document.forms['otrsReplyForm'].elements['formtoken'].value);
						jQuery('#loading').hide();
					}
				}).send();
			}
		});
	});

    function addAttachment(error, id, name) {
        if (!error) {
            // Looks OK
            // Add the file inside the attachmentlist list
            var newEl = new Element('li', { 'id': 'att-' + id  });
            newEl.appendText(name + ' ');
            var newLink = new Element('a', { 'href': 'javascript:delAttachment("' + id + '")', 'onclick':'delAttachment("' + id + '")', 'class': 'small button' });
            newLink.appendText('<?php echo JText::_( 'COM_OTRSGATEWAY_REMOVE' ); ?>');
            newLink.inject(newEl);
            newEl.inject($('attachmentlist'));
            document.forms['attform'].reset();
        } else {
			console.log("error addAttachment: " + error + " id: " + id + " name: " + name);
		}
    }

    function delAttachment( id ) {
        document.forms['delattform'].elements['fileID'].value = id;
        $('delattform').set('send', {
            noCache: true,
            onComplete: function(resp){
                            var el = $('att-' + id);
                            if (el) { el.dispose(); }
                        }
        }).send();
    }
//-->
</script>
