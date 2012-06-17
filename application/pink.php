<?php
	if(!defined('ON_PAGE')) die('Direct script access is not allowed.');
	
	class PinkController {
		protected $request;
		
		public function defaultAction($request) {
			$this->request = $request;
			
			$this->renderView();
		}
		
		public function renderView($view = 'default') {
			$classname = ucwords($view) . 'View';
			
			$view_obj = new $classname;
			
			$view_obj->render($this->request);
		}
	}
	
	class PinkView {
		protected $request;
		
		public function render($request) {
			header('Content-Type: text/html; charset=utf-8');
			
			$this->request = $request;
			
			$this->header();

			$this->content();
			
			$this->footer();
		}
		
		public function header() {
?>
HEADER
<?php
		}
		
		public function footer() {
?>
FOOTER
<?php
		}
		
		public function content() {
?>
CONTENT
<?php
		}
	}
	
	class Pink {
		public static function Autoload($name) {
			if(substr($name, -4, 4) == 'View') {
				require_once(VIEWS_PATH . strtolower(substr($name, 0, -4)) . '.php');
			}
			else if(substr($name, -5, 5) == 'Model') {
				require_once(MODELS_PATH . strtolower(substr($name, 0, -5)) . '.php');
			}
			else if(substr($name, -10, 10) == 'Controller') {
				require_once(CONTROLLERS_PATH . strtolower(substr($name, 0, -10)) . '.php');
			}
			else if(substr($name, 0, 4) == 'Pink') { // lol namespacing
				require_once(MODULES_PATH . strtolower(substr($name, 4)) . '.php');
			}
		}
		
		public static function Bootstrap() {
			// Set up autoload
			spl_autoload_register('Pink::Autoload');
			
			// Set timezone
			date_default_timezone_set(PinkConfig::TIMEZONE);
			
			// Set locale
			setlocale(LC_ALL, 'en_US.utf8');
			
			// Set request values
			$request = '';
			
			if(array_key_exists(PinkConfig::QUERY_PARAM, $_REQUEST)) {
				$request = $_REQUEST[PinkConfig::QUERY_PARAM];
			}
			else {
				$request = '/';
			}
			$request_parts = explode($request, '/');
			
			if($request_parts[0] == '') {
				$request_parts[0] = 'default';
			}
			
			if($request_parts[1] == '') {
				$request_parts[1] = 'default';
			}
			
			// Filter request values
			$request_parts[0] = preg_replace('/[^a-zA-Z0-9]+/', '', $request_parts[0]);
			$request_parts[1] = preg_replace('/[^a-zA-Z0-9]+/', '', $request_parts[1]);
			
			// Run controller
			$controller_classname = ucwords($request_parts[0]) . 'Controller';
			
			$controller_method = $request_parts[1] . 'Action';
			
			$controller = new $controller_classname;
			
			$controller->{$controller_method}($_REQUEST);
		}
	}