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
        $this->db->setToken($this->promptInput("*) Please enter your encryption key :"));
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
        $this->showContent("11) Exit");

        $this->climate->br();
        $this->promptInputCallable("Please choose a number :" , function($response) {
            if(!Utils::checkRange(range(1 , 11) , $response)) return false;
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
                case 11:
                    $this->exitScreen();
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
            $this->promptInput("Press enter to return homepage");
            $this->createHomeScreen();
        } else {
            $this->createHomeScreen();
        }
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
        if($this->confirm("Add another entry ?")) {
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
            $this->createHomeScreen();
        } else {
            $this->createHomeScreen();
        }
    }

    //---------------------------------------------------------------------------------------------

    public function createNewEntryScreen() {
        $this->drawBanner();
        $this->drawHeader("Creating new entry");
        if(Utils::isTextGiven($out = $this->promptInput("*) Container ID :"))) {
            $id = intval($out);
            if(is_numeric($out) && $this->db->containerExists($id)) {
                $this->createNewEntrySubScreen($id);
                $this->createHomeScreen();
            } else {
                $this->climate->br();
                $this->errorMessage(sprintf("Container with ID %s isn't found" , $out));
                if($this->confirm("Try agian ?")) {
                    $this->createNewEntryScreen();
                } else {
                    $this->createHomeScreen();
                }
            }
        } else {
            $this->createHomeScreen();
        }
    }

    //---------------------------------------------------------------------------------------------

    public function removeContainerScreen() {
        $this->drawBanner();
        $this->drawHeader("Removing container");
        if(Utils::isTextGiven($out = $this->promptInput("*) Container ID : "))) {
            $id = intval($out);
            if(is_numeric($out) && $this->db->containerExists($id)) {
                $this->db->removeContainer($id);
                $this->climate->br();
                $this->succeedMessage("Container removed");
                if($this->confirm("Remove another container ?")) {
                    $this->climate->br();
                    $this->removeContainerScreen();
                } else {
                    $this->createHomeScreen();
                }
            } else {
                $this->climate->br();
                $this->errorMessage(sprintf("Container with ID %s isn't found" , $out));
                if($this->confirm("Try agian ?")) {
                    $this->removeContainerScreen();
                } else {
                    $this->createHomeScreen();
                } 
            }
        } else {
            $this->createHomeScreen();
        }
    }

    //---------------------------------------------------------------------------------------------

    public function removeEntryScreen() {
        $this->drawBanner();
        $this->drawHeader("Removing entry");
        if(Utils::isTextGiven($out = $this->promptInput("*) Entry ID : "))) {
            $id = intval($out);
            if(is_numeric($out) && $this->db->entryExists($id)) {
                $this->db->removeEntry($id);
                $this->climate->br();
                $this->succeedMessage("Entry removed");
                if($this->confirm("Remove another entry ?")) {
                    $this->climate->br();
                    $this->removeEntryScreen();
                } else {
                    $this->createHomeScreen();
                }
            } else {
                $this->climate->br();
                $this->errorMessage(sprintf("Entry with ID %s isn't found" , $out));
                if($this->confirm("Try agian ?")) {
                    $this->removeEntryScreen();
                } else {
                    $this->createHomeScreen();
                }
            }
        } else {
            $this->createHomeScreen();
        }       
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
        if(Utils::isTextGiven($out = $this->promptInput("*) Container ID : "))) {
            $id = intval($out);
            if(is_numeric($out) && $this->db->containerExists($id)) {
                $this->climate->br();
                $this->contentDescTitle("*) Container old Description : " , $this->db->getContainer($id)["Description"]);
                $desc = $this->promptInput("*) Container new Description : "); 
                $this->db->updateContainer($id , Utils::textReplace($this->db->getContainer($id)["Description"] , $desc));
                $this->climate->br();
                if($this->confirm("Update container entries ")) {
                    foreach($this->db->getContainerEntries($id) as $entry) {
                        $this->drawBanner();
                        $this->drawHeader(sprintf("Updating container entry#%d" , $entry["ID"]));
                        $r = $this->updateEntrySubScreen($entry);
                        $this->db->updateEntry($r["ID"] , $r["Key"] , $r["Value"] , $r["Description"]);
                        $this->climate->br();
                        if(!$this->confirm("Update next entry ?")) break;
                    }
                } else {
                    $this->createHomeScreen();
                }
            } else {
                $this->climate->br();
                $this->errorMessage(sprintf("Container with ID %s isn't found" , $out));
                if($this->confirm("Try agian ?")) {
                    $this->updateContainerScreen();
                } else {
                    $this->createHomeScreen();
                }
            }
        } else {
            $this->createHomeScreen();
        }
    }

    //---------------------------------------------------------------------------------------------

    public function updateEntryScreen() {
        $this->drawBanner();
        $this->drawHeader("Updating entry");
        if(Utils::isTextGiven($out = $this->promptInput("*) Entry ID : "))) {
            $id = intval($out);
            if(is_numeric($out) && $this->db->entryExists($id)) {
                $entry = $this->db->getEntry($id);
                $r = $this->updateEntrySubScreen($entry);
                $this->db->updateEntry($r["ID"] , $r["Key"] , $r["Value"] , $r["Description"]);
                $this->climate->br();
                if($this->confirm("Update another entry ? ")) {
                    $this->updateEntryScreen();
                } else {
                    $this->createHomeScreen();
                }
            } else {
                $this->climate->br();
                $this->errorMessage(sprintf("Entry with ID %s isn't found" , $out));
                if($this->confirm("Try agian ?")) {
                    $this->updateEntryScreen();
                } else {
                    $this->createHomeScreen();
                }
            }
        } else {
            $this->createHomeScreen();
        }
    }

    //-----------------------------------------------------------------------------------

    public function searchScreen() {
        $this->drawBanner();
        $this->drawHeader("Searching");
        if(Utils::isTextGiven($keywords = $this->promptInput("*) Enter your search keywords : "))) {
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
            } else {
                foreach($sorted as $id => $count) {
                    $container = $this->db->getContainer($id);
                    $this->innerTitle(sprintf("Container ID#%s" , $container["ID"]));
                    $this->columnTitle("Description");
                    $this->showContent($container["Description"]);
                    $this->climate->br();
                    $entries = $this->db->getContainerEntries($container["ID"]);
                    if($entries) $this->showTable($entries);
                    else {
                        $this->hintMessage("The contaner has no entries");
                    }
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
        $this->showContent("3) Erase backups");

        $this->climate->br();
        $this->promptInputCallable("*) Enter action number : " , function($response) {
            if(!Utils::isTextGiven($response)) return true;
            if(!Utils::checkRange(range(1,3) , $response)) return false;
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
                    if(count($backups) == 0) {
                        $this->hintMessage("There is no backups");
                        break;
                    }
                    $this->hintMessage("Note: if you restore a backup the current data will be lost");
                    $this->innerTitle("*) Please choose one from the following backups ");
                    foreach($backups as $key => $item) {
                        ++$key;
                        $this->showContent(date("$key ) Y:m:d H:i:s\t|" , explode("." , $item)[0]));
                        $this->climate->border("-" , 25);
                    }
                    $this->climate->br();
                    $this->promptInputCallable("*) Your choice : " , function($choice) use($backups) {
                        if(!Utils::isTextGiven($choice)) {$this->climate->br();return true;}
                        if(!Utils::checkRange(range(1,count($backups)-1) , $choice)) return false;
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

                case 3 :
                    if($this->db->eraseBackups()) {
                        $this->climate->br();
                        $this->succeedMessage("Backups erased successfully");
                    } else {
                        $this->climate->br();
                        $this->errorMessage("Error occurred while erasing backups");
                    }
                    break;
            }
            return true;
        });
        if($this->confirm("Backup/Restore again ?")) {
            $this->backupScreen();
        } else {
            $this->createHomeScreen();
        }
    }

    //---------------------------------------------------------------------------------------------

    public function exitScreen() {
        $this->climate->clear();
        $this->climate->animation("thankyou")->speed(700)->scroll("left");
        $this->climate->clear();
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
        return (Utils::isTextGiven($new)) ? $new : $old;
    }

    //-----------------------------------------------------------------------------------

    public static function cleanText($text) {
        return trim($text);
    }

    //-----------------------------------------------------------------------------------

    public static function isTextGiven($text) {
        return strlen(Utils::cleanText($text)) !== 0 ? true : false; 
    }

    //-----------------------------------------------------------------------------------

    public static function checkRange($arr , $given) {
        if(!(Utils::isTextGiven($given) && is_numeric($given))) return false;
        $given = Utils::cleanText($given);
        return in_array($given , $arr);
    }
}


// (new GUI())->createStartScreen();