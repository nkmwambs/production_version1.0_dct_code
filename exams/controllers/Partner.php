<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*	
 *	@author 	: Nicodemus Karisa
 *	date		: 27 April, 2017
 */

class Partner extends CI_Controller
{
    
    
	function __construct()
	{
		parent::__construct();
		$this->load->database();
        $this->load->library('session');
		$this->load->model('file');		
		$this->load->library('zip');
		//$this->load->library('Utility_forms');
		
		
       /*cache control*/
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
		
    }
	
	/***default functin, redirects to login page if no admin logged in yet***/
   public function index()
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url() . 'exams.php/login', 'refresh');
		
    }

    function dashboard()
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url().'exams.php','refresh');
			
        $page_data['page_name']  = 'dashboard';
        $page_data['page_title'] = get_phrase('exams_dashboard');
        $this->load->view('backend/index', $page_data);
    }
	
	function delete_kcse_result($result_id,$epoch_start_of_year_date){
		 if ($this->session->userdata('admin_login') != 1)
            redirect(base_url().'exams.php','refresh');
		
			$year = date("Y");
			
			if($epoch_start_of_year_date !== "") {
				$year = date('Y',$epoch_start_of_year_date);
			}else{
				$epoch_start_of_year_date = strtotime('first day of january this year');
			}
			
		$this->db->where(array('rID'=>$result_id));
		$this->db->delete('kcse_spread');	
		
		$this->db->where(array('rID'=>$result_id));
		$this->db->delete('kcse');
		
		if($this->db->affected_rows() > 0){
			
			$this->session->set_flashdata('flash_message','Delete Success');
		}
			
		$results = $this->db->get_where('kcse',array('pNo'=>$this->session->center_id,
		'acYr'=>$year))->result_object();			
        
		$page_data['grades_key'] = array('12'=>'A','11'=>'A-','10'=>'B+','9'=>'B','8'=>'B-','7'=>'C+',
		'6'=>'C','5'=>'C-','4'=>'D+','3'=>'D','2'=>'D-','1'=>'E');
		$page_data['start_of_year_date'] = $epoch_start_of_year_date;
		$page_data['records'] = $results;
        $page_data['page_name']  = 'kcse';
        $page_data['page_title'] = get_phrase('KCSE_results');
        $this->load->view('backend/index', $page_data);	
	}
    
   function kcse($epoch_start_of_year_date = "")
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url().'exams.php','refresh');
		
		$year = date("Y");
		
		if($epoch_start_of_year_date !== "") {
			$year = date('Y',$epoch_start_of_year_date);
		}else{
			$epoch_start_of_year_date = strtotime('first day of january this year');
		}
		
		$results = $this->db->get_where('kcse',array('pNo'=>$this->session->center_id,
		'acYr'=>$year))->result_object();			
        
		$page_data['grades_key'] = array('12'=>'A','11'=>'A-','10'=>'B+','9'=>'B','8'=>'B-','7'=>'C+',
		'6'=>'C','5'=>'C-','4'=>'D+','3'=>'D','2'=>'D-','1'=>'E');
		$page_data['start_of_year_date'] = $epoch_start_of_year_date;
		$page_data['records'] = $results;
        $page_data['page_name']  = __FUNCTION__;
        $page_data['page_title'] = get_phrase('KCSE_results');
        $this->load->view('backend/index', $page_data);	
    }
	
	function edit_kcse_results($result_id="",$view_only = false){
		if ($this->session->userdata('admin_login') != 1)
            redirect(base_url().'exams.php','refresh');
		
		//Get Exam result
		$this->db->join('kcse_spread','kcse_spread.rID=kcse.rID');
		$get_result_rows = $this->db->get_where('kcse',array('kcse.rID'=>$result_id))->num_rows();
		
		if($get_result_rows == 0){
			$get_result = $this->db->get_where('kcse',array('kcse.rID'=>$result_id))->result_array();	
		}else{
			$this->db->join('kcse_spread','kcse_spread.rID=kcse.rID');
			$get_result = $this->db->get_where('kcse',array('kcse.rID'=>$result_id))->result_array();
		}
	
		$data = $get_result[0];
		
		$forms = new Utility_forms();
			
		$fields[] = array(
		  'label'=>get_phrase('cluster'),
		  'element'=>'input',
		  'properties' => array('name'=>'cstName','id'=>'cstName',
		  'value'=>$this->session->cluster,'readonly'=>'readonly')
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('project_id'),
		  'element'=>'input',
		  'properties' => array('name'=>'pNo','id'=>'pNo',
		  'value'=>$this->session->center_id,'readonly'=>'readonly')
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('beneficiary_number'),
		  'element'=>'input',
		  'properties' => array('name'=>'childNo','id'=>'childNo','value'=>$data['childNo'],
		  'readonly'=>'readonly')
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('beneficiary_name'),
		  'element'=>'input',
		  'properties' => array('name'=>'childName','id'=>'childName','readonly'=>'readonly',
		  'value'=>$data['childName'])
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('gender'),
		  'element'=>'input',
		  'properties' => array('name'=>'sex','id'=>'sex','readonly'=>'readonly',
		  'value'=>$data['sex'])
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('date_of_birth'),
		  'element'=>'input',
		  'properties' => array('name'=>'dob','id'=>'dob','readonly'=>'readonly',
		  'value'=>$data['dob'])
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('index_number'),
		  'element'=>'input',
		  'properties' => array('name'=>'indx','id'=>'indx','value'=>$data['indx'])
		 );
		 
		 $years = range(2012,2030);
		 
		 $year_option = array();
		 
		 foreach($years as $year){
		 	$selected = $data['acYr']== $year?"selected":"";
		 	$year_option[$year] = array('option'=>$year,'properties'=>array($selected));
		 }
		 
		 $fields[] = array(
		  	'label'=>get_phrase('examination_year'),
		  	'element'=>'select',
		  	'properties' => array('name'=>'acYr','id'=>'acYr'),
		 	'options' => $year_option
		 );
		 
		$grade_array = array(12=>'A',11=>'A-',10=>'B+',9=>'B',8=>'B-',7=>'C+',
		6=>'C',5=>'C-',4=>'D+',3=>'D',2=>'D-',1=>'E');
		
		$grade_option = array();
		
		foreach($grade_array as $scale=>$grade){
		 	$selected = $data['mean_grade'] == $scale?"selected":"";
		 	$grade_option[$scale] = array('option'=>$grade,'properties'=>array($selected));
		 }
		
		 $fields[] = array(
		  	'label'=>get_phrase('mean_grade'),
		  	'element'=>'select',
		  	'properties' => array('name'=>'mean_grade','id'=>'mean_grade'),
		 	'options' => $grade_option
		 );
		 
		 
		$forms->set_form_fields($fields);
		$forms->set_use_form_tag(false);
		
		$page_data['build_bio_form'] = $forms->create_single_column_form();
		//End of Bio Form
		
		$subjects = $this->db->get_where('kcse_subject',array('status'=>'1'))->result_object();
		
		$subject_array = array();
		
		foreach($subjects as $subject){
			$subject_array[$subject->kcse_subject_id] = array('option'=>$subject->name);
		}
		
		$fields_spread[] = array(
		  	'label'=>get_phrase('subject'),
		  	'element'=>'select',
		  	'properties' => array('name'=>'subject[]','id'=>''),
		  	'options' => $subject_array,
			'values' => array_column($get_result, 'kcse_subject_id')
		 );
		 
		$fields_spread[] = array(
		  	'label'=>get_phrase('grade'),
		  	'element'=>'select',
		  	'properties' => array('name'=>'grade[]','id'=>''),
		 	'options' => $grade_option,
		 	'values' => array_column($get_result, 'grade')
		 );
		
 
		$forms->set_initial_row_count(count($get_result));
		$forms->set_form_fields($fields_spread);
		$forms->set_form_id('frm_edit_kcse_result');
		
		$page_data['build_spread_form'] = $forms->create_multi_column_form();
		//End of Spread Form
		
		
		$page_data['result_id'] = $result_id;
		$page_data['page_name']  = __FUNCTION__;
        $page_data['page_title'] = get_phrase(__FUNCTION__);
		if($view_only){
			$page_data['page_name']  = "view_kcse_results";
        	$page_data['page_title'] = get_phrase('view__kcse_results');
		}
        $this->load->view('backend/index', $page_data);	
	}

	function post_edit_kcse_result($result_id = ""){
		$msg = "Edit failed";
		
		$post = $this->input->post();

		$header['indx'] 		= $post['indx'];
		$header['acYr'] 		= $post['acYr'];
		$header['mean_grade'] 	= $post['mean_grade'];
		
		//if($check_existing_result == 0){
			$this->db->where(array('rID'=>$result_id));
			$this->db->update('kcse',$header);
			
			$this->db->where(array('rID'=>$result_id));
			$this->db->delete('kcse_spread');
			
			for($i=0;$i<sizeof($post['subject']);$i++){
					
				$spread['rID'] = $result_id;
				$spread['kcse_subject_id'] = $post['subject'][$i];
				$spread['grade'] = $post['grade'][$i];
				
				$this->db->insert('kcse_spread',$spread);
			}
			
			$msg = "Edit successful";
		//}
		
		
		echo $msg;
	}
	
	
	/**This is an admin function**/
	function add_subject(){
		if ($this->session->userdata('admin_login') != 1)
            redirect(base_url().'exams.php','refresh');
			
		$form = new Utility_forms();
		
		 $fields[] = array(
		  	'label'=>get_phrase('subject'),
		  	'element'=>'input',
		  	'properties' => array('name'=>'subject[]','id'=>'mean_grade'),
		 );
		
		$form->set_form_fields($fields);
		$form->set_form_action(base_url().'exams.php/partner/create_subject');
		$form->set_form_id('frm_create_subject');
		
		$page_data['build_form'] = $form->create_multi_column_form();			
        $page_data['subjects'] = $this->db->get('kcse_subject')->result_object();
        $page_data['page_name']  = __FUNCTION__;
        $page_data['page_title'] = get_phrase(__FUNCTION__);
        $this->load->view('backend/index', $page_data);	
	}
	
	function create_subject(){

		$subjects = $this->input->post('subject');
		
		$cnt = 0;
		
		foreach($subjects as $subject){
			//Check if subject exists. Insert if not
			$subject_count = $this->db->get_where('kcse_subject',array('name'=>$subject))->num_rows();
			
			if($subject_count == 0){
				//$data['name'] = $subject;
				$this->db->insert('kcse_subject',array('name'=>$subject));
				$cnt++;
			}
		}

		echo $cnt.' '.get_phrase('record(s)_created');
	}
	
	function add_kcse_result(){
		if ($this->session->userdata('admin_login') != 1)
            redirect(base_url().'exams.php','refresh');
		
				
		$forms = new Utility_forms();
		
		$fields[] = array(
		  'label'=>get_phrase('cluster'),
		  'element'=>'input',
		  'properties' => array('name'=>'cstName','id'=>'cstName',
		  'value'=>$this->session->cluster,'readonly'=>'readonly')
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('project_id'),
		  'element'=>'input',
		  'properties' => array('name'=>'pNo','id'=>'pNo',
		  'value'=>$this->session->center_id,'readonly'=>'readonly')
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('beneficiary_number'),
		  'element'=>'input',
		  'properties' => array('name'=>'childNo','id'=>'childNo','required'=>'required')
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('beneficiary_name'),
		  'element'=>'input',
		  'properties' => array('name'=>'childName','id'=>'childName','readonly'=>'readonly','required'=>'required')
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('gender'),
		  'element'=>'input',
		  'properties' => array('name'=>'sex','id'=>'sex','readonly'=>'readonly')
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('date_of_birth'),
		  'element'=>'input',
		  'properties' => array('name'=>'dob','id'=>'dob','readonly'=>'readonly')
		 );
		 
		 $fields[] = array(
		  'label'=>get_phrase('index_number'),
		  'element'=>'input',
		  'properties' => array('name'=>'indx','id'=>'indx')
		 );
		 
		 $years = range(2012,2030);
		 
		 $year_option = array();
		 
		 foreach($years as $year){
		 	$selected = date("Y")==$year?"selected":"";
		 	$year_option[$year] = array('option'=>$year,'properties'=>array($selected));
		 }
		 
		 $fields[] = array(
		  	'label'=>get_phrase('examination_year'),
		  	'element'=>'select',
		  	'properties' => array('name'=>'acYr','id'=>'acYr'),
		 	'options' => $year_option
		 );
		
		 $fields[] = array(
		  	'label'=>get_phrase('mean_grade'),
		  	'element'=>'select',
		  	'properties' => array('name'=>'mean_grade','id'=>'mean_grade'),
		 	'options' => array(
		 						'12'=>array('option'=>'A'),
		 						'11'=>array('option'=>'A-'),
		 						'10'=>array('option'=>'B+'),
		 						'9'=>array('option'=>'B'),	
		 						'8'=>array('option'=>'B-'),
		 						'7'=>array('option'=>'C+'),
		 						'6'=>array('option'=>'C'),
		 						'5'=>array('option'=>'C-'),
		 						'4'=>array('option'=>'D+'),
		 						'3'=>array('option'=>'D'),
		 						'2'=>array('option'=>'D-'),
		 						'1'=>array('option'=>'E')
							)
		 );
		 
		 
		$forms->set_form_fields($fields);
		$forms->set_use_form_tag(false);
		
		$page_data['build_bio_form'] = $forms->create_single_column_form();
		//End of Bio Form
		
		$subjects = $this->db->get_where('kcse_subject',array('status'=>'1'))->result_object();
		
		$subject_array = array();
		
		foreach($subjects as $subject){
			$subject_array[$subject->kcse_subject_id] = array('option'=>$subject->name);
		}
		
		$fields_spread[] = array(
		  	'label'=>get_phrase('subject'),
		  	'element'=>'select',
		  	'properties' => array('name'=>'subject[]','id'=>''),
		 	'options' => $subject_array
		 );
		 
		$fields_spread[] = array(
		  	'label'=>get_phrase('grade'),
		  	'element'=>'select',
		  	'properties' => array('name'=>'grade[]','id'=>''),
		 	'options' => array(
		 						'12'=>array('option'=>'A'),
		 						'11'=>array('option'=>'A-'),
		 						'10'=>array('option'=>'B+'),
		 						'9'=>array('option'=>'B'),	
		 						'8'=>array('option'=>'B-'),
		 						'7'=>array('option'=>'C+'),
		 						'6'=>array('option'=>'C'),
		 						'5'=>array('option'=>'C-'),
		 						'4'=>array('option'=>'D+'),
		 						'3'=>array('option'=>'D'),
		 						'2'=>array('option'=>'D-'),
		 						'1'=>array('option'=>'E')
							)
		 );
		 
		
		$forms->set_form_fields($fields_spread);
		
		$page_data['build_spread_form'] = $forms->create_multi_column_form();
		//End of Spread Form
		
			
        $page_data['page_name']  = __FUNCTION__;
        $page_data['page_title'] = get_phrase(__FUNCTION__);
        $this->load->view('backend/index', $page_data);			
	}

	function create_kcse_result(){
		
		$msg = "Submit failed. May be the record exists";
		
		$post = $this->input->post();
		
		$header['cstName'] 		= $post['cstName'];
		$header['pNo'] 			= $post['pNo'];
		$header['childNo'] 		= $post['childNo'];
		$header['childName'] 	= $post['childName'];
		$header['dob'] 			= $post['dob'];
		$header['sex'] 			= $post['sex'];
		$header['indx'] 		= $post['indx'];
		$header['acYr'] 		= $post['acYr'];
		$header['mean_grade'] 	= $post['mean_grade'];
		
		//Check if results of the beneficary and year exists
		$check_existing_result = $this->db->get_where('kcse',array('childNo'=>$post['childNo'],
		'acYr'=>$post['acYr']))->num_rows();
		
		if($check_existing_result == 0){
			$this->db->insert('kcse',$header);
			$kcse_rid = $this->db->insert_id();
			
			for($i=0;$i<sizeof($post['subject']);$i++){
					
				$spread['rID'] = $kcse_rid;
				$spread['kcse_subject_id'] = $post['subject'][$i];
				$spread['grade'] = $post['grade'][$i];
				
				$this->db->insert('kcse_spread',$spread);
			}
			
			$msg = "Submit successful";
		}
		
		
		echo $msg;
	}
    // function kcpe()
    // {
        // if ($this->session->userdata('admin_login') != 1)
            // redirect(base_url().'exams.php','refresh');
// 			
        // $crud = new grocery_CRUD();
		// $crud->set_theme('flexigrid');
		// $crud->set_table('kcpe');
// 		
		// $acYr = date('Y');
// 		
		// if(isset($_POST['acYr'])){
			// $acYr = $this->input->post('acYr');
		// }
		// $crud->where(array('pNo'=>$this->session->center_id,'acYr'=>$acYr));
// 		
		// $crud->display_as('cstName','Cluster')
			// ->display_as('pNo','Project')
			// ->display_as('childNo','Beneficiary Number')
			// ->display_as('childName','Beneficiary Name')
			// ->display_as('sex','Gender')
			// ->display_as('indx','Index Number')
			// ->display_as('eng','English')
			// ->display_as('kis','Kiswahili')
			// ->display_as('sci','Science')
			// ->display_as('mat','Maths')
			// ->display_as('totMrk','Total Marks')
			// ->display_as('acYr','Academic Year')
			// ->display_as('stmp','Time Stamp')
			// ->display_as('dob','Date Of Birth')
			// ->display_as('sstd','Social Studies');	
// 		
		// $crud->required_fields('cstName','pNo','childNo','childName','dob','sex','indx','eng','kis','sci','mat','sstd','totMrk','acYr');	
// 		
		// $crud->callback_field('cstName',array($this,'field_callback_cluster'));
		// $crud->callback_field('pNo',array($this,'field_callback_pNo'));
// 		
		// $years = array_combine(range('2012', '2030'), range('2012', '2030'));
// 		
		// $crud->field_type('acYr', 'dropdown',$years);
		// $crud->field_type('dob', 'numeric');
// 		
		// $crud->callback_before_insert(array($this,'insert_result_callback'));
// 		
		// $crud->unset_fields(array('stmp'));
		// //$crud->unset_edit();	
		// //$crud->unset_delete();			
// 		
// 		
		// $output = $crud->render();			
        // $page_data['page_name']  = __FUNCTION__;
        // $page_data['page_title'] = get_phrase('KCPE_results');
		// $output = array_merge($page_data,(array)$output);
// 
        // $this->load->view('backend/index', $output);		
    // }	
	
	function kcpe(){
		$build_list = new Utility_forms2();
		
		$selected_columns = array("cstName as Cluster","pNo as 'FCP ID'",
		"childNo as 'Beneficiary ID'","childName as 'Beneficairy Name'","dob as 'Date Of Birth'",'sex as Gender',
		"indx as 'Index Number'","eng as English","kis as Kiswahili","sci as Science","mat as Mathematics",
		"sstd as 'Social Studies/RE'","totMrk as 'Grade'","acYr as 'Examination Year'");
		
		
		$build_list->set_selected_fields($selected_columns);
		
		//$build_list->set_where_clause(array('cname'=>'Ishiara','department'=>0));
				
		$build_list->set_panel_title("Examination Results");
		
		$action = array(
			'add' 	=> array('href'=>'exams.php/Partner/add_kcpe_result'),
			'view' 	=> array('href'=>'exams.php/Partner/view_kcpe_result'),
			'edit' 	=> array('href'=>'exams.php/Partner/edit_kcpe_result'),
			'delete'=> array('href'=>'exams.php/Partner/delete_kcpe_result'),
		);
		
		$build_list->set_list_action($action);
		
		$build_list->set_db_table("kcpe");
		
		$build_list->set_add_form();
		
		$page_data['page_name'] = 'kcpe';
		$page_data['page_title'] = 'K.C.P.E Results';
		$page_data['output'] = $build_list->render_item_list();
		$this->load->view('backend/index', $page_data);		
	}

	function add_kcpe_result(){
		$build_form = new Utility_forms2();
		
		$fields[] = array(
			'element'=>'input',
			'label'=>'Cluster',
			'properties'=>array('id'=>'','name'=>'cstName[]','readonly'=>'readonly','value'=>$this->session->cluster)
		);
		
		$fields[] = array(
			'element'=>'input',
			'label'=>'FCP ID',
			'properties'=>array('id'=>'','name'=>'pNo[]','readonly'=>'readonly','value'=>$this->session->center_id)
		);
		
		$fields[] = array(
			'label'=>"Beneficiary ID",
			'element'=>'input',
			'properties'=>array('id'=>'','name'=>'childNo[]')
		);
		
		$fields[] = array(
			'label'=>"Beneficiary Name",
			'element'=>'input',
			'properties'=>array('id'=>'','name'=>'childName[]','readonly'=>'readonly')
		);
		
		$fields[] = array(
			'label'=>"Date Of Birth",
			'element'=>'input',
			'properties'=>array('id'=>'','name'=>'dob[]','readonly'=>'readonly')
		);
		
		$fields[] = array(
			'label'=>"Gender",
			'element'=>'input',
			'properties'=>array('id'=>'','name'=>'sex[]','readonly'=>'readonly')
		);
		
		$fields[] = array(
			'label'=>"Index Number",
			'element'=>'input',
			'properties'=>array('id'=>'','name'=>'indx[]')
		);
		
		
		// $fields[] = array(
			// 'label'=>"English",
			// 'element'=>'input',
			// 'properties'=>array('id'=>'','name'=>'eng[]')
		// );
// 		
// 		
		// $fields[] = array(
			// 'label'=>"Kiswahili",
			// 'element'=>'input',
			// 'properties'=>array('id'=>'','name'=>'kis[]')
		// );
// 		
// 		
		// $fields[] = array(
			// 'label'=>"Science",
			// 'element'=>'input',
			// 'properties'=>array('id'=>'','name'=>'sci[]')
		// );
// 		
// 		
		// $fields[] = array(
			// 'label'=>"Maths",
			// 'element'=>'input',
			// 'properties'=>array('id'=>'','name'=>'Mat[]')
		// );
// 		
// 		
		// $fields[] = array(
			// 'label'=>"Social Studies/ RE",
			// 'element'=>'input',
			// 'properties'=>array('id'=>'','name'=>'sstd[]')
		// );		
		
		$fields[] = array(
			'label'=>"Total",
			'element'=>'input',
			'properties'=>array('id'=>'','name'=>'totMrk[]')
		);	
		
		$fields[] = array(
			'label'=>"Examination Year",
			'element'=>'input',
			'properties'=>array('id'=>'','name'=>'acYr[]')
		);	
		
		
		$build_form->set_form_fields($fields);

		$build_form->set_form_action(base_url().'index.php/welcome/post');

		$build_form->set_go_back_on_post(false);
		
		$page_data['page_name'] = "kcpe";
		$page_data['page_title'] = "Add new result";
		$page_data['output'] = $build_form->render_form('single_form');
		$this->load->view('backend/index',$page_data);
	}
	
	function insert_result_callback($post_array){
		
		$yr = range('2012', '2030');
		
		$post_array['acYr'] =  $yr[$post_array['acYr']];
		
		return $post_array;
		
	}

	function field_callback_cluster(){
		return "<INPUT type='text' maxlength='20' class='form-control' readonly='readonly' id='field-cstName' name='cstName' value='".$this->session->cluster."'>";
		
	}

	function field_callback_pNo(){
		return "<INPUT type='text' maxlength='20' class='form-control' readonly='readonly' id='field-pNo' name='pNo' value='".$this->session->center_id."'>";
		
	}
	
	function get_childNo($benNo){
		
		if($this->db->get_where('childdetails',array('childNo'=>$benNo))->num_rows() > 0){
			echo json_encode($this->db->get_where('childdetails',array('childNo'=>$benNo))->row());
		}else{

			$obj['childName'] = "Missing Details";
			$obj['sex'] = "Missing Details";
			$obj['dob'] = "Missing Details";
			
			echo json_encode($obj);
		}
				
		
	}
	
	function cleanData(&$str)
	  {
	    $str = preg_replace("/\t/", "\\t", $str);
	    $str = preg_replace("/\r?\n/", "\\n", $str);
	    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
	  }
	
	function download_kcse_results($epoch_date){
		
		//All Active subjects
		$subjects = $this->db->get_where('kcse_subject',array('status'=>1))->result_object();
		
		$grade_array = array('12'=>'A','11'=>'A-','10'=>'B+','9'=>'B','8'=>'B-','7'=>'C+',
		'6'=>'C','5'=>'C-','4'=>'D+','3'=>'D','2'=>'D-','1'=>'E');
		
		$data = array();
		
		$this->db->join('kcse_spread','kcse_spread.rID=kcse.rID','LEFT');	  
		$results = $this->db->get_where('kcse',array('acYr'=>date("Y",$epoch_date),
		'pNo'=>$this->session->center_id));
		
		
		// if($results->num_rows() == 0){
			// $results = $this->db->get_where('kcse',array('acYr'=>date("Y",$epoch_date),
			// 'pNo'=>$this->session->center_id));
		// }				
		
		foreach($results->result_object() as $row){
						
			$data[$row->childNo]['Cluster'] = $row->cstName;
			$data[$row->childNo]['Project'] = $row->pNo;
			$data[$row->childNo]['Beneficiary ID'] = $row->childNo;
			$data[$row->childNo]['Beneficiary Name'] = $row->childName;
			$data[$row->childNo]['Gender'] = $row->sex;
			$data[$row->childNo]['Birthdate'] = $row->dob;
			$data[$row->childNo]['Mean Grade'] = $grade_array[$row->mean_grade];
					
			foreach($subjects as $subject){
				$data[$row->childNo][$subject->name] = 0;
			}

		}
		
		
		foreach($results->result_object() as $row){
			foreach($subjects as $subject){
				
					if($row->kcse_subject_id == $subject->kcse_subject_id){
						$data[$row->childNo][$subject->name] = $grade_array[$row->grade];
					}

				
			}
		}

		  
		$this->export_to_excel($data);
		  
	}

	private function export_to_excel($data){
		   // file name for download
		  $filename = "compassion_toolkit_data_" . date('Ymd') . ".xls";
		
		  header("Content-Disposition: attachment; filename=\"$filename\"");
		  header("Content-Type: application/vnd.ms-excel");
		
		  $flag = false;
		  foreach($data as $row) {
		    if(!$flag) {
		      // display field/column names as first row
		      echo implode("\t", array_keys($row)) . "\n";
		      $flag = true;
		    }
		    array_walk($row,  array($this, 'cleanData'));
		    echo implode("\t", array_values($row)) . "\n";
		  }
		
		  exit;
	}
		
}