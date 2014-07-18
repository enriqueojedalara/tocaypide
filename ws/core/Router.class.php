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
 * Routing the URLs
 *
 */
class Router{

    /**
    * Calls by method used to match the HTTP request
    */
    private $calls = array();

    public function get($uri, $class_method, $auth=true){
        $this->add_call(Http::GET, array('uri' => $uri, 'class_method'=>$class_method, 'auth'=>$auth));
    }

    public function post($uri, $class_method, $auth=true){
        $this->add_call(Http::POST, array('uri' => $uri, 'class_method'=>$class_method, 'auth'=>$auth));
    }

    public function put($uri, $class_method, $auth=true){
        $this->add_call(Http::PUT, array('uri' => $uri, 'class_method'=>$class_method, 'auth'=>$auth));
    }

    public function delete($uri, $class_method, $auth=true){
        $this->add_call(Http::DELETE, array('uri' => $uri, 'class_method'=>$class_method, 'auth'=>$auth));
    }

    public function run(){
        $request = new Request;
        $parameters=$request->parameters;

        $call = $this->match_call();
        if (isset($call['parameters'])){
            $parameters=array_merge($parameters, $call['parameters']);
        }
        if ($call['auth']){
            $headers=$request->get_headers();
            $parameters[Http::Authorization]=@$headers[Http::Authorization];
            if (empty($parameters[Http::Authorization])){
                $msg = 'Unauthorized, missing authorization';
                throw new Error(Http::UNAUTHORIZED, $msg);
            }
        }
        header('HTTP/1.0 200 OK');
        $response = $this->run_class_method($call['class_method'], $parameters);
        die( json_encode($response) );
        return ;
    }

    private function match_call(){
        $request = new Request;

        if (!isset($this->calls[$request->get_method()])){
            $msg = 'Just not found the stuff you are trying to reach ('.strtoupper($request->get_method()).': '.$request->get_service_url().')';
            throw new Error(Http::NOT_FOUND, $msg);
        }
        $p_values = array();
        $found=false;
        foreach($this->calls[$request->get_method()] as $call){
            preg_match_all('@:([\w]+)@', $call['uri'], $p_names, PREG_PATTERN_ORDER);
            $p_names = $p_names[0];
            $url_regex = preg_replace_callback('@:[\w]+@', 'Router::regex_url', $call['uri']);
            $url_regex .= '/?';
            if (preg_match('@^' . $url_regex . '$@', $request->get_service_url(), $p_values)) {
                array_shift($p_values);
                foreach($p_names as $index => $value) $call['parameters'][substr($value,1)] = urldecode($p_values[$index]);
                $found=true;
                break;
            }
        }
        if (!$found){
            $msg = 'Bad request '.strtoupper($request->get_method()).': '.$request->get_service_url();
            throw new Error(Http::BAD_REQUEST, $msg);
        }
        return $call;
    }

    private function run_class_method($class_method, $parameters){

        if (preg_match('/->/',$class_method)){
            $tokens=explode('->',$class_method);
            $object = new $tokens[0]();
            $response = $object->$tokens[1]($parameters);
        }
        else if (preg_match('/::/',$class_method)){
            $tokens=explode('::',$class_method);
            $response = $tokens[0]::$tokens[1]($parameters);
        }
        else{
            $msg = 'Error executing method '.$class_method;
            throw new Error(Http::INTERNAL_SERVER_ERROR, $msg);
        }
        return $response;
    }

    private function add_call($http_method, $call){
        $this->calls[$http_method][] = $call;
    }

    private static function regex_url($matches){
        return '([a-zA-Z0-9,_\+\-%]+)';
    }
}