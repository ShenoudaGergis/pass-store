<?php
require_once "./ui/ui.php";

class Application {
    private $gui = null;

    public function __construct() {
        $this->gui = new \ui\GUI();
    }

    //---------------------------------------------------------------------------------------------

    public function run() {
        $this->gui->createStartScreen();
    }

    
}

(new Application())->run();
