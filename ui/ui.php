<?php

namespace ui;

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../data/database.php";
require_once __DIR__ . "/../lemmatization/lemmatizer.php";

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
        $this->showContent("1 ) Show all containers");
        $this->showContent("2 ) Erase all data");
        $this->showContent("3 ) Create new container");
        $this->showContent("4 ) Create new entry");
        $this->showContent("5 ) Remove container");
        $this->showContent("6 ) Remove entry");
        $this->showContent("7 ) Update Container");
        $this->showContent("8 ) Update Entry");
        $this->showContent("9 ) Search");
        $this->showContent("10) Backup/Restore");


        $this->climate->br();
        $this->promptInputCallable("Please choose a number :" , function($response) {
            if(!in_array($response , [1,2,3,4,5,6,7,8,9,10])) return false;
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
                case 6:
                    $this->removeEntryScreen();
                    break;   
                case 7:
                    $this->updateContainerScreen();
                    break; 
                case 8:
                    $this->updateEntryScreen();
                    break; 
                case 9:
                    $this->searchScreen();
                    break; 
                case 10:
                    $this->backupScreen();
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
            $this->climate->br();
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
        $id = Utils::cleanText($this->promptInput("*) Container ID :"));
        if($id && $this->db->containerExists($id)) {
            $this->createNewEntrySubScreen($id);
        } else {
            if($id) {
                $this->climate->br();
                $this->errorMessage(sprintf("Container with ID %s isn't found" , $id));
                if($this->confirm("Try agian ?")) {
                    $this->createNewEntryScreen();
                }
            }
        }
        $this->createHomeScreen();
    }

    //---------------------------------------------------------------------------------------------

    public function removeContainerScreen() {
        $this->drawBanner();
        $this->drawHeader("Removing container");
        $id = Utils::cleanText($this->promptInput("*) Container ID : "));
        if($id && $this->db->containerExists($id)) {
            $this->db->removeContainer($id);
            $this->climate->br();
            $this->succeedMessage("Container removed");
            while($cont = $this->confirm("Remove another container ?")) {
                $this->climate->br();
                $this->removeContainerScreen();
            }
        } else {
            $this->climate->br();
            if($id) {
                $this->errorMessage(sprintf("Container with ID %s isn't found" , $id));
                if($this->confirm("Try agian ?")) {
                    $this->removeContainerScreen();
                }
            }
        }
        $this->createHomeScreen();
    }

    //---------------------------------------------------------------------------------------------

    public function removeEntryScreen() {
        $this->drawBanner();
        $this->drawHeader("Removing entry");
        $id = Utils::cleanText($this->promptInput("*) Entry ID : "));
        if($id && $this->db->entryExists($id)) {
            $this->db->removeEntry($id);
            $this->climate->br();
            $this->succeedMessage("Entry removed");
            while($cont = $this->confirm("Remove another Entry ?")) {
                $this->climate->br();
                $this->removeEntryScreen();
            }
        } else {
            $this->climate->br();
            if($id) {
                $this->errorMessage(sprintf("Entry with ID %s isn't found" , $id));
                if($this->confirm("Try agian ?")) {
                    $this->removeEntryScreen();
                }
            }
        }
        $this->createHomeScreen();

    }

    //---------------------------------------------------------------------------------------------

    private function updateEntrySubScreen($entry) {
        $this->contentDescTitle("*) Entry old key : " , $entry["Key"]);
        $key = $this->promptInput("*) Entry new key : ");
        $this->climate->br(); 
        $this->contentDescTitle("*) Entry old value : " , $entry["Value"]);
        $value = $this->promptInput("*) Entry new value : "); 
        $this->climate->br(); 
        $this->contentDescTitle("*) Entry old description : " , $entry["Description"]);
        $desc = $this->promptInput("*) Entry new description : "); 

        return ["ID" => $entry["ID"] , 
                "Key" => Utils::textReplace($entry["Key"] , $key) , 
                "Value" => Utils::textReplace($entry["Value"] , $value) , 
                "Description" => Utils::textReplace($entry["Description"] , $desc)
                ];
    }

    //---------------------------------------------------------------------------------------------

    public function updateContainerScreen() {
        $this->drawBanner();
        $this->drawHeader("Updating container");
        $id = Utils::cleanText($this->promptInput("*) Container ID : "));
        if($id && $this->db->containerExists($id)) {
            $this->climate->br();
            $this->contentDescTitle("*) Container old Description : " , $this->db->getContainer($id)["Description"]);
            $desc = $this->promptInput("*) Container new Description : "); 
            $this->db->updateContainer($id , Utils::textReplace($this->db->getContainer($id)["Description"] , $desc));
            $this->climate->br();
            if($this->confirm("Update container entries ")) {
                foreach($this->db->getContainerEntries(intval($id)) as $entry) {
                    $this->drawBanner();
                    $this->drawHeader(sprintf("Updating container entry#%d" , $entry["ID"]));
                    $r = $this->updateEntrySubScreen($entry);
                    $this->db->updateEntry($r["ID"] , $r["Key"] , $r["Value"] , $r["Description"]);
                    $this->climate->br();
                    if(!$this->confirm("Update next entry ?")) break;
                }
            }
        } else {
            if($id) {
                $this->climate->br();
                $this->errorMessage(sprintf("Container with ID %s isn't found" , $id));
                if($this->confirm("Try agian ?")) {
                    $this->updateContainerScreen();
                }
            }
        }
        $this->createHomeScreen();

    }

    //---------------------------------------------------------------------------------------------

    public function updateEntryScreen() {
        $this->drawBanner();
        $this->drawHeader("Updating entry");
        $id = Utils::cleanText($this->promptInput("*) Entry ID : "));
        if($id && $this->db->entryExists($id)) {
            $entry = $this->db->getEntry($id);
            $r = $this->updateEntrySubScreen($entry);
            $this->db->updateEntry($r["ID"] , $r["Key"] , $r["Value"] , $r["Description"]);
            $this->climate->br();
        } else {
            if($id) {
                $this->climate->br();
                $this->errorMessage(sprintf("Entry with ID %s isn't found" , $id));
                if($this->confirm("Try agian ?")) {
                    $this->updateEntryScreen();
                }
            }
        }
        $this->createHomeScreen();
    }

    //---------------------------------------------------------------------------------------------

    public function searchScreen() {
        $this->drawBanner();
        $this->drawHeader("Searching");
        $keywords = Utils::cleanText($this->promptInput("*) Enter your search keywords : "));
        if($keywords) {
            $stored     = $this->db->getAllContainersText();
            $keywords   = explode(" " , $keywords);
            $sorted     = []; 
            foreach($stored as $cont) {
                $score = \lemmatization\Lemmatizer::getIntersection($keywords , $cont["Text"]);
                if($score !== 0) $sorted[$cont["ID"]] = $score; 
            } 
            arsort($sorted);
            $this->climate->br();
            $this->innerTitle("*) Listing of matched results ");
            if(count($sorted) === 0) {
                $this->hintMessage("No matched result");
                if($this->confirm("Try agian ?")) {
                    $this->searchScreen();
                } else {
                    $this->createHomeScreen();
                }    
            } else {
                foreach($sorted as $id => $count) {
                    $container = $this->db->getContainer($id);
                    $this->innerTitle(sprintf("Container ID#%s" , $container["ID"]));
                    $this->columnTitle("Description");
                    $this->showContent($container["Description"]);
                    $this->climate->br();
                    $this->showTable($this->db->getContainerEntries($container["ID"]));
                }    
            }
            if($this->confirm("Search again ?")) {
                $this->searchScreen();
            } else {
                $this->createHomeScreen();
            }
        } else {
            $this->createHomeScreen();
        }
    }

    //---------------------------------------------------------------------------------------------

    public function backupScreen() {
        $this->drawBanner();
        $this->drawHeader("Backup/Restore");
        $this->innerTitle("*) Please choose an action ");
        $this->showContent("1) Create new backup");
        $this->showContent("2) Restore backup");

        $this->climate->br();
        $this->promptInputCallable("*) Enter action number : " , function($response) {
            if(!$response && $response != 0) return true;
            if(!in_array($response , [1 , 2])) return false;
            switch($response) {
                case 1 :
                    if($this->db->createBackup()) {
                        $this->climate->br();
                        $this->succeedMessage("Backup created successfully");
                    } else {
                        $this->climate->br();
                        $this->errorMessage("Error occurred while creating backup");
                    }
                    break;
                
                case 2 :
                    $backups = $this->db->getBackups();
                    $this->climate->br();
                    $this->innerTitle("*) Please choose one from the following backups ");
                    foreach($backups as $key => $item) {
                        ++$key;
                        $this->showContent(date("$key ) Y:m:d H:i:s\t|" , explode("." , $item)[0]));
                        $this->climate->border("-" , 25);
                    }
                    $this->climate->br();
                    $this->promptInputCallable("*) Your choice : " , function($choice) use($backups) {
                        if(!$choice && $choice != 0) {$this->climate->br();return true;}
                        if(!($choice > 0 && $choice <= count($backups))) return false;
                        --$choice;
                        if($this->db->restoreBackup($backups[$choice])) {
                            $this->climate->br();
                            $this->succeedMessage("Backup restored successfully");
                        } else {
                            $this->climate->br();
                            $this->errorMessage("Error occurred while restoring backup");
                        }
                        return true;
                    });
                    break;
            }
            if($this->confirm("Backup/Restore again ?")) {
                $this->backupScreen();
            } else {
                $this->createHomeScreen();
            }
            return true;
        });
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
    }

    //---------------------------------------------------------------------------------------------

    private function contentDescTitle($title , $content) {
        $this->climate->bold()->inline($title);
        $this->showContent($content);
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

class Utils {
    public static function textReplace($old , $new) {
        $new = Utils::cleanText($new);
        return ($new) ? $new : $old;  
    }

    //---------------------------------------------------------------------------------------------

    public static function cleanText($text) {
        return trim($text);
    }
}

// (new GUI())->createStartScreen();