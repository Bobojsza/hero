<?php

class Admincp_Controller extends MY_Controller {
	var $navigation;

	function __construct () {
		parent::__construct();
		
		define("_CONTROLPANEL","TRUE");
		
		$this->load->library('notices');
		$this->load->helper('admincp/get_notices');
		
		// are they logged in?
		if ($this->user_model->logged_in() and !$this->user_model->is_admin()) {
			$this->notices->SetError('You are logged in but do not have control panel privileges.');
			redirect(site_url('admincp/login'));
			die();
		}
		elseif (!$this->user_model->logged_in() and $this->router->fetch_class() != 'login') {
			redirect(site_url('admincp/login'));
			die();
		}
	
		// store dynamically-generated navigation
		$this->load->library('navigation');
		
		$this->navigation->parent_link('dashboard','Dashboard');
		$this->navigation->parent_link('publish','Publish');
		$this->navigation->parent_link('storefront','Storefront');
		$this->navigation->parent_link('members','Members');
		$this->navigation->parent_link('reports','Reports');
		$this->navigation->parent_link('design','Design');
		$this->navigation->parent_link('configuration','Configuration');
		
		$this->navigation->child_link('dashboard',1,'Dashboard',site_url('admincp'));
		
		// admin-specific loading
		$this->load->helper('admincp/dataset_link');
		$this->load->helper('directory');
		$this->load->helper('form');
		$this->load->helper('admincp/admin_link');
	
		// load all modules with control panel to build navigation, etc.
		$directory = APPPATH . 'modules/';
		$modules = directory_map($directory);
		
		// load each module definition file, for admincp navigation
		foreach ($modules as $module => $module_folder) {
			MY_Loader::define_module($module . '/');
		}
		
		// define WYSIWYG session variables for file uploading
		session_start();
		$_SESSION['KCFINDER'] = array();
		$_SESSION['KCFINDER']['disabled'] = FALSE;
		
		// Safari base_href fix
		$url = parse_url(base_url());
		$this->load->library('user_agent');
		// if they are using Safari and don't have Caribou installed in a sub-folder, this prefix "/" fixes the problem
		if (stripos($this->agent->browser(),'safari') !== FALSE and trim($url['path'], '/') == '') {
			$prefix = '/';
		}
		else {
			$prefix = '';
		}
		$_SESSION['KCFINDER']['uploadURL'] = $prefix . str_replace(FCPATH,'',setting('path_editor_uploads'));
		$_SESSION['KCFINDER']['uploadDir'] = rtrim(setting('path_editor_uploads'),'/');
	}
}