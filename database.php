<?php
namespace data;

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
        $this->db->exec(sprintf('INSERT INTO containers (description) VALUES ("%s")' , $description));
    }

    //-----------------------------------------------------------------------------------

    public function createNewEntry($cd_id , $key , $value , $description) {
        $this->db->exec(sprintf('INSERT INTO container_data (cd_id , key , value , description) 
                                 VALUES (%d , "%s" , "%s" , "%s")' , 
                                 $cd_id , $key , $value , $description));
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
        $this->db->exec(sprintf('UPDATE containers SET description="%s" WHERE id=%d' , $description , $id));
    }

    //-----------------------------------------------------------------------------------

    public function updateEntry($id , $key , $value , $description) {
        $this->db->exec(sprintf('UPDATE container_data SET  
            key="%s",
            value="%s",
            description="%s" 
            WHERE id=%d' ,  $key , $value , $description , $id));
    }

    //-----------------------------------------------------------------------------------

    public function getAllContainers() {
        $r1   = $this->db->query('SELECT * FROM containers');
        $all  = [];
        while($row = $r1->fetchArray()) {
            $each = ["ID" => $row["id"] , "Description" => $row["description"] , "Entries" => []];
            $r2 = $this->db->query(sprintf('SELECT * FROM container_data WHERE cd_id=%d' , $each["ID"]));
            while($row = $r2->fetchArray()) {
                $each["Entries"][] = ["ID"=>$row["id"],"Container ID"=>$row["cd_id"],"Key"=>$row["key"],"Value"=>$row["value"],"Description"=>$row["description"]];
            }
            $all[] = $each;
        }
        // print_r($all);
        return $all;
    }

    //-----------------------------------------------------------------------------------

    public function wipeData() {
        $this->db->exec('DELETE FROM containers');
    }

    //-----------------------------------------------------------------------------------

    public function containerExists($id) {
        return $this->db->query(sprintf('SELECT EXISTS (SELECT 1 FROM containers WHERE id=%d)' , $id))->fetchArray()[0];
    }
}

// print_r((new DataBase())->containerExists(12));
