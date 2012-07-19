<?php



// mysql_escape_string on input such as email

// use transactions

// urlencode input pass to url
// anytime you send user input to a browser send htmlspecialchars
// mysql real escape chars or prepare




/* From PHP Tutorials Examples Introduction to PHP PDO 
   http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html#4.3 
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

// Build a form using Twitter bootstrap API, use POST so values are not shown in URL
function loginForm() 
{
	global $renderView;
	global $pageTitle;
	
	$pageTitle = "Please Login";
     			          			 
    $renderView = "<form method=\"post\">
     			   <input name=\"email\" id=\"email\" type=\"text\" class=\"input-small\" placeholder=\"Email\"><br />
     			   <input name=\"password\" id=\"password\" type=\"password\" class=\"input-small\" placeholder=\"Password\"><br />
     			   <button type=\"submit\" class=\"btn btn-primary\">
     			   <i class=\"icon-user icon-white\"></i> Sign In</button>
     			   <button type=\"submit\" name=\"register\" value=\"register\" class=\"btn btn-info\">
     			   <i class=\"icon-arrow-right icon-white\"></i> Register Now</button>
     			   </form>";     			      			   
}


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

        // below try cause heavily based off Example 1 at http://www.php.net/manual/en/pdo.transactions.php
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
	                
        // close database and return 
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

        // close database and return 
        $pdo = null;
        return $userId;
}

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

        // close database and return 
        $pdo = null;
        
        return $balance;
}

function displayStocks()
{
		global $pdo;
		global $renderView;
		
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
        	$renderView .= "<table width=\"300\">";
        	$renderView .= "<tr align=\"left\"><th>Symbol</th><th>Shares</th><th>Action</th></tr>";
        	
            foreach ($stmt as $row) 
            {
            	$results[] = $row;
            	$renderView .= "<tr><td>".$row['symbol']."</td><td>".$row['shares']."</td>";
            	$renderView .= "<td><a href=\"index.php?action=sell&symbol=" . $row['symbol']. "\">Sell</a></tr>";
            }
            $renderView .= "</table>";
        } 
        else
        {
	        $renderView .= "Your portfolio is currently empty";
        }
        
        // close database and return 
        $pdo = null;
}


function displayMenu ()
{
	global $renderView;
	global $quoteResult;
	global $buyStockResult;
	
	$renderView = "<form class=\"form-inline\" name=\"submitForm\" method=\"GET\" action=\"index.php\">";
	$renderView .= "<input class=\"btn btn-mini\" type=\"submit\" value=\"Get Quote\">";
	$renderView .= "<input type=\"hidden\" name=\"action\" value=\"getQuote\">";
    $renderView .= "<input type=\"text\" class=\"span1\" name=\"symbol\" />";
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

	$renderView .= "<form class=\"form-inline\" name=\"submitForm\" method=\"GET\" action=\"index.php\">";
    $renderView .= "<input class=\"btn btn-mini\" type=\"submit\" value=\"Buy Stock\">";
	$renderView .= "<input type=\"hidden\" name=\"action\" value=\"buy\">";
	$renderView .= "<input type=\"text\" class=\"span1\" name=\"symbol\" />";
	$renderView .= "&nbsp;";
	$renderView .= "Quantity  <input type=\"text\" class=\"span1\" name=\"quantity\" />";
	$renderView .= $buyStockResult . "<br />";
    $renderView .= "</form>";


  //  <input type="hidden" name="param1" value="param1Value">
    //<A HREF="javascript:document.submitForm.submit()">Click Me</A>

	$renderView .= "<br /><br />";
}

/*
   Code based on combination of section notes and Scriptplayground Stock Quotes Tutorial 
   http://v2.scriptplayground.com/tutorials/php/Stock-Quotes/
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
   	   $buyStockResult = "&nbsp;&nbspInvalid quantity";

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
//	INSERT INTO table (a,b,c) VALUES (1,2,3)
//  ON DUPLICATE KEY UPDATE c=c+1;	 

		/* get database handle */
        $pdo = connectDb();
        
        /* build query */
        $query1 = sprintf("INSERT INTO stocks (id,symbol,shares) VALUES('%s','%s','%s') ON DUPLICATE KEY UPDATE shares=shares+'%s'"   
        														, $_SESSION['userId'], $symbol, $quantity, $quantity);
        $query2 = sprintf("UPDATE users SET balance=balance-'%s' WHERE id='%s'"   
        														,$total, $_SESSION['userId']);

        // below try cause heavily based off Example 1 at http://www.php.net/manual/en/pdo.transactions.php
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
	                
        // close database and return 
        $pdo = null;
        $buyStockResult = "&nbsp;&nbsp;$quantity shares of $symbol successfuly purchased for a total of $ $total";
	 }
   }
   return $buyStockResult;

}

// function to check that stock is valid

// potentially allow user to enter csv list of symbols for quotes

// function to sellStock

// function to check that username is valid

// function to check that password is valid

// function to check that email is valid

?>
