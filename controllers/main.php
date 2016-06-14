<?php

// Dane z modelow i przekazujemy do widokow

use TheSeer\fXSL\fXSLCallback;
use TheSeer\fXSL\fXSLTProcessor;

class controller 
{
    // Allowable module values
    protected $moduleList = ['main', 'introduction', 'account', 'register', 'search'];

    // Init xsl class
    protected $xsl;

    // Init users class
    protected $users;

    // Init trains class
    protected $trains;

    // Init common class
    protected $common;

    public function __construct() {
        $this->xsl = new xsl();
        $this->users = new users();
        $this->trains = new trains();
        $this->common = new common();
        
        $this->loadTemplate();
    }

    static public function loadClass($name) {
        if (file_exists('models/' . $name . '.class.php')) {
            require 'models/' . $name . '.class.php';
        }
    }

    private function loadTemplate() {
        $content = $this->loadView();
        require CONFIG_TEMPLATE_DIR;
    }

    private function loadView() {
        $module = $this->getModule();
        if (!empty($module)) {
            if (!in_array($module, $this->moduleList)) {
                 $module = '';
            }
        } else {
            $module = CONFIG_APP_MAIN_MODULE;
        }
        return $this->xsl->loadModuleFile($module);
    }

    private function getModule() {
        return filter_input(INPUT_GET, CONFIG_APP_MODULE_VAR_NAME, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}