<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Installer for A/B Test module
 *
 * @package		Ab_test
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		Mark J. Reeves
 * @link		http://markjreeves.com/ab-test-expressionengine
 */
class Ab_test_upd {
		
	var $version        = '0.2'; 
	var $module_name = "Ab_test";
	
    function Ab_test_upd( $switch = TRUE ) 
    { 
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
    } 

    /**
     * Installer for the Rating module
     */
    function install() 
	{				
						
		$data = array(
			'module_name' 	 => 'Ab_test',
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
		);

		$this->EE->db->insert('modules', $data);
						
		if ( ! $this->EE->db->table_exists('exp_ab_test'))
		{
			$this->EE->db->query("CREATE TABLE exp_ab_test (
				              `ab_test_case_id`	int(10)	unsigned	NOT NULL	auto_increment,
				              `test_name`		varchar(250)		NOT NULL	default '',
				              `case_name`		varchar(250)		NOT NULL	default '',
				              `hits`			int(10)				NOT NULL	default 1,
				              PRIMARY KEY		(`ab_test_case_id`)
							)");
		}
		if ( ! $this->EE->db->table_exists('exp_ab_test_tracking'))
		{
			$this->EE->db->query("CREATE TABLE exp_ab_test_tracking (
				              `ab_test_tracking_id`	int(10)	unsigned	NOT NULL	auto_increment,
				              `ab_test_case_id`	int(10)	unsigned	NOT NULL	default 0,
				              `test_name`		varchar(250)		NOT NULL	default '',
				              `case_name`		varchar(250)		NOT NULL	default '',
				              `href_action`		varchar(1000)		NOT NULL	default '',
				              `hits`			int(10)				NOT NULL	default 1,
				              PRIMARY KEY		(`ab_test_tracking_id`)
							)");
		}
		
		/* Actions */

																
		return TRUE;
	}


	function uninstall() 
	{ 				
		$this->EE->load->dbforge();
		
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->module_name));
		
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
		
		$this->EE->db->where('module_name', $this->module_name);
		$this->EE->db->delete('modules');
		
		$this->EE->db->where('class', $this->module_name);
		$this->EE->db->delete('actions');
		
		$this->EE->db->where('class', $this->module_name.'_mcp');
		$this->EE->db->delete('actions');
		
		if ( $this->EE->db->table_exists('exp_ab_test_tracking'))
		{
			$this->EE->db->query("DROP TABLE exp_ab_test_tracking");
		}
		
		if ( $this->EE->db->table_exists('exp_ab_test'))
		{
			$this->EE->db->query("DROP TABLE exp_ab_test");
		}
		
		return TRUE;
	}
	
	function update($current = '')
	{
		if ($current == $this->version)
		{
			return FALSE;
		}
			
		if ($current < 2.0) 
		{

		} 
		
		return TRUE; 
	}
    
}

/* End of file upd.ab_test.php */ 
/* Location: ./system/expressionengine/third_party/ab_test/upd.ab_test.php */ 