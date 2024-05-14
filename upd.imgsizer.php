<?php

class Imgsizer_upd {

	var $version = '4.1.0';

	function install()
	{
		// install the module
		ee()->db->insert('modules', array(
			'module_name' => 'Imgsizer',
			'module_version' => $this->version,
			'has_cp_backend' => 'n',
			'has_publish_fields' => 'n',
		));

		// install the RTE button
		ee()->db->insert('rte_tools', array(
			'name' => 'Imgsizer',
			'class' => 'Imgsizer_rte',
			'enabled' => 'Y',
		));

		// add hook reference
		$data = array(
			'class'		=> 'Imgsizer_ext',
			'method'	=> 'channel_entries_tagdata_end',
			'hook'		=> 'channel_entries_tagdata_end',
			'priority'	=> 10,
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);
		ee()->db->insert('extensions', $data);

		// replace the image button with our button in the
		// rte toll set
		$sql = "UPDATE exp_rte_toolsets
			SET tools = CONCAT(REPLACE(tools, CONCAT('|',
				(SELECT tool_id FROM (SELECT tool_id FROM exp_rte_tools WHERE NAME = 'Image') AS a)
				), ''), '|',
				(SELECT tool_id FROM (SELECT tool_id FROM exp_rte_tools WHERE NAME = 'Imgsizer') AS b)
				 )
			WHERE enabled = 'Y'";
		ee()->db->query($sql);

		return true;
	}

	function uninstall()
	{
		// delete the module
		ee()->db->delete('modules', array(
			'module_name' => 'Imgsizer',
		));

		// delete hook
		ee()->db->delete('extensions', array(
			'class' => 'Imgsizer_ext',
		));

		// delete RTE button
		ee()->db->delete('rte_tools', array(
			'name' => 'Imgsizer',
		));

		return true;
	}

}
