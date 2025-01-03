<?php
/**
 * Installation file for stockablecustomfields
 *
 * @package 	stockablecustomfields.install
 * @author 		Sakis Terzis
 * @copyright 	Copyright (c) 2025 breakdesigns.net. All rights reserved.
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: script.php 2025-01-22 $
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die;

use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;

/**
 * Load the installer
 *
 * @copyright
 * @author 		Sakis Terz
 * @access 		public
 * @param
 * @return
 * @since 		1.5.3
 */
class plgVmCustomStockablecustomfieldsInstallerScript {

    /**
     * @var array
     * @since 1.0.0
     */
    protected array $printed_messages = [];

    /**
	 * Installation routine
	 *
	 * @copyright
	 * @author 		Sakis Terz
	 * @access 		public
	 * @param
	 * @return
	 * @since 		2.0
	 */
	public function install($parent)
    {
	}

	/**
	 * Update routine
	 *
	 * @copyright
	 * @author 		Sakis Terzis
	 * @todo
	 * @see
	 * @access 		public
	 * @param
	 * @return
	 * @since 		2.0
	 */
	public function update($parent)
    {
	}


    /**
     * Preflight routine executed before install and update
     *
     * @param        $type    string    type of change (install, update or discover_install)
     *
     * @return
     * @since         2.0
     * @copyright
     * @author        Sakis Terzis
     * @todo
     * @see
     * @access        public
     */
    public function preflight($type, $parent)
    {
        if ($type == 'update') {
            //store the milestone versions and the messages that each 1 will print
            $milestone_versions = [];
            $messages = [];
            //E.G. $messages['1.8.0']='New feature: Result page is now based on menu items.';
            $this->printed_messages = [];

            $oldRelease = $this->getParam('version');
            foreach ($milestone_versions as $m_v) {
                if (version_compare($oldRelease, $m_v) == -1) {
                    $this->printed_messages[] = $messages[$m_v];
                }
            }
        }
    }

	/**
	 * Postflight routine executed after install and update
	 *
	 * @copyright
	 * @author 		Sakis Terzis
	 * @todo
	 * @see
	 * @access 		public
	 * @param 		$type	string	type of change (install, update or discover_install)
	 * @return
	 * @since 		2.0
	 */
	public function postflight($type, $parent)
    {
		$db = Factory::getDbo();
		$status = new stdClass;
		$status->modules = [];
		$status->plugins = [];
		$status->templateoverrides= [];
		$src = $parent->getParent()->getPath('source');
		$manifest = $parent->getParent()->manifest;
		$plugins = $manifest->xpath('plugins/plugin');

		foreach ($plugins as $plugin)
		{
			$name = (string)$plugin->attributes()->plugin;
			$group = (string)$plugin->attributes()->group;

			if($name!='stockablecustomfields'){
				$path = $src.'/plugins/'.$group;
				if (Folder::exists($src.'/plugins/'.$group.'/'.$name))
				{
					$path = $src.'/plugins/'.$group.'/'.$name;
				}
				$installer = new Installer;
				$result = $installer->install($path);
			}else $result=true; //installed by the current manifest

			$query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element=".$db->quote($name)." AND folder=".$db->quote($group);
			$db->setQuery($query);
			$db->execute();
			$status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
		}

		$modules = $manifest->xpath('modules/module');

		foreach ($modules as $module)
		{
			$name = (string)$module->attributes()->module;
			$client = (string)$module->attributes()->client;
			if (is_null($client))
			{
				$client = 'site';
			}
			($client == 'administrator') ? $path = $src.'/administrator/modules/'.$name : $path = $src.'/modules/'.$name;

			if($client == 'administrator')
			{
				$db->setQuery("SELECT id FROM #__modules WHERE `module` = ".$db->quote($name));
				$isUpdate = (int)$db->loadResult();
			}

			$installer = new Installer;
			$result = $installer->install($path);

			$status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
			if($client == 'administrator' && !$isUpdate)
			{
				$position ='cpanel';
				$db->setQuery("UPDATE #__modules SET `position`=".$db->quote($position).",`published`='1' WHERE `module`=".$db->quote($name));
				$db->execute();

				$db->setQuery("SELECT id FROM #__modules WHERE `module` = ".$db->quote($name));
				$id = (int)$db->loadResult();

				$db->setQuery("INSERT IGNORE INTO #__modules_menu (`moduleid`,`menuid`) VALUES (".$id.", 0)");
				$db->execute();
			}
		}

		$template_overrides = $manifest->xpath('templateoverrides/templateoverride');

		foreach($template_overrides as $template_override){
			$name = (string)$template_override->attributes()->name;
			$client = (string)$template_override->attributes()->client;
			$source=$src.DIRECTORY_SEPARATOR.'templateoverrides'.DIRECTORY_SEPARATOR.$name;

			if (is_null($client))$client = 'site';
			($client == 'administrator') ? $destination = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'templates' : $path =JPATH_SITE.DIRECTORY_SEPARATOR.'templates';

			$templates=Folder::folders($destination);
			foreach ($templates as $tmpl){
				$final_destination=$destination.DIRECTORY_SEPARATOR.$tmpl.DIRECTORY_SEPARATOR.'html';
				$this->recurse_copy($source,$final_destination);
			}
		}

		$this->installationResults($status,$type);
	}


	/**
	 * copy all $src to $dst folder and remove it
	 *
	 * @author Max Milbers-Sakis Terz
	 * @param String $src path
	 * @param String $dst path
	 * @param String $type modules, plugins, languageBE, languageFE
	 */
	private function recurse_copy($src,$dst,$last=false )
    {
        $dst_exist = is_dir(Path::clean($dst));
        if (!$dst_exist) {
            $dst_exist = Folder::create($dst);
        }
        $dir = opendir($src);

        if (is_resource($dir) && $dst_exist) {
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src . DIRECTORY_SEPARATOR . $file)) {
                        $this->recurse_copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                    } else {
                        if (is_file($dst . DIRECTORY_SEPARATOR . $file)) {
                        }
                        if (!File::copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file)) {
                            //$app = Factory::getApplication();
                            //$app -> enqueueMessage('Couldnt copy '.$src .DIRECTORY_SEPARATOR. $file.' to '.$dst .DIRECTORY_SEPARATOR. $file);
                        }
                    }
                }
            }
        }
		if(is_resource($dir)) {
		    closedir($dir);
        }
		if (is_dir($src) && $last) {
		    Folder::delete($src);
        }
	}

	/**
	 * Get a variable from the manifest file (actually, from the manifest cache).
	 */
	function getParam( $name ) {
		$db = Factory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM #__extensions WHERE element = "stockablecustomfields"');
		$manifest = json_decode( $db->loadResult(), true );
		return $manifest[ $name ];
	}

	private function installationResults($status,$type)
	{
		$language = Factory::getApplication()->getLanguage();
		$language->load('plg_vmcustom_stockablecustomfields');
		$rows = 0;

		if($type=='update'){
			$status_type=Text::_('PLG_STOCKABLECUSTOMFIELDS_UPDATE_STATUS');
			$success_msg='<span style="color:#5cb85c">'.Text::_('PLG_STOCKABLECUSTOMFIELDS_SUCEESS').'<span>';
			$fail_msg='<span style="color:#ff0000">'.Text::_('PLG_STOCKABLECUSTOMFIELDS_NOT_UPDATED').'</span>';
		} else{
			$status_type=Text::_('PLG_STOCKABLECUSTOMFIELDS_INSTALLATION_STATUS');
			$success_msg='<span style="color:#5cb85c">'.Text::_('PLG_STOCKABLECUSTOMFIELDS_SUCEESS').'<span>';
			$fail_msg='<span style="color:#ff0000">'.Text::_('PLG_STOCKABLECUSTOMFIELDS_NOT_INSTALLED').'<span>';
		}
		?>
<?php
//if update messages
if(!empty($this->printed_messages)){?>
<div class="clr"></div>
<h3><?php echo Text::_('PLG_STOCKABLECUSTOMFIELDS_UPDATE_MESSAGES');?></h3>
<div id="system-message-container">
<dl id="system-message">
<dt class="message">Message</dt>
<dd class="message message">
<ul>
<?php
foreach ($this->printed_messages as $message){?>
<li><?php echo $message?></li>
<?php }?>
</ul>
</dd>
</dl>
</div>
<?php }?>

<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th class="title"><?php echo Text::_('PLG_STOCKABLECUSTOMFIELDS_EXTENSION'); ?></th>
			<th><?php echo Text::_('PLG_STOCKABLECUSTOMFIELDS_GROUP'); ?></th>
			<th width="30%"><?php echo $status_type; ?></th></tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<?php if (count($status->modules)): ?>

		<?php foreach ($status->modules as $module): ?>
		<tr class="row<?php echo(++$rows % 2); ?>">
			<td class="key"><?php echo $module['name']; ?> - module</td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td><strong><?php echo ($module['result'])?$success_msg:$fail_msg; ?></strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		<?php if (count($status->plugins)): ?>
		<?php foreach ($status->plugins as $plugin): ?>
		<tr class="row<?php echo(++$rows % 2); ?>">
			<td class="key"><?php echo ucfirst($plugin['name']); ?> - plugin</td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td><strong><?php echo ($plugin['result'])?$success_msg:$fail_msg; ?></strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
		<?php
	}
}
?>
