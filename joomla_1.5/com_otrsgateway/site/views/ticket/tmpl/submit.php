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
    $editorJS .= "content = " . $this->editor->getContent( 'otrsmessage' ) . "\n";
} else {
    $editorJS .= "content = form.otrsmessage.value.trim();\n";
}
$editorJS .= "return content;\n}\n";
$doc =& JFactory::getDocument();
$doc->addScriptDeclaration($editorJS);

?>

<h1 class="componentheading"><?php echo JText::_( 'OTRS_NEW_TICKET' ); ?></h1>

<div id="otrs-submit-form" class="contentpaneopen">

<form action="index.php" method="post" id="otrsNewTicketForm" name="otrsNewTicketForm" enctype="multipart/form-data">

<table class="adminform" style="vertical-align:top;">
<?php if ( !empty( $this->ticketTypes ) ) : ?>
    <tr>
        <td>
            <label for="typeID">
                <strong><?php echo JText::_( 'OTRS_TYPE' ); ?>:</strong>
            </label>
        </td>
        <td>
            <select name="typeID" id="typeID">
                <option></option>
<?php
    foreach ($this->ticketTypes as $key => $val)
    {
        echo '<option value="' . htmlspecialchars($key) . '"' .
             '>' . htmlspecialchars($val) . '</option>';
    }
?>
            </select>
        </td>
    </tr>
<?php endif; ?>
    <tr>
        <td width="100">
            <label for="Dest">
                <strong><?php echo JText::_( 'OTRS_TO' ); ?>:</strong>
            </label>
        </td>
        <td width="500">
            <select name="Dest" id="Dest">
<?php
    foreach ($this->queues as $key => $val)
    {
        $selected = ($this->defaultDest == $key) ? ' selected="selected"' : '';
        echo '<option value="' . htmlspecialchars($key) . '"' . $selected .
             '>' . htmlspecialchars($val) . '</option>';
    }
?>
            </select>
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
        $selected = ($this->defaultPriority == $val) ? ' selected="selected"' : '';
        echo '<option value="' . htmlspecialchars($key) . '"' .
             $selected . '>' . htmlspecialchars($val) . '</option>';
    }
?>
            </select>
        </td>
    </tr>
<?php } ?>
    <tr>
        <td>
            <label for="Subject">
                <strong><?php echo JText::_( 'OTRS_SUBJECT' ); ?>:</strong>
            </label>
        </td>
        <td>
            <input type="text" name="Subject" class="inputbox" size="70" value="<?php echo htmlspecialchars($this->defaultSubject); ?>" />
        </td>
    </tr>
    <tr>
        <td width="100">
            <label for="otrsmessage">
                <strong><?php echo JText::_( 'OTRS_MESSAGE' ); ?>:</strong>
            </label>
        </td>
        <td width="500">
<?php 
    if ( isset( $this->editor ) )
    {
        echo $this->editor->display('otrsmessage', $this->defaultText , '450', '200', '75', '', false, array('mode'=>'simple', 'advimg' => 0, 'theme' => 'simple')); 
    }
    else
    {
        echo '<textarea name="otrsmessage" id="otrsmessage" rows="10" cols="60" style="height:auto!important"></textarea>';
    }
?>
        </td>
    </tr>
</table>
<input type="hidden" name="option" value="com_otrsgateway" />
<input type="hidden" name="task" value="submit" />
<input type="hidden" name="view" value="ticket" />
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

<form action="#" onsubmit="return false;">
<input name="submitBtn" class="button" type="button" value="<?php echo JText::_('OTRS_SUBMIT'); ?>" onclick="submitbutton('submit');" />
</form>

<form action="index.php" id="delattform" name="delattform" method="post">
<input type="hidden" name="option" value="com_otrsgateway" />
<input type="hidden" name="task" value="delAttachment" />
<input type="hidden" name="format" value="raw" />
<input type="hidden" name="fileID" value="" />
<input type="hidden" name="formtoken" value="<?php echo $this->formToken; ?>" />
</form>
</div>

<script language="javascript" type="text/javascript">
<!--
    function submitbutton(pressbutton) {
        var form = document.otrsNewTicketForm;
        if (pressbutton == 'submit') {
            <?php if ( isset( $this->editor ) ) { echo $this->editor->save( 'otrsmessage' ); } ?>
            // Check for required fields
            if (form.Dest.selectedIndex < 1) {
                alert('<?php echo JText::_( 'OTRS_ALERT_PROVIDE_TO' ); ?>');
                form.Dest.focus();
            } else if (form.Subject.value.trim() == '') {
                alert('<?php echo JText::_( 'OTRS_ALERT_PROVIDE_SUBJECT' ); ?>');
                form.Subject.focus();
            } else if (!validateEditor(form)) {
                alert('<?php echo JText::_( 'OTRS_ALERT_PROVIDE_MESSAGE' ); ?>');
            } else {
                document.otrsNewTicketForm.submit();
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
