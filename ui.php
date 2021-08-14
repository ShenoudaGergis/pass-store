<?php

namespace ui;

require_once "./vendor/autoload.php";
require_once "./database.php";

class GUI {
    private $climate  = null;
    private $db       = null;

    public function __construct() {
        $this->climate = new \League\CLImate\CLImate;
        $this->climate->addArt("./art");
        $this->db = new \data\Database();
    }

    //---------------------------------------------------------------------------------------------

    public function createStartScreen() {
        $this->drawBanner();
        $this->db->setToken($this->promptInput("*) Please enter your hashkey :"));
        $this->createHomeScreen();
    }

    //---------------------------------------------------------------------------------------------

    public function createHomeScreen() {
        $this->drawBanner();
        $this->drawHeader("Please choose your action below");
        $this->showContent("1) Show all containers");
        $this->showContent("2) Erase all data");
        $this->showContent("3) Create new container");
        $this->showContent("4) Create new entry");
        $this->showContent("5) Remove container");

        $this->promptInputCallable("Please choose a number :" , function($response) {
            if(!in_array($response , [1,2,3,4,5])) return false;
            switch ($response) {
                case 1:
                    $this->createShowAllContainersScreen();
                    break;
                case 2:
                    $this->createWipeAllDataScreen();
                    break;
                case 3:
                    $this->createNewContainerScreen();
                    break;
                case 4:
                    $this->createNewEntryScreen();
                    break;   
                case 5:
                    $this->removeContainerScreen();
                    break;   
            }
            return true;
        });
    }

    //---------------------------------------------------------------------------------------------

    public function createShowAllContainersScreen() {
        $this->drawBanner();
        $this->drawHeader("Showing all containers");
        $allContainers = $this->db->getAllContainers();
        if(!$allContainers) {
            $this->hintMessage("There is no containers");
        }
        foreach ($allContainers as $row) {
            $this->innerTitle(sprintf("Container ID#%s" , $row["ID"]));
            $this->columnTitle("Description");
            $this->showContent($row["Description"]);
            $this->columnTitle("Entries");
            if($row["Entries"]) {
                $this->showTable($row["Entries"]);
            }
            else $this->hintMessage("The container has no entries");
        }
        $this->promptInput("To return back press enter key");
        $this->createHomeScreen();
    }    

    //---------------------------------------------------------------------------------------------

    public function createWipeAllDataScreen() {
        $this->drawBanner();
        $this->drawHeader("Erasing all data");
        if($this->confirm("Are you sure to erase all data")) {
            $this->db->wipeData();
            $this->climate->br();
            $this->succeedMessage("All data has been deleted");
        } else {
            $this->createHomeScreen();
        }
        $this->promptInput("Press enter to return homepage");
        $this->createHomeScreen();

    }

    //---------------------------------------------------------------------------------------------

    private function createNewEntrySubScreen($cid) {
        $this->drawBanner();
        $this->drawHeader("Creating new entry");
        $desc  = $this->promptInput("*) Entry description :");
        $key   = $this->promptInput("*) Entry key :");
        $value = $this->promptInput("*) Entry value :");
        $this->db->createNewEntry($cid , $key , $value , $desc);
        $this->climate->br();
        while($cont = $this->confirm("Add another entry ?")) {
            $this->climate->br();
            $this->createNewEntrySubScreen($cid);
        }
    }

    //---------------------------------------------------------------------------------------------

    public function createNewContainerScreen() {
        $this->drawBanner();
        $this->drawHeader("Creating new entry");
        $desc = $this->promptInput("*) Container description :");
        $this->db->createNewContainer($desc);
        $id   = $this->db->getLastCreatedID();
        $this->climate->br();
        if($this->confirm("Add container's entries now ?")) {
            $this->createNewEntrySubScreen($id);
        }
        $this->createHomeScreen();
    }

    //---------------------------------------------------------------------------------------------

    public function createNewEntryScreen() {
        $this->drawBanner();
        $this->drawHeader("Creating new entry");
        $id = $this->promptInput("*) Container ID :");
        if($this->db->containerExists($id)) {
            $this->createNewEntrySubScreen($id);
        } else {
            $this->climate->br();
            $this->errorMessage(sprintf("Container with ID %s isn't found" , $id));
            if($this->confirm("Try agian ?")) {
                $this->createNewEntryScreen();
            }
        }
        $this->createHomeScreen();
    }

    //---------------------------------------------------------------------------------------------

    public function removeContainerScreen() {
        $this->drawBanner();
        $this->drawHeader("Removing container");
        $id = $this->promptInput("*) Container ID : ");
        if($this->db->containerExists($id)) {
            $this->db->removeContainer($id);
            $this->climate->br();
            $this->succeedMessage("Container removed");
            while($cont = $this->confirm("Remove another container ?")) {
                $this->climate->br();
                $this->removeContainerScreen();
            }
        } else {
            $this->climate->br();
            $this->errorMessage(sprintf("Container with ID %s isn't found" , $id));
            if($this->confirm("Try agian ?")) {
                $this->removeContainerScreen();
            }
        }
        $this->createHomeScreen();
    }

    //---------------------------------------------------------------------------------------------

    private function drawBanner() {
        $this->climate->clear();
        $this->climate->lightCyan()->draw("passstore");
        $this->climate->br();
    }

    //---------------------------------------------------------------------------------------------

    private function drawHeader($title) {
        $this->climate->bold()->underline()->out($title);
        $this->climate->br();
    }

    //---------------------------------------------------------------------------------------------

    private function innerTitle($title) {
        $this->climate->bold()->out($title)->border("=" , strlen($title) + 5);
        $this->climate->br();
    }

    //---------------------------------------------------------------------------------------------

    private function showContent($content) {
        $this->climate->out($content);
        $this->climate->br();
    }

    //---------------------------------------------------------------------------------------------
    
    private function errorMessage($error) {
        $this->climate->backgroundRed()->white()->out($error);
        $this->climate->br();
    }

    //---------------------------------------------------------------------------------------------

    private function succeedMessage($msg) {
        $this->climate->backgroundGreen()->white()->out($msg);
        $this->climate->br();        
    }

    //---------------------------------------------------------------------------------------------

    private function hintMessage($msg) {
        $this->climate->lightBlue($msg);
        $this->climate->br();
    }

    //---------------------------------------------------------------------------------------------

    private function columnTitle($title) {
        $this->climate->lightYellow()->underline($title);
        $this->climate->br();
    }

    //---------------------------------------------------------------------------------------------
    
    private function showTable($data) {
        $this->climate->table($data);
        $this->climate->br();
    }

    //---------------------------------------------------------------------------------------------

    private function confirm($msg) {
        return $this->climate->yellow()->confirm($msg)->confirmed();
    }

    //---------------------------------------------------------------------------------------------

    private function promptInput($msg) {
        return $this->climate->bold()->input($msg)->prompt();
    }

    //---------------------------------------------------------------------------------------------

    private function promptInputCallable($msg , $callable) {
        $this->climate->bold()->input($msg)->accept($callable)->prompt();
    }
}

// (new GUI())->createStartScreen();