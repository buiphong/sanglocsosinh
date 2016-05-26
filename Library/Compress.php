<?php
/*
*
* Name: compress.php 
*
* Purpose: 
*	Allow Apache server to directly serve pre-gzipped and minified JS and CSS files
*	with no server overhead for deflating or gzipping the file each time it is served.
*	This script combines, minifies and gzips Javascript and Cascading Style Sheet files
*	in batch mode. Each combined group of JS or CSS creates two files: 
*		Minified; Minified and Gzipped; base file names set by user
*
* Reason for using:
*	You want to reduce server overhead by eliminating server calls and decreasing the number
*		of bytes transmitted.
*	You are serving move than one JS or CSS file per page.
*	Your JS and CSS are not minified and/or not GZipped	
*	Your Host server does not support or implement mod_pagespeed
*	Your Host server does not support/implement by default gzip or deflate for js or css files
*	Online minify packages have performance issues on your server and increase server overhead
*
* Features:
*	Source files unchanged, output written to user specified directory reserved
*		for compressed/minified files 
*	Appends time stamp to user file names avoiding cache issues when file data changes. 
*	Creates a PHP file that provides the timestamp for the created files
*		so user written PHP code can easily generate correct html links.
*	Creates output directory and .htaccess file on initial execution 
* 		allowing Apache to correctly serve gzipped JS and CSS files
*		and cache the combined files for one week
*	Deletes outdated versions of combined files
*
* Usage:
*	Install module in www/dir where dir is a directory of your choice
*		PHP file compress_timestamp.php is written to this directory
*		directory can be at any level	      
*	Install CSSMIN into www/dir and optionally change settings	
*	Install JSMIN into www/dir and optionally change settings	
*	Set output directory name, default: ../min
*		should be at the same level as your CSS directory or background-url may need to change
*	code the combining output file names and source file names
*	execute the script (initial)
*	Change your source code to use generated files, sample shown below
*	execute the script when changes are made to the source CSS or JS files
*	(optional) setup Cron job on your host to run weekly catching changes
*		that may not have been compressed 
*	
* Sample PHP implementation code:
* 
* require_once('compress_timestamp.php');		//load timestamp created by compress.php module
*							//sets field $compress_stamp=unix_timestamp					
* if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'],'GZIP')!==false)	
*	$gz='gz';
* else
*	$gz=null;
* echo '<link rel="stylesheet" type="text/css" href="min/css_schedule_'.$compress_stamp.'.css'.$gz.'" />',PHP_EOL;
* 
* //    the following scripts were combined into two groups named css_schedule and css_non_schedule
* //	echo '<link rel="stylesheet" type="text/css" href="CSS/menu.css" />',PHP_EOL;
* //	echo '<link rel="stylesheet" type="text/css" href="CSS/ThreeColumnFixed.css" />',PHP_EOL;
* //	echo '<link rel="stylesheet" type="text/css" href="CSS/sprite.css" />',PHP_EOL;
* //	echo '<link rel="stylesheet" type="text/css" href="CSS/iCal.css" />',PHP_EOL;
* 	
* PHP 5 or higher is required.
*
* Copyright (c) 2011 Arnold Burkhoff
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
* --
*
* @package compress.php
* @author Arnold Burkhoff <arnbme@gmail.com>
* @license http://opensource.org/licenses/mit-license.php MIT License
* @version 1.0.1 (2011-02-06)
* @link http://code.google.com/p/compress
* @dependencies: 
*	JSMin author Ryan Grove <ryan@wonko.com> https://github.com/rgrove/jsmin-php/
*		PHP version of Douglas Crockford's JSMin
* 	copyright 2002 Douglas Crockford <douglas@crockford.c
* 	copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
*
*	CSSMin author Joe Scyllia http://code.google.com/p/cssmin/
*	copyright	2008 - 2011 Joe Scylla <joe.scylla@gmail.com>*
*/

/**
 * Compression
 * @author phongbui
 */
class Compress
{
    public static function CompressJs($files = array(), $gzip=false)
    {
        $fl=null;//clear file data variable
        foreach($files as $value)//merge files in the group
        {
            if(!file_exists($value))
                $value = Url::getAppDir() . trim($value);
            $fl.= file_get_contents($value).' ';
        }
        //$len_orig=strlen($fl);
        $fl = Compression_JSMin::minify($fl);
        if($gzip)
            return gzencode ($fl,9);
        else
            return $fl;
    }

    public static function CompressCss($files = array(), $gzip = false)
    {
        $fl=null;//clear file data variable
        foreach($files as $value)//merge files in the group
        $fl.= file_get_contents($value).' ';
        //$len_orig=strlen($fl);
        $fl = Compression_CssMin::minify($fl);
        if($gzip)
            return gzencode ($fl,9);
        else
            return $fl;
    }
}
?>