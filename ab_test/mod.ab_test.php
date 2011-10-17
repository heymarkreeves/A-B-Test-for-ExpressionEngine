<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 *
 * @package		Ab_test
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		Mark J. Reeves
 * @link		http://markjreeves.com/ab-test-expressionengine
 */
class Ab_test {	
	
	var $return_data = '';
	
	function Ab_test()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		
		if ($this->EE->input->post('test') && $this->EE->input->post('test'))
		{
			$href_action = $this->EE->input->post('href');
			$test_name = $this->EE->input->post('test');
			$case_name = $this->EE->input->post('case');
			// Process an AJAX submit or click
			// Check the ab_test table to see if this test/case already exists or needs to be added
			$test_case_id = $this->EE->db->query(sprintf("SELECT ab_test_case_id FROM exp_ab_test WHERE test_name='%s' AND case_name='%s'",$test_name,$case_name));
			$results = $this->EE->db->query(sprintf("SELECT * FROM exp_ab_test_tracking WHERE href_action='%s' AND test_name='%s' AND case_name ='%s'",$href_action,$test_name,$case_name));
			
			// If it does not exist, add it
			if ($results->num_rows() == 0)
			{
				$data = array('ab_test_case_id' => $test_case_id->row('ab_test_case_id'), 'href_action' => $href_action, 'test_name' => $test_name, 'case_name' => $case_name);
				$sql = $this->EE->db->insert_string('exp_ab_test_tracking', $data);
				$this->EE->db->query($sql);
				//$entry_id = $this->EE->db->insert_id;
			}
			else
			{
				// If it does exist, update the display count
				$data = array('hits' => $results->row('hits')+1);
				$where_clause = sprintf("ab_test_case_id=%s AND href_action='%s' AND test_name='%s' AND case_name='%s'", $test_case_id->row('ab_test_case_id'), $href_action, $test_name, $case_name);
				$sql = $this->EE->db->update_string('exp_ab_test_tracking', $data, $where_clause);
				$this->EE->db->query($sql);
			}
			
		}
		else
		{
			// Display the A/B
			$tagdata = $this->EE->TMPL->tagdata;
			preg_match_all('/'.LD.'(case)(:(\w+))?(\s+.*)?'.RD.'/sU', $tagdata, $matches);
			
			// Grabbing all {case} matches, so we need the count to generate a random index to pull from the array with
			$case_count = count($matches[0]);
			
			$case_to_display = rand(1,$case_count);
			$index = $case_to_display-1;
			
			// Start: Borrowed from Brandon Kelly's FieldFrame 1.4
			$field_name = $matches[1][$index];
			
			$start_tag = $matches[0][$index];
			$tag_len = strlen($matches[0][$index]);
			// Modified this a bit to look within all the tag data
			$tagdata_pos = strpos($tagdata, $start_tag) + $tag_len;
			$endtag = LD.'/'.$field_name.(isset($matches[2][$index]) ? $matches[2][$index] : '').RD;
			$endtag_len = strlen($endtag);
			$endtag_pos = strpos($tagdata, $endtag, $tagdata_pos);
			
			$field_tagdata = ($endtag_pos !== FALSE)
				? substr($tagdata, $tagdata_pos, $endtag_pos - $tagdata_pos)
				: '';
			// End: Borrowed from Brandon Kelly's FieldFrame 1.4
			
			$case_params = $matches[4][$index];
			$start_name = 'name="';
			$name_len = strlen($start_name);
			$end_name = '"';
			$name_pos = strpos($case_params, $start_name) + $name_len;
			$end_name_len = 1;
			$end_name_pos = strpos($case_params, $end_name, $name_pos);
			
			// Pull the case name from the matched tag
			$case_name = ($end_name_pos !== FALSE)
				? substr($case_params, $name_pos, $end_name_pos - $name_pos)
				: '';
			// Grab the test name from the ab_test tag params
			$test_name = $this->EE->TMPL->fetch_param('name');
			
			// Check the ab_test table to see if this test/case already exists or needs to be added
			$results = $this->EE->db->query(sprintf("SELECT * FROM exp_ab_test WHERE test_name='%s' AND case_name ='%s'",$test_name,$case_name));
			
			// If it does not exist, add it
			if ($results->num_rows() == 0)
			{
				$data = array('test_name' => $test_name, 'case_name' => $case_name);
				$sql = $this->EE->db->insert_string('exp_ab_test', $data);
				$this->EE->db->query($sql);
				//$entry_id = $this->EE->db->insert_id;
			}
			else
			{
				// If it does exist, update the display count
				$data = array('hits' => $results->row('hits')+1);
				$where_clause = sprintf("test_name='%s' AND case_name='%s'", $test_name, $case_name);
				$sql = $this->EE->db->update_string('exp_ab_test', $data, $where_clause);
				$this->EE->db->query($sql);
			}
						
			$field_tagdata = '<div id="ab_test" name="'.$test_name.'" case="'.$case_name.'">' . $field_tagdata;
			$field_tagdata .= '</div>';
			
			// Modify the Tagdata output using jQuery. Add attributes to links and forms.

			if ($this->EE->uri->uri_string() == '')
			{
			$post_uri = '/';
			}
			else
			{
			$post_uri = $this->EE->uri->uri_string();
			}
			
			$field_tagdata .= "<script type=\"text/javascript\">
					$(document).ready(function(){
						$('div#ab_test').find('a').click(function(e){
							postClick($(this).attr('href'),$('div#ab_test').attr('name'),$('div#ab_test').attr('case'));
						});
						$('div#ab_test').find('form').submit(function(e){
							postClick($(this).attr('action'),$('div#ab_test').attr('name'),$('div#ab_test').attr('case'));
						});
					})
					
					function postClick(href, test_name, case_name){
						$.ajax({
							type: \"POST\",
							url: \"".$post_uri."\",
							data: 'href='+href+'&test='+test_name+'&case='+case_name,
							dataType: 'json',
							async: false,
							success: function(data, textStatus){
								
					        },
					        error: function(req, textStatus, errorThrown) {

					        }
					    });
					}
				</script>";
			
			// This outputs whatever's in the case randomly chosen
			$this->return_data = $field_tagdata;
								
			/*
			array(5) {
			  [0]=>
			  array(2) {
			    [0]=>
			    string(15) "{case name="a"}"
			    [1]=>
			    string(15) "{case name="b"}"
			  }
			  [1]=>
			  array(2) {
			    [0]=>
			    string(4) "case"
			    [1]=>
			    string(4) "case"
			  }
			  [2]=>
			  array(2) {
			    [0]=>
			    string(0) ""
			    [1]=>
			    string(0) ""
			  }
			  [3]=>
			  array(2) {
			    [0]=>
			    string(0) ""
			    [1]=>
			    string(0) ""
			  }
			  [4]=>
			  array(2) {
			    [0]=>
			    string(25) " name="a" other_param="y""
			    [1]=>
			    string(9) " name="b""
			  }
			}
			*/
			//var_dump($matches);
		}
	}
	
}

/* End of file mod.ab_test.php */ 
/* Location: ./system/expressionengine/third_party/ab_test/mod.ab_test.php */ 