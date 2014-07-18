<?php
/**
 * This file routes all urls that reach the system
 *
 * This is used for storing and matching the uris of the complete system
 * also allows to execute a method of a class according to the URi defined
 *
 *
 * LICENSE: Copyright (c), Enrique Ojeda <enriqueojedalara@gmail.com>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice, 
 * this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice, 
 * this list of conditions and the following disclaimer in the documentation 
 * and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its 
 * contributors may be used to endorse or promote products derived from this 
 * software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @license   http://www.debian.org/misc/bsd.license
 * @author    Enrique Ojeda Lara <enriqueojedalara@gmail.com>
 * @version   Latest version is available in https://github.com/enriqueojedalara
 * 
 */

/**
 * Database class (Need to be improved)
 *
 */
class Database{

    private static $db = null;
   
    public static function load($forceReload = false){
        
        if(self::$db instanceof Database) {
            return self::$db;
        }

        self::$db = new Database();
        self::$db->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        return self::$db;
    }


    private function connect($server, $username, $password, $dbname) {
        ini_set('mysql.connect_timeout', 120);
        ini_set('default_socket_timeout', 120);

        $this->db_link = @mysqli_connect($server, $username, $password, $dbname);
        if (mysqli_connect_error()){
            $msg = 'Could not connect to database [' . DB_NAME . '] ' . $this->get_error();
            throw new Error(Http::INTERNAL_SERVER_ERROR, $msg);
        }
    }


    public function close(){
        @mysqli_close($this->db_link);
        unset($this->db_link);
    }


    public function query($query){
        if(isset($this->db_result) && $this->db_result != null){
            mysqli_free_result($this->db_result);
        }
        $this->db_result = mysqli_query($this->db_link, $query);

        if (!$this->db_result){
            unset($this->db_result);
            $msg = sprintf("%d - %s, Query: %s", 
                    $this->error_no(), $this->get_error(), $query);
            throw new Error(Http::INTERNAL_SERVER_ERROR, $msg);
        }
    }

    public function num_rows(){
        return @mysqli_num_rows($this->db_result);
    }

    public function affected_rows(){
        if($this->db_result != null){
            return @mysqli_affected_rows($this->db_link);
        }
        return 0;
    }

    public function insert_id(){
        $res = @mysqli_query($this->db_link, 'SELECT LAST_INSERT_ID()');
        if(!$res) {
            $msg = 'Imposible getting the last insert id';
            throw new Error(Http::INTERNAL_SERVER_ERROR, $msg);
        }
        $res = @mysqli_fetch_row($res);
        return $res[0];
    }

    public function free_result(){
        @mysqli_free_result($this->db_result);
        $this->db_result = null;
    }


    public function fetch_array(){
        if($this->db_result != null){
            return @mysqli_fetch_array($this->db_result);
        }
    }


    public function fetch_row(){
        if($this->db_result != null){
            return @mysqli_fetch_row($this->db_result);
        }
    }


    public function fetch_assoc(){
        if($this->db_result != null){
            return @mysqli_fetch_assoc($this->db_result);
        }
    }


    public function error_no(){
        return mysqli_errno($this->db_link);
    }

    public function get_error(){
        return mysqli_error($this->db_link);
    }


    public function begin(){
        $this->db_link->autocommit(false);
        mysqli_query($this->db_link, "START TRANSACTION");
        return mysqli_query($this->db_link, "BEGIN");
    }


    public function commit(){
        return mysqli_query($this->db_link, "COMMIT");
    }


    public function rollback(){
        return mysql_query($this->db_link, "ROLLBACK");
    }

    public function __destruct(){
        $this->close();
    }

}