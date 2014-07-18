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
 * Utils class (Many helpful methods)
 *
 */
class Utils{
    
    const SALT = 'T0TalL3d$_';
    const ACCESS_TOKEN_TIMELIFE = 9600;


    public static function date(){
        return date('Y-m-d H:i:s');
    }

    public static function load_database($mode = 'RO'){
        if(!isset($GLOBALS[DB_USER]) || !$GLOBALS[DB_USER] || !$GLOBALS[DB_USER] instanceof Database){
            $GLOBALS[DB_USER] = Database::load();
        }
    }

    public static function mysql_prepare(){
        $params = func_get_args();
        if ($params <= 0){
            $message = 'Error trying to prepare a mysql query, no args were found';
            throw new Error(Http::INTERNAL_SERVER_ERROR, $message);
        }
        
        $sql = array_shift($params);
        if (substr_count($sql, '%s') != count($params)){
            $message = 'Arguments and sql query do not match '
                    . substr_count($sql, '%s') . ' were expected in sql query and only '
                    . count($params) . ' were sent';
            throw new Error(Http::INTERNAL_SERVER_ERROR, $message);
        }

        $sql = str_replace('%s', '"%s"', $sql);
        if (count($params) > 0){
            $params = array_map('mysql_real_escape_string', $params);
        }

        return vsprintf($sql, $params);
    }

    public static function generate_time() {
        return time() + self::ACCESS_TOKEN_TIMELIFE;
    }

    public static function generate_hash($access_token) {
        return '|' . Utils::token_rehash($access_token);
    }

    public static function validate_access_token($access_token)
    {
        $access_token=Utils::decrypt($access_token);
        $tokens = explode(',', $access_token);

        $hash = array_pop($tokens);
        
        $access_token_hashed = substr($hash, 1);
        $token = implode(',', $tokens);
        if ($access_token_hashed !== Utils::token_rehash($token))
        {
            $msg = 'Invalid access token 002';
            throw new Error(Http::UNAUTHORIZED, $msg);
        }

        list($time) = explode('.', $tokens[2]);
        if ($time < time())
        {
            $msg = 'Token has expired';
            throw new Error(Http::UNAUTHORIZED, $msg);
        }

        return true;
    }
    
    public static function encrypt($data){
       self::validate_data($data);
        $td = mcrypt_module_open('tripledes', '', 'ecb', '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, Utils::SALT, $iv);
        $encrypted = mcrypt_generic($td, trim($data));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return bin2hex($encrypted);
    }
    
    public static function decrypt($data){
        self::validate_data($data);
        $data=self::hex2bin($data);
        $td = mcrypt_module_open('tripledes', '', 'ecb', '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $key = substr(Utils::SALT, 0, mcrypt_enc_get_key_size($td));
        mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, trim($data));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return trim($decrypted);
    }
    
    private static function validate_data($data){
        if (empty($data) || strlen($data) < 10){
            $msg = 'Invalid access token...';
            throw new Error(Http::UNAUTHORIZED, $msg);
        }
    }
    
    public static function hex2bin($h){
        if (!is_string($h)) return null;
        $r='';
        for ($a=0; $a<@strlen($h); $a+=2) { $r.=@chr(@hexdec($h{$a}.$h{($a+1)})); }
        return $r;
    }
    
    public static function uuid(){
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
    
    public static function object_to_array($obj){
        $arr_obj = is_object($obj) ? get_object_vars($obj) : $obj;
        $arr=array();
        foreach ($arr_obj as $key => $val){
                $val = (is_array($val) || is_object($val)) ? self::object_to_array($val) : $val;
                $arr[$key] = $val;
        }
        return $arr;

    }
    
    public static function hostname(){
        $protocol = 'http://';
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443){
                $protocol = 'https://';
        }
        return $protocol.$_SERVER['SERVER_NAME'];
    }
    
    public function string_to_boolean($string){
        if (is_bool($string)) return $string;
        $string = trim(strtolower($string)) === 'true' ? true : false;
        return $string;
    }
    
    public static function token_rehash($access_token){
        return Utils::hash_hmac($access_token,  Utils::get_hash_salt() . $access_token);
    }
    
    public static function user_pass_rehash($username, $password, $timestamp, $role){
        return Utils::hash_hmac($timestamp . $username,  Utils::get_hash_salt() . $password . $role);
    }
    
    private static function hash_hmac($data, $key){
        $hmac = base64_encode(hash_hmac('sha256', $data, $key, TRUE));
        return strtr($hmac, array('+' => '-', '/' => '_', '=' => ''));
    }
    
    private static function get_hash_salt() {
        return hash('sha256', self::SALT);
    }

    public static function check_parameters($params,$madatory) {
        foreach($madatory as $v){
            if (!isset($params[$v])){
                $class = $line = '';
                $trace = debug_backtrace();
                if (isset($trace[0]['class'])) {
                    $class = ' in ' . $trace[0]['class'];
                }
                if (isset($trace[0]['line'])) {
                    $line = ' line ' . $trace[0]['line'];
                }
                $msg = $v. ' parameter is missing' . $class . $line;
                throw new Error(Http::BAD_REQUEST, $msg);
            }
        }
        return true;
    }

    public static function check_min_parameters($params,$min_params) {
        if (!is_array($params)){
            $msg = 'Parameter should be an array';
            throw new Error(Http::BAD_REQUEST, $msg);
        }
        if (count($params) < $min_params){
            $msg = 'Size of parameter must not be less than ' . $min_params;
            throw new Error(Http::BAD_REQUEST, $msg);
        }
        return true;
    }

    public static function format_numeric(&$params){
        foreach($params as $k=>$v){
            if (is_numeric($v)){
                $params[$k] = $v + 0;
                continue;
            }
            /*if (is_array($v)){
                self::format_numeric($v);
            }*/
        }
    }
}