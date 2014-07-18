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
 * Manage Http Requests
 *
 */
class Request {
    const URL_BASE = '/ws/';

    public function __construct(){
        $this->url=$this->get_service_url();
        $this->method=$this->get_method();
        $this->query=$this->get_query();
        $this->headers=$this->get_headers();
        $this->parameters=$this->get_parameters();

        $url_tokens=explode('/',$this->url);
        $this->classe = $url_tokens[0];
        $this->action = @$url_tokens[1];
    }

    public function get_service_url(){
        $request = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
        return '/'.substr_replace($request,'',0,(strpos($request,self::URL_BASE)+strlen(self::URL_BASE)));
    }

    public function get_method(){
        return trim(strtolower($_SERVER['REQUEST_METHOD']));
    }

    public function get_headers(){
        $headers = apache_request_headers();
        if (isset($headers['Content-Type'])){
            $headers['Content-Type'] = trim(strtolower($headers['Content-Type']));
        }
        return $headers;
    }

    public function get_query(){
        return $_SERVER['QUERY_STRING'];
    }

    public function get_parameters($method=null){
        if (!isset($method)){
            $method = $this->get_method();
        }
        $params=array();
        switch($method){
            case Http::GET:
                $params = $_GET;
                break;
            case Http::POST:
                if (0 === strpos($this->headers['Content-Type'], 'application/json')){
                    $params = (array)json_decode(file_get_contents("php://input"));
                }
                else{
                    $params = $_POST;
                }
                if (isset($_FILES) && !empty($_FILES)){
                    $params['files']  = $_FILES;
                }
                break;
            case Http::PUT:
            case Http::DELETE:
                $params = (array)json_decode(file_get_contents("php://input"));
                break;
            default:
                $msg = 'HTTP ' .$method. ' method is not allowed';
                throw new Error(Http::BAD_REQUEST, $msg);
        }
        return $params;
    }
}
