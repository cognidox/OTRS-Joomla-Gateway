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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * Script file of HelloWorld component
 */
class com_OTRSGatewayInstallerScript
{
    function install($parent) 
    {
        $manifest = $parent->get("manifest");
        $parent2 = $parent->getParent();
        $source = $parent2->getPath("source");
        $lang   = JFactory::getLanguage();
        $lang->load( 'com_otrsgateway.sys', $source.DS.'admin', $lang->getDefault(), false, false);

        $installer = new JInstaller();
        $plugin_names = array();
        // Install plugins
        foreach ($manifest->plugins->plugin as $plugin)
        {
            $attributes = $plugin->attributes();
            $plg = $source . DS . $attributes['folder'] . DS . $attributes['plugin'];
            $installer->install($plg);
            $plugin_names[] = $attributes['plugin'];
        }

        //
        $db = JFactory::getDbo();
        $tableExtensions = $db->nameQuote("#__extensions");
        $columnElement   = $db->nameQuote("element");
        $columnType      = $db->nameQuote("type");
        $columnEnabled   = $db->nameQuote("enabled");

        foreach ($plugin_names as $plugin)
        {
            $db->setQuery(
                "UPDATE $tableExtensions SET $columnEnabled=1 WHERE 
                $columnElement='$plugin' AND $columnType='plugin'" );
            $db->query();
        }
        echo JText::_( 'COM_OTRSGATEWAY_INSTALLED' );
    }

    function uninstall($parent) 
    {
        $plugins = array(
                    array( 'search', 'otrsgateway' ),
                    array( 'authentication', 'otrsgateway' ),
                    );

        $where = array();
        foreach ( $plugins as $plugin )
        {
            $where[] = vsprintf("(folder='%s' AND element='%s')", $plugin);
        }

        $query = 'SELECT id FROM #__plugins WHERE '.implode( ' OR ', $where );

        $dbo = JFactory::getDBO();
        $dbo->setQuery($query);
        $tmp = $dbo->loadResultArray();
        $plugins = array();
        if ( is_array( $plugins ) && count( $plugins ) )
        {
            foreach ( $tmp as $plugin )
            {
                $plugins[$plugin] = 0;
            }

            $model = JModel::getInstance( 'Plugins', 'InstallerModel' );
            $model->remove( $plugins );
        }
        echo '<p>' . JText::_( 'COM_OTRSGATEWAY_UNINSTALL_TEXT' ) . '</p>';
    }

    function preflight($type, $parent) 
    {
        $manifest = $parent->get("manifest");
        $parent2 = $parent->getParent();
        $source = $parent2->getPath("source");
        $lang   = JFactory::getLanguage();
        $lang->load( 'com_otrsgateway.sys', $source.DS.'admin', $lang->getDefault(), false, false);

        // Make sure JSON is installed
        if ( $type == 'install' )
        {
            if ( !function_exists( 'json_decode' ) )
            {
                echo '<p>' . JText::_( 'COM_OTRSGATEWAY_NO_JSON' ) . '</p>';
                return false;
            }
        }
    }
}
