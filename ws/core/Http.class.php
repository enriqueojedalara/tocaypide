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
 * Manage Http
 *
 */
class Http{
    const POST = 'post';
    const GET = 'get';
    const PUT = 'put';
    const DELETE = 'delete';
    const OK = 200;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const TIMEOUT = 408;
    const CONFLICT = 409;
    const I_AM_A_TEAPOT = 418;
    const AUTHENTICATION_TIMEOUT = 419;
    const INTERNAL_SERVER_ERROR = 500;
    const SERVICE_UNAVAILABLE = 503;
    
    public static $http_methods;
    
    public static function is_http_allowed($class, $action, $method){
        $args = func_get_args();
        array_walk($args, create_function('&$val', '$val = strtolower(trim($val));'));
        return isset(self::$http_methods[$args[0]][$args[1]][$args[2]]);
    }
}