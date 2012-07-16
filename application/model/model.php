<?php

// From PHP Tutorials Examples Introduction to PHP PDO http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html#4.3
function connectDb () 
{
	
    // mysql hostname
    $hostname = 'localhost';
    
    // mysql username
    $username = 'jharvard';
    
    // mysql password
    $password = 'crimson';
    
    try 
    {
        $dbh = new PDO("mysql:host=$hostname;dbname=mysql", $username, $password);
        // echo message saying we have connected
        echo 'Connected to database';
    }
    catch(PDOException $e)
    {
        echo $e->getMessage();
    }
        
}

// Scriptplayground Stock Quotes Tutorial http://v2.scriptplayground.com/tutorials/php/Stock-Quotes/
class stock
{
    function get_stock_quote($symbol)
    {
    	$url = sprintf("http://finance.yahoo.com/d/quotes.csv?s=%s&f=sl1" ,$symbol);
    	$fp = @fopen($url, "r");
    	if($fp == FALSE)
    	{
    		print "Error, Can\'t Open" . "\"$url\"";
    	}
    	else
    	{
    		$array = @fgetcsv($fp , 4096 , ",");
    		@fclose($fp);
    		$this->name = $array[0];
    		$this->last = $array[1];
    	}
    }
}

// function to check that stock is valid

// function to buyStock

// function to sellStock

// function to check that username is valid

// function to check that password is valid

// function to check that email is valid

?>
