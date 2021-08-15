<?php
namespace data;

use function \encryption\enc;
use function \encryption\dec;


require_once "./encryption.php";

//-------------------------------------------------------------------------------------------------

class Database {
    private $dirpath = null;
    private $dbpath  = null;
    private $db      = null;
    private $token   = null;

    //-----------------------------------------------------------------------------------

    public function setToken($token) {
        $this->token = $token;
    }

    //-----------------------------------------------------------------------------------
    
    public function __construct() {
        $this->dirpath = $this->getHomeDir() . "/.passstore/";
        $this->dbpath  = $this->dirpath . "/data.db";
        $this->checkInstall();
        $this->db = new \SQLite3($this->dbpath);
        $this->createIniTables();
    }

    //-----------------------------------------------------------------------------------

    public function getHomeDir() {
        $home = getenv('HOME');
        if (!empty($home)) {
            $home = rtrim($home, '/');
        }
        elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
            $home = rtrim($home, '\\/');
        }
        return empty($home) ? NULL : $home;
    }

    //-----------------------------------------------------------------------------------
    
    public function checkInstall() {
        @mkdir($this->dirpath);
        if(!is_file($this->dbpath)) {
            fclose(fopen($this->dbpath , "w"));
        }
    }

    //-----------------------------------------------------------------------------------

    public function getLastCreatedID() {
        return $this->db->lastInsertRowID();
    }

    //-----------------------------------------------------------------------------------

    public function createIniTables() {
        $this->db->exec("
            PRAGMA foreign_keys = ON;
            CREATE TABLE IF NOT EXISTS containers (
                id INTEGER PRIMARY KEY,
                description TEXT
            );
            CREATE TABLE IF NOT EXISTS container_data (
                id INTEGER PRIMARY KEY,
                cd_id INTEGER,
                key TEXT,
                value TEXT,
                description TEXT,
                FOREIGN KEY(cd_id) REFERENCES containers(id) ON DELETE CASCADE
            )
        ");
    }

    //-----------------------------------------------------------------------------------

    public function createNewContainer($description) {
        $this->db->exec(sprintf('INSERT INTO containers (description) VALUES ("%s")' , enc($description , $this->token)));
    }

    //-----------------------------------------------------------------------------------

    public function createNewEntry($cd_id , $key , $value , $description) {
        $this->db->exec(sprintf('INSERT INTO container_data (cd_id , key , value , description) 
                                 VALUES (%d , "%s" , "%s" , "%s")' , 
                                 $cd_id , enc($key , $this->token) , enc($value , $this->token) , enc($description , $this->token)));
    }    

    //-----------------------------------------------------------------------------------

    public function removeContainer($id) {
        $this->db->exec(sprintf('DELETE FROM containers WHERE id=%d' , $id));
    }

    //-----------------------------------------------------------------------------------

    public function removeEntry($id) {
        $this->db->exec(sprintf('DELETE FROM container_data WHERE id=%d' , $id));
    }

    //-----------------------------------------------------------------------------------

    public function updateContainer($id , $description) {
        $this->db->exec(sprintf('UPDATE containers SET description="%s" WHERE id=%d' , enc($description , $this->token) , $id));
    }

    //-----------------------------------------------------------------------------------

    public function updateEntry($id , $key , $value , $description) {
        $this->db->exec(sprintf('UPDATE container_data SET  
            key="%s",
            value="%s",
            description="%s" 
            WHERE id=%d' ,  enc($key , $this->token) , enc($value , $this->token) , enc($description , $this->token) , $id));
    }

    //-----------------------------------------------------------------------------------

    public function getAllContainers() {
        $r1   = $this->db->query('SELECT * FROM containers');
        $all  = [];
        while($row = $r1->fetchArray()) {
            $each = ["ID" => $row["id"] , "Description" => dec($row["description"] , $this->token) , "Entries" => []];
            $r2 = $this->db->query(sprintf('SELECT * FROM container_data WHERE cd_id=%d' , $each["ID"]));
            while($row = $r2->fetchArray()) {
                $each["Entries"][] = ["ID"=>$row["id"],"Container ID"=>$row["cd_id"],"Key"=>dec($row["key"] , $this->token),"Value"=>dec($row["value"] , $this->token),"Description"=>dec($row["description"] , $this->token)];
            }
            $all[] = $each;
        }
        return $all;
    }

    //-----------------------------------------------------------------------------------

    public function getContainer($id) {
        $r = $this->db->querySingle(sprintf('SELECT * FROM containers WHERE id=%d' , $id) , true);
        $r["description"] = dec($r["description"] , $this->token);
        return $r;
    }

    //-----------------------------------------------------------------------------------

    public function getEntry($id) {
        $r = $this->db->querySingle(sprintf('SELECT * FROM container_data WHERE id=%d' , $id) , true);
        $r2 = [];
        $r2["ID"]  = $r["id"];
        $r2["Container ID"] = $r["cd_id"];
        $r2["Key"] = dec($r["key"] , $this->token);
        $r2["Value"] = dec($r["value"] , $this->token);
        $r2["Description"] = dec($r["description"] , $this->token);
        return $r2;
    }

    //-----------------------------------------------------------------------------------

    public function wipeData() {
        $this->db->exec('DELETE FROM containers');
    }

    //-----------------------------------------------------------------------------------

    public function containerExists($id) {
        return $this->db->query(sprintf('SELECT EXISTS (SELECT 1 FROM containers WHERE id=%d)' , $id))->fetchArray()[0];
    }

    //-----------------------------------------------------------------------------------
    
    public function entryExists($id) {
        return $this->db->query(sprintf('SELECT EXISTS (SELECT 1 FROM container_data WHERE id=%d)' , $id))->fetchArray()[0];
    }

    //-----------------------------------------------------------------------------------

    public function getContainerEntries($id) {
        $allContainers = $this->getAllContainers();
        foreach($allContainers as $key => $container) {
            if($container["ID"] === $id) return $container["Entries"];
        }
        return [];
    }

}

// print_r((new DataBase())->getEntry(2));
