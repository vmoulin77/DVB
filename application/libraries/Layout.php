<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Layout
{
    private $CI;
    private $var = array();
    private $theme = 'default';
    
    public function __construct() {
        $this->CI =& get_instance();
        
        $this->var['title'] = 'DVB';
        $this->var['charset'] = $this->CI->config->item('charset');
        $this->var['menu'] = '';
        $this->var['output'] = '';
        $this->var['css'] = array();
        $this->var['js'] = array();
    }
    
    /******************************************************************************/
    
    public function set_title($title) {
        if(is_string($title) AND !empty($title)) {
            $this->var['title'] = $title;
            return true;
        }
        return false;
    }

    public function set_charset($charset) {
        if(is_string($charset) AND !empty($charset)) {
            $this->var['charset'] = $charset;
            return true;
        }
        return false;
    }

    public function set_theme($theme) {
        if(is_string($theme) AND !empty($theme) AND file_exists('./application/themes/' . $theme . '.php')) {
            $this->theme = $theme;
            return true;
        }
        return false;
    }

    /******************************************************************************/
    
    public function add_css($name) {
        if(is_string($name) AND !empty($name) AND file_exists('./assets/css/' . $name . '.css')) {
            $this->var['css'][] = base_url() . 'assets/css/' . $name . '.css';
            return true;
        }
        return false;
    }

    public function add_js($name) {
        if(is_string($name) AND !empty($name) AND file_exists('./assets/js/' . $name . '.js')) {
            $this->var['js'][] = base_url() . 'assets/js/' . $name . '.js';
            return true;
        }
        return false;
    }

    public function add_basic_assets() {
        $this->var['css'][] = base_url() . 'assets/third_party/jquery/css/jquery-ui.css';
        $this->var['css'][] = base_url() . 'assets/third_party/bootstrap/css/bootstrap.css';
        $this->var['css'][] = base_url() . 'assets/third_party/bootstrap/css/bootstrap-theme.css';
        $this->var['css'][] = base_url() . 'assets/css/app.css';
        
        $this->var['js'][] = base_url() . 'assets/third_party/jquery/js/jquery.js';
        $this->var['js'][] = base_url() . 'assets/third_party/jquery/js/jquery-ui.js';
        $this->var['js'][] = base_url() . 'assets/third_party/bootstrap/js/bootstrap.js';
        $this->var['js'][] = base_url() . 'assets/js/app.js';
        
        return $this;
    }

    /******************************************************************************/
    
    private function process_simple_view($name, $data, $auto_loaded_assets, $return = false) {
        if (in_array('css', $auto_loaded_assets)
            && (file_exists('./assets/css/' . $name . '.css'))
        ) {
            $this->var['css'][] = base_url() . 'assets/css/' . $name . '.css';
        }
        
        if (in_array('js', $auto_loaded_assets)
            && (file_exists('./assets/js/' . $name . '.js'))
        ) {
            $this->var['js'][] = base_url() . 'assets/js/' . $name . '.js';
        }
        
        if ($return) {
            return $this->CI->load->view($name, $data, true);
        } else {
            $this->var['output'] .= $this->CI->load->view($name, $data, true);
        }
    }
    
    public function menu($menu = 'default') {
        $this->var['menu'] .= $this->CI->load->view('menus/' . $menu, array(), true);
        return $this;
    }

    public function action_view($data = array()) {
        $controller = $this->CI->router->class;
        if (substr($controller, -11) === '_controller') {
            $controller = substr($controller, 0, -11);
        }
        
        $action = $this->CI->router->method;
        
        if (file_exists('./assets/css/controllers/' . $controller . '/controller.css')) {
            $this->var['css'][] = base_url() . 'assets/css/controllers/' . $controller . '/controller.css';
        }
        if (file_exists('./assets/css/controllers/' . $controller . '/actions/' . $action . '.css')) {
            $this->var['css'][] = base_url() . 'assets/css/controllers/' . $controller . '/actions/' . $action . '.css';
        }

        if (file_exists('./assets/js/controllers/' . $controller . '/controller.js')) {
            $this->var['js'][] = base_url() . 'assets/js/controllers/' . $controller . '/controller.js';
        }
        if (file_exists('./assets/js/controllers/' . $controller . '/actions/' . $action . '.js')) {
            $this->var['js'][] = base_url() . 'assets/js/controllers/' . $controller . '/actions/' . $action . '.js';
        }

        $this->view('controllers/' . $controller . '/actions/' . $action, $data);
    }
    
    public function views($name, $data = array(), $auto_loaded_assets = array(), $return = false) {
        if ($return) {
            return $this->process_simple_view($name, $data, $auto_loaded_assets, $return);
        } else {
            $this->process_simple_view($name, $data, $auto_loaded_assets, $return);
            return $this;
        }
    }
    
    public function view($name, $data = array(), $auto_loaded_assets = array()) {
        $this->process_simple_view($name, $data, $auto_loaded_assets);
        
        $this->var['css'] = array_unique($this->var['css']);
        $this->var['js'] = array_unique($this->var['js']);
        
        $this->CI->load->view('../themes/' . $this->theme, $this->var);
    }
}

/* End of file layout.php */
/* Location: ./application/libraries/layout.php */