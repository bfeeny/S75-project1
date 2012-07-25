<?php

/* From PHP Tutorials Examples Introduction to PHP PDO 
   http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html#4.3 
   connect to our database
 */
function connectDb () 
{		
    try 
    {
        return $pdo = new PDO(DSN, DB_USER, DB_PASS);
    }
    catch(PDOException $e)
    {
        echo $e->getMessage();
    }       
}

/* Build a form using Twitter bootstrap API, use POST so values are not shown in URL */
function loginForm() 
{
	global $renderView;
	global $pageTitle;
	
	$pageTitle = "Please Login";
     			          			 
    $renderView = "<form name=\"loginForm\" method=\"post\">
     			   <input name=\"email\" id=\"email\" type=\"text\" class=\"input-small\" placeholder=\"Email\"><br />
     			   <input name=\"password\" id=\"password\" type=\"password\" class=\"input-small\" placeholder=\"Password\"><br />
     			   <button type=\"submit\" class=\"btn btn-primary\">
     			   <i class=\"icon-user icon-white\"></i> Sign In</button>
     			   <button type=\"submit\" name=\"register\" value=\"register\" class=\"btn btn-info\">
     			   <i class=\"icon-arrow-right icon-white\"></i> Register Now</button>
     			   </form>";     	
    /*
    javascript to place focus on first textbox, code learned from How to Make a Text Field Automatically Active (Set Focus)
    http://www.mediacollege.com/internet/javascript/form/focus.html as well as section notes
    */
    $renderView .= "<script type=\"text/javascript\">";
    $renderView .= "document.forms['loginForm'].elements['email'].focus();";
    $renderView .= "</script>";
		      			   
}

/* Add a new user to the database */
function addUser($email, $password)
{
		global $pdo;
		
		/* prepare email address and password hash for safe query */
        $email = mysql_escape_string($email);
        $pwdhash = hash("SHA1",$password);

        /* get database handle */
        $pdo = connectDb();
        
        /* build query */
        $query = sprintf("INSERT INTO users (email,passwordhash) VALUES('%s','%s')"   
        														,strtolower($email),$pwdhash);

        /* below try cause heavily based off Example 1 at http://www.php.net/manual/en/pdo.transactions.php */
        try 
        {  
	        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	        
	        $pdo->beginTransaction();
	        $pdo->exec($query);
	        $pdo->commit();
	    }	        
	    catch (Exception $e) 
	    {
	        $pdo->rollBack();
	        echo "Failed: " . $e->getMessage();
	    }
	                
        /* close database and return  */
        $pdo = null;
}

/*
part of this function was written using Chris Gerbers session notes/examples and part came from 
http://www.php.net/manual/en/pdo.query.php
*/
function loginUser($email, $password)
{
		global $pdo;
		$userId = 0;
		
		/* prepare email address and password hash for safe query */
        $email = mysql_escape_string($email);
        $pwdhash = hash("SHA1",$password);

        /* get database handle */
        $pdo = connectDb();

        /* build query */
        $query = sprintf("SELECT id FROM users WHERE LOWER(email)='%s' AND passwordhash='%s'"    
        														,strtolower($email),$pwdhash);
   
        /* get statement handle from executed query */														
        $stmt = $pdo->query($query);

        /* set userId if it is present in our result */
        if ($stmt)
        {
            $row = $stmt->fetch();
            if (isset($row[0]))
                $userId = $row[0];
        }

        /* close database and return  */
        $pdo = null;
        return $userId;
}

/* display a users cash balance */
function getBalance()
{
		global $pdo;
		
		/* prepare for safe query */
        $userId = mysql_escape_string($_SESSION['userId']);

        /* get database handle */
        $pdo = connectDb();

        /* build query */
        $query = sprintf("SELECT balance FROM users WHERE id='%s'"    
        														,$userId);
   
        /* get statement handle from executed query */														
        $stmt = $pdo->query($query);

        /* set balance if it is present in our result */
        if ($stmt)
        {
            $row = $stmt->fetch();
            if (isset($row[0]))
                $balance = $row[0];
        }

        /* close database and return  */
        $pdo = null;
        
        return $balance;
}

/* display a list of users stocks */
function displayStocks()
{
		global $pdo;
		global $renderView;
		global $portfolioTotal;
		global $balance;
		$total = 0;
		
		/* prepare for safe query */
        $userId = mysql_escape_string($_SESSION['userId']);

        /* get database handle */
        $pdo = connectDb();

        /* build query */
        $query = sprintf("SELECT symbol,shares FROM stocks WHERE id='%s'"    
        														,$userId);
   
        /* get statement handle from executed query */														
        $stmt = $pdo->query($query);
        if($stmt->rowCount() > 0 )
        {               
        	$renderView .= "<table style=\"width: 300px;\">";
        	$renderView .= "<tr style=\"text-align: left;\"><th>Symbol</th><th>Shares</th><th>Action</th>";
        	
        	/* if the portfolioTotal value is true we display actual realtime value information */
        	if($portfolioTotal) 
        	{
	        	$renderView.= "<th>Total</th>";
        	}
        	$renderView.="</tr>";
        	
        	
            foreach ($stmt as $row) 
            {
            	$results[] = $row;
            	$renderView .= "<tr><td>".$row['symbol']."</td><td>".$row['shares']."</td>";
            	$renderView .= "<td><a href=\"index.php?action=sell&amp;symbol=" . $row['symbol']. "\">Sell</a>";
            	if($portfolioTotal) 
            	{
	            	$quoteResult = getQuote($row['symbol']);
	            	$renderView .= "<td>$" . $quoteResult['lastTrade'] . "</td>";
	            	$total += $quoteResult['lastTrade'];
            	}
            	$renderView .= "</tr>";
            }
            if($portfolioTotal)
            {
	            $renderView .= "<tr><td colspan=\"3\">Total Stock Value</td><td>$ $total</td></tr>";
	            $renderView .= "<tr><td colspan=\"3\">Total Cash</td><td>$ $balance</td></tr>";
	            $total+=$balance;
	            $renderView .= "<tr><td colspan=\"3\">Total Portfolio Value</td><td>$ $total</td></tr>";
            }
            $renderView .= "</table>";
        } 
        /* user has no stocks in their portfolio */
        else
        {
	        $renderView .= "Your portfolio is currently empty";
        }
        
        /* close database and return  */
        $pdo = null;
}

/*
display the menu.  I could have used a number of different ways to handle multiple submit buttons.  I chose to do 3 <form>
elements because this allowed me to keep my case statement consistant, looking for action=
*/

function displayMenu ()
{
	global $renderView;
	global $quoteResult;
	global $buyStockResult;
	global $sellStockResult;
	
	
	$renderView = "<form class=\"form-inline\" name=\"submitForm\" method=\"GET\" action=\"index.php\">";
	$renderView .= "<input class=\"btn btn-mini\" type=\"submit\" value=\"Get Quote\">";
	$renderView .= "<input type=\"hidden\" name=\"action\" value=\"getQuote\">";
    $renderView .= "<input type=\"text\" class=\"span1\" name=\"symbol\" />";
    
    /* if we have a status message from a previous quote request display it */
    if(isset($quoteResult['lastTrade'])) 
    {
        if($quoteResult['lastTrade'] > 0)
        {
            $renderView .= "&nbsp;&nbsp;Latest quote for " . $quoteResult['symbol'] . " is $" . $quoteResult['lastTrade'];  
        }
        else
        {
	        $renderView .= "&nbsp;&nbsp" . $quoteResult['symbol'] . " is an invalid stock symbol";
        }
    }
    $renderView .= "</form>";
    
    /*
    javascript to place focus on first textbox, code learned from How to Make a Text Field Automatically Active (Set Focus)
    http://www.mediacollege.com/internet/javascript/form/focus.html as well as section notes
    */
    $renderView .= "<script type=\"text/javascript\">";
    $renderView .= "document.forms['submitForm'].elements['symbol'].focus();";
    $renderView .= "</script>";
    
    /*
   	javascript function to validate our number is positive non-decimal number, learned concept from 
    10+ Useful JavaScript Regular Expressions... at 
    http://ntt.cc/2008/05/10/over-10-useful-javascript-regular-expression-functions-to-improve-your-web-applications-efficiency.html
    */
    $renderView .= "<script type=\"text/javascript\">";
    $renderView .= "function isPositiveWholeNumber(quantity)";
   	$renderView .= "{";
	$renderView .= "var regex = /^\d+$/;";
	$renderView .= "return regex.test(quantity.value);";
	$renderView .= "}";
	$renderView .= "</script>";

	/* trapping submit demonstrated in lectures/section, used to check input on client side */
	$renderView .= "<form onsubmit=\"return isPositiveWholeNumber(quantity)\" class=\"form-inline\" name=\"submitForm\" method=\"GET\" action=\"index.php\">";
    $renderView .= "<input class=\"btn btn-mini\" type=\"submit\" value=\"Buy Stock\">";
	$renderView .= "<input type=\"hidden\" name=\"action\" value=\"buy\">";
	$renderView .= "<input type=\"text\" class=\"span1\" name=\"symbol\" />";
	$renderView .= "&nbsp;";
	$renderView .= "Quantity  <input type=\"text\" class=\"span1\" id=\"quantity\" name=\"quantity\" />";
	$renderView .= $buyStockResult;
	$renderView .= $sellStockResult;
	$renderView .= "<br />";
    $renderView .= "</form>";
    
    $renderView .= "<form class=\"form-inline\" name=\"submitForm\" method=\"GET\" action=\"index.php\">";
    $renderView .= "<input class=\"btn btn-mini\" type=\"submit\" value=\"Total Portfolio\">";
	$renderView .= "<input type=\"hidden\" name=\"action\" value=\"portfolioTotal\">";
	$renderView .= "<br />";
    $renderView .= "</form>";

	$renderView .= "<br /><br />";
}

/*
   Code based on combination of section notes and Scriptplayground Stock Quotes Tutorial 
   http://v2.scriptplayground.com/tutorials/php/Stock-Quotes/
   Get a stock quote from yahoo
*/
   
function getQuote($symbol)
{
	$result = array();
	$url = sprintf("http://finance.yahoo.com/d/quotes.csv?s=%s&f=sl1" ,$symbol);
	$fp = @fopen($url, "r");
	if($fp == FALSE)
	{
		print "Error, Can\'t Open" . "\"$url\"";
	}
	else
	{
	 if ($row = fgetcsv($fp))
         if (isset($row[1]))
             $result = array("symbol"    => $row[0],
                             "lastTrade" => $row[1]);
                                                        
		@fclose($fp);
	}
	
	return $result;
}

/*
Information used in this function learned in section notes 
and http://dev.mysql.com/doc/refman/5.0/en/insert-on-duplicate.html
*/
function buyStock($symbol, $quantity) 
{
   $buyStockResult = '';
   $symbol=mysql_escape_string(strtoupper($symbol));
   $quantity=mysql_escape_string($quantity);
   
   $quoteResult=getQuote($symbol);
   if (!($quoteResult['lastTrade'] > 0))
   {
	   $buyStockResult = "&nbsp;&nbsp" . $quoteResult['symbol'] . " is an invalid stock symbol";
   } 
   elseif ($quantity <= 0)
   {
   	   $buyStockResult = "&nbsp;&nbspPlease enter a quantity greater than zero";

   } 
   	 /*
   	    routine to check for non-decimal taken from 
   	    http://stackoverflow.com/questions/6772603/php-check-if-number-is-decimal
   	  */
   elseif ((float) $quantity !== floor($quantity))
   {
	   $buyStockResult = "&nbsp;&nbspPlease enter a non-decimal quantity";
   } 
   else
   {
	 $total = $quoteResult['lastTrade'] * $quantity;  
	 $balance = getBalance();
	 if($balance < $total) 
	 {
		 $buyStockResult = "&nbsp;&nbsp;Insufficient funds";		 
	 }
	 else
	 {

		/* get database handle */
        $pdo = connectDb();
        
        /* build query */
        $query1 = sprintf("INSERT INTO stocks (id,symbol,shares) VALUES('%s','%s','%s') ON DUPLICATE KEY UPDATE shares=shares+'%s'"   
        														, $_SESSION['userId'], $symbol, $quantity, $quantity);
        $query2 = sprintf("UPDATE users SET balance=balance-'%s' WHERE id='%s'"   
        														,$total, $_SESSION['userId']);

        /* below try cause heavily based off Example 1 at http://www.php.net/manual/en/pdo.transactions.php */
        try 
        {  
	        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	        
	        $pdo->beginTransaction();
	        $pdo->exec($query1);
	        $pdo->exec($query2);
	        $pdo->commit();
	    }	        
	    catch (Exception $e) 
	    {
	        $pdo->rollBack();
	        echo "Failed: " . $e->getMessage();
	    }
	                
        /* close database and return */ 
        $pdo = null;
        $buyStockResult = "&nbsp;&nbsp;$quantity shares of $symbol successfuly purchased for a total of $ $total";
	 }
   }
   return $buyStockResult;

}

/* sell a stock */
function sellStock($symbol) 
{

   $sellStockResult = '';
   $quoteResult = '';
   $symbol = mysql_escape_string(strtoupper($symbol));
   
   $quoteResult = getQuote($symbol);
   if (!($quoteResult['lastTrade'] > 0))
   {
	   $buyStockResult = "&nbsp;&nbsp" . $quoteResult['symbol'] . " is an invalid stock symbol";
   } 
   
   /*
   	  routine to check for non-decimal taken from 
   	  http://stackoverflow.com/questions/6772603/php-check-if-number-is-decimal
   */
   else
   {
       $balance = getBalance();
       $total = 0;
	    
       /* get database handle */
	    
       $pdo = connectDb();
	          
       /* build queries */
       $query1 = sprintf("SELECT shares FROM stocks WHERE id='%s' AND symbol='%s'"   
        														, $_SESSION['userId'], $symbol);           														
	   $query2 = sprintf("DELETE FROM stocks WHERE id='%s' AND symbol='%s'"   
        														, $_SESSION['userId'], $symbol); 	
       /* Perform all three queries in one transaction */																											
       try 
       {  
	       $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	        
	       $pdo->beginTransaction();	
           $stmt = $pdo->query($query1);
           /* set userId if it is present in our result */
           if ($stmt)
           {
               $row = $stmt->fetch();
               if (isset($row[0]))
               {
                   $quantity = $row[0];
               }
           }

           $total = $quantity * $quoteResult['lastTrade'];
           $query3 = sprintf("UPDATE users SET balance=balance+'%s' WHERE id='%s'"   
        														, $total, $_SESSION['userId']); 
           $pdo->exec($query2);
	       $pdo->exec($query3);
	       $pdo->commit();	   	       
	   }	        
	   catch (Exception $e) 
	   {
	       $pdo->rollBack();
	       echo "Failed: " . $e->getMessage();
	   }	   
	               
       /* close database and return */ 
       $pdo = null;
       
       $sellStockResult = "&nbsp;&nbsp;$quantity shares of $symbol sold at a price of $ {$quoteResult['lastTrade']} each $ $total total";
    }  
       return $sellStockResult;
}

/**
Below function taken from "Validate an E-Mail Address with PHP, the Right Way
Linux Journal
Douglas Lovell
http://www.linuxjournal.com/article/9585?page=0,3

Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.
*/
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

/*
below function taken from examples at PHP Password Complexity Checker 2
http://www.zoobey.com/index.php/resources/all-articles-list/181-php-password-complexity-checker-2
*/
function validPass($password)
{
	$isValid = true;
	
    /* must be at least 6 characters */
    if( strlen($password) < 6 ) 
    {
        $isValid = false;
    }
    /* must contain at least 1 number */
    if( !preg_match("#[0-9]+#", $password) ) 
    {
        $isValid = false;
    }
    /* must contain at least 1 letter */
    if( !preg_match("#[a-z]+#", $password) ) 
    {
        $isValid = false;
    }
    return $isValid;
}


?>
