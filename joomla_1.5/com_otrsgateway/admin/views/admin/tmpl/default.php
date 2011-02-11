<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die( 'Restricted access' );

?><p>
<?php echo JText::_( 'OTRS_GATEWAY_HELP' ); ?>
<h3><?php echo JText::_( 'OTRS_GATEWAY_SUMMARY' ); ?></h3>

<ul><?php
    foreach ( $this->summary as $item ) {
        echo "<li>" . htmlspecialchars($item) . "</li>";
    }
?></ul>
</p>
