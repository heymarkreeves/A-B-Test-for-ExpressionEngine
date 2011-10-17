<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * A/B Test Example
 *
 * @package		Ab_test
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		Mark J. Reeves
 * @link		http://markjreeves.com/ab-test-expressionengine
 */
class Ab_test_mcp 
{
	var $base;			// the base url for this module			
	var $form_base;		// base url for forms
	var $module_name;	
	
	var $module_instance;

	function Ab_test_mcp( $switch = TRUE )
	{		
		// normal EE stuff
		$this->EE =& get_instance();
		$this->module_name = strtolower(str_replace('_mcp', '', get_class($this)));
		$this->base	= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;	

							
		$this->EE->cp->set_breadcrumb($this->base, lang('A/B Test'));
		$this->EE->cp->set_variable('cp_page_title', "A/B Test Reports" );
	}
		
	function index() 
	{
		$vars['reset_link'] = BASE.AMP.'C=addons_modules'.AMP
			.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=reset_all_tests';
		
		$vars['results'] = 'yes, please';
		$this->EE =& get_instance();

		$tests = $this->EE->db->query(sprintf("SELECT test_name, SUM(hits) hits FROM exp_ab_test group by test_name order by test_name"));
		if ($tests->num_rows() > 0)
		{
			$i = 1;
			foreach($tests->result_array() as $test)
			{
			
				$vars['tests'][$i]['test_name'] = $test['test_name'];
				$vars['tests'][$i]['hits'] = $test['hits'];
				$vars['tests'][$i]['reset_link'] = BASE.AMP.'C=addons_modules'.AMP
					.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=reset_test'.AMP.'test_name='.$test['test_name'];
			
				$test_cases = $this->EE->db->query(sprintf("SELECT * FROM exp_ab_test WHERE test_name = '%s' order by case_name",$test['test_name']));
				foreach($test_cases->result_array() as $test_case)
				{
					$vars['tests'][$i]['test_cases'][$test_case['ab_test_case_id']]['test_name'] = $test_case['test_name'];
					$vars['tests'][$i]['test_cases'][$test_case['ab_test_case_id']]['case_name'] = $test_case['case_name'];
					$vars['tests'][$i]['test_cases'][$test_case['ab_test_case_id']]['hits'] = $test_case['hits'];
					
					$case_actions = $this->EE->db->query(sprintf("SELECT * FROM exp_ab_test_tracking WHERE ab_test_case_id = %s",$test_case['ab_test_case_id']));
				
					if ($case_actions->num_rows() > 0)
					{
						foreach($case_actions->result_array() as $case_action)
						{
							$vars['tests'][$i]['test_cases'][$test_case['ab_test_case_id']]['actions'][$case_action['ab_test_tracking_id']]['action'] = $case_action['href_action'];
							$vars['tests'][$i]['test_cases'][$test_case['ab_test_case_id']]['actions'][$case_action['ab_test_tracking_id']]['hits'] = $case_action['hits'];
						}
					}
					else
					{
						$vars['tests'][$i]['test_cases'][$test_case['ab_test_case_id']]['actions'][0]['action'] = '';
						$vars['tests'][$i]['test_cases'][$test_case['ab_test_case_id']]['actions'][0]['hits'] = '';
					}
				}
				
				$i++;
			
			}
		}
		else
		{
			$vars['results'] = 'none';
		}
		
		return $this->EE->load->view('reports', $vars, TRUE);
		}
		
		function reset_test()
		{
			$test_name = $this->EE->input->get_post('test_name');
			
			$data = array('hits' => 0);
			$where_clause = "test_name='".$test_name."'";
			$sql = $this->EE->db->update_string('exp_ab_test', $data, $where_clause);
			$this->EE->db->query($sql);
			
			$data = array('hits' => 0);
			$where_clause = "test_name='".$test_name."'";
			$sql = $this->EE->db->update_string('exp_ab_test_tracking', $data, $where_clause);
			$this->EE->db->query($sql);
			
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP
				.'M=show_module_cp'.AMP.'module=ab_test');	
			
		}
		
		function reset_all_tests()
		{
			$data = array('hits' => 0);
			$where_clause = '1=1';
			$sql = $this->EE->db->update_string('exp_ab_test', $data, $where_clause);
			$this->EE->db->query($sql);
			
			$data = array('hits' => 0);
			$where_clause = '1=1';
			$sql = $this->EE->db->update_string('exp_ab_test_tracking', $data, $where_clause);
			$this->EE->db->query($sql);
			
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP
				.'M=show_module_cp'.AMP.'module=ab_test');	
		}
	
}

/* End of file mcp.ab_test.php */ 
/* Location: ./system/expressionengine/third_party/ab_test/mcp.ab_test.php */ 