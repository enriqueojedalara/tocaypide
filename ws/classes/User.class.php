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

if(!isset($_SESSION)) {session_start();}

/**
 * User class
 *
 * This is only an example
 *
 */
class User{

    function __construct(){
        Utils::load_database();
    }
    
    public function login($params){
        if (!isset($params['email'])){
            $msg = 'Email is missing';
            throw new Error(Http::NOT_FOUND, $msg);
        }
        $sql = 'SELECT `sEmail`, `sPasswords`
                FROM `user`
                WHERE 
                `sEmail` = %s';
        $sql = Utils::mysql_prepare($sql, $params['email']);
        $rs = $GLOBALS[DB_USER]->query($sql);
        
        if ($GLOBALS[DB_USER]->num_rows() <= 0){
            $msg = 'Usuario no encontrado';
            throw new Error(Http::NOT_FOUND, $msg);
        }

        $rs = $GLOBALS[DB_USER]->fetch_assoc();
        if ($rs['sPassword'] !== sha1($params['password'])){
            $msg = 'El password es incorrecto';
            throw new Error(Http::NOT_FOUND, $msg);
        }

        unset($rs['sPassword']);
        return  $rs;
    }
}
