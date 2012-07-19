<?php
// Start session tracking
session_start();

// Prepare data and make functions available
include(M . "model.php");

$renderView = '';
$pageTitle = "c$75 Finance";
$statusMessage = '';
$balance = '';
$displayBalance = '';

/* if we are not authenticated we need to login or register */
if(!(isset($_SESSION['authenticated']))) 
{
	/* show our status as not logged in */
	$loginStatus = "Not logged in";

    /* check if we have submitted a login or registration request */
    if ((isset($_POST['email'])) && (isset($_POST['password']))) 
    {
    	if ( isset($_POST['register'])) 
    	{
    		echo "Adding User<br />";
    		addUser($_POST['email'], $_POST['password']);
    	}
    	else
    	{
    	    echo "Checking Login<br />";
    	    $userId = loginUser($_POST['email'], $_POST['password']);
    	    echo "userId is" . $userId . "<br />";
    	    
    	    /* if we have a valid userId, move to the main page, otherwise print rejection */
    	    if( $userId > 0 )
    	    {
    	    	$_SESSION['authenticated']=true;
    	    	$_SESSION['userId']=$userId;
    	    	header("Location: http://$host$path/index.php");

    	    } else 
    	    {
    		    $statusMessage = "You have failed authentication<br />";
    	    }
    	}
    }  
    /* display loginForm */
	loginForm();

}
else
{
	/* We are logged in */
	$loginStatus = "Logged in (<a href=\"http://$host$path/index.php?action=logout\">Log Out</a>)";
	$balance = getBalance();
	$displayBalance = "Your account balance is: $balance";
	
	/* check what action the user is trying to take */
    if (isset($_GET['action'])) 
    {
    
    	$action = $_GET['action']; 
        
        switch ($action) 
        { 
        	case "getQuote":
        	
        		$pageTitle = "Get Quote";
        		$quoteResult = getQuote($_GET['symbol']);
        		
        	break;
        
            case "buy":
            
            	$pageTitle = "Buy Stocks";
            	$buyStockResult = buyStock($_GET['symbol'],$_GET['quantity']);
                  
            break;
            
            case "sell":
            
                $pageTitle = "Sell Stocks";
                  
            break;                   
            
            case "logout":
            
                session_destroy();
    	        header("Location: http://$host$path/index.php");

            break;      
        }
    }
    /* We are logged in*/
	$loginStatus = "Logged in (<a href=\"http://$host$path/index.php?action=logout\">Log Out</a>)";
	
	/*
	Important to set balance information AFTER any actions have been taken to reflect
	proper info
	*/
	$balance = getBalance();
	$displayBalance = "Your account balance is: $balance";

    displayMenu();
    displayStocks();
}

include(V . "view.php");

?>
