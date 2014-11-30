<?php
/**
 * Created by IntelliJ IDEA.
 * User: Steve
 * Date: 10/9/2014
 * Time: 1:58 PM
 */

class Moove {
    public $pdo;

    function __construct($data_source_name) {
        /** Connect to SQLite database **/
        $this->pdo = new PDO($data_source_name);
    }

    function getUserIdByApiKey($api_key) {
        /** Prepare and execute SQL statement **/
        $sth = $this->pdo->prepare("SELECT id FROM users where apikey = ? limit 1");
        $sth->execute(array($api_key));

        /** Get first result **/
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        /** Return user id or throw exception **/
        if(isset($res["id"])) return $res["id"];
        else throw new Exception('No User Found');
    }

    function isUserExist($email_address) {
        $sth = $this->pdo->prepare("select password,id from users where email = ?");
        $sth->execute(array($email_address));
        $res = $sth->fetchAll();

        if (count($res) > 0) return true;
        else return false;
    }

    function authenticateByPassword($email, $password) {
        $sth = $this->pdo->prepare("select apikey from users where email = ? and password = ?");
        $sth->execute(array($email, $password));
        return $this->authenticate($sth);
    }

    function authenticateByApiKey($email, $api_key) {
        $sth = $this->pdo->prepare("select apikey from users where email = ? and apikey = ?");
        $sth->execute(array($email, $api_key));
        return $this->authenticate($sth);
    }

    private function authenticate($statement_handler) {
        $res = $statement_handler->fetch();
        if(isset($res["apikey"])) return "0," . $res["apikey"] . ",,1";
        else throw new Exception('bad auth');
    }

    function getUserHistory($user_id, $limit) {
        $history = "";

        /** Prepare and execute SQL statement **/
        $sth = $this->pdo->prepare("SELECT id,name,key,ext,time,hits FROM files where owner = ? order by time desc" . ($limit > 0 ? " limit " . $limit : ""));
        $sth->execute(array($user_id));

        /** Print blank entry */
        $history .= "0\n";

        /** Print the rest of the entries **/
        foreach($sth->fetchAll() as $row) {
            $history .= $row["id"] . ",";
            $history .= date("Y-m-d H:i:s", $row["time"]) . ",";
            $history .= ROOT_URL . $row["key"] . "/" . base_convert($row["id"],10,36) . "." . $row["ext"] . ",";
            $history .= $row["name"] . ",";
            $history .= $row["hits"] . ",";
            $history .= "0\n";
        }

        return $history;
    }

    function getFileById($file_id, $key) {
        $sth = $this->pdo->prepare("SELECT name,ext,size,owner,time,hits FROM files WHERE id = ? AND key = ?");
        $sth->execute(array($file_id, $key));
        if($sth->rowCount() == 0) return false;
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    function countHit($file_id) {
        $sth = $this->pdo->prepare("UPDATE files SET hits = hits + 1 WHERE id = ?");
        $sth->execute(array($file_id));
    }

    function upgradeDatabase() {
        $sth = $this->pdo->prepare("alter table files add column hits integer default ?");
        $sth->execute(array(0));
        $this->setDatabaseVersion(2);
    }

    function getDatabaseVersion() {
        return $this->pdo->query("pragma user_version")->fetchColumn();
    }

    function setDatabaseVersion($version) {
        $sth = $this->pdo->prepare("pragma user_version = ?");
        $sth->execute(array($version));
    }

    function __destruct() {
        $this->pdo = null;
    }

}
