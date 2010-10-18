<?php
/**
 * Copyright (c) 2010 Cognidox Ltd 
 * http://www.cognidox.com/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


$app = JFactory::getApplication();

$plugins = array('search_otrsgateway', 'authentication_otrsgateway'
                );

$db = JFactory::getDBO();
foreach ($plugins as $plugin)
{
    $p_dir = $this->parent->getPath('source').DS.'plugins'.DS.$plugin;

    $package = array();
    $package['packagefile'] = null;
    $package['extractdir'] = null;
    $package['dir'] = $p_dir;
    $package['type'] = JInstallerHelper::detectType($p_dir);

    $installer = new JInstaller();

    // Install the package
    if (!$installer->install($package['dir']))
    {
        // There was an error installing the package
        $msg = JText::sprintf('INSTALLEXT', JText::_($package['type']), JText::_('Error'));
        $app->enqueueMessage($msg);
        $result = false;
    } else {
        // Package installed sucessfully
        $msg = JText::sprintf('INSTALLEXT', JText::_($package['type']), JText::_('Success'));
        $app->enqueueMessage($msg);
        $result = true;

        // Enable the installed plugin
        $plgParts = explode("_", $plugin);
        $query = "UPDATE #__plugins SET published = 1 WHERE folder = '" .
                 $plgParts[0] . "' AND element = '" . $plgParts[1] . "'";
        $db->setQuery( $query );
        $db->query();
    }
}


function com_install()
{
    echo "<p>The OTRS Gateway component and search plugin have both been installed.</p>";
    return true;
}

?>
