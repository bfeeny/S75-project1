<?php
/* Start session tracking */
session_start();

/* Prepare data and make functions available */
include(M . "model.php");

/* initialize a few variables */
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
    	/* we have a registration request */
    	if ( isset($_POST['register'])) 
    	{
    		/* check to make sure email is valid */
    		if (!(validEmail($_POST['email'])))
    		{
	    		echo "Your email is not valid<br />";
    		}
    		/* check to make sure password is valid */
    		elseif (!(validPass($_POST['password'])))
    		{
    			echo "Your password is not valid.  Must be at least 6 characters<br />";
    			echo "and contain at least 1 number and 1 letter.<br />";
    		}
    		/* if we have a good email and pass then add the user */
    		else
    		{
	    		addUser($_POST['email'], $_POST['password']);
	    		echo "Username Added!!<br />";
	    	}
    	}
    	else
    	{
    	    $userId = loginUser($_POST['email'], $_POST['password']);
    	    
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
	/* We are logged in, show cash balance information */
	$loginStatus = "Logged in (<a href=\"http://$host$path/index.php?action=logout\">Log Out</a>)";
	$balance = getBalance();
	$displayBalance = "Your cash account balance is: $ $balance";
	
	/* check what action the user is trying to take */
    if (isset($_GET['action'])) 
    {
    	$action = $_GET['action']; 
        
        switch ($action) 
        { 
        	/* show a stock quote */
        	case "getQuote":
        	
        		$pageTitle = "Get Quote";
        		$quoteResult = getQuote($_GET['symbol']);
        		
        	break;
        	/* buy a stock */
            case "buy":
            
            	$pageTitle = "Buy Stocks";
            	$buyStockResult = buyStock($_GET['symbol'],$_GET['quantity']);
                  
            break;
            /* sell a stock */
            case "sell":
            
                $pageTitle = "Sell Stocks";
                $sellStockResult = sellStock($_GET['symbol']);
                  
            break;     
            /* show a total of all stocks, including total cash and stocks */
            case "portfolioTotal":
            
                $pageTitle = "Portfolio Total";
                $portfolioTotal = TRUE;
                  
            break;   
            /* logout user */                         
            case "logout":
            
                session_destroy();
    	        header("Location: http://$host$path/index.php");

            break;      
        }
    }
    /* We are logged in, allow the option to logout */
	$loginStatus = "Logged in (<a href=\"http://$host$path/index.php?action=logout\">Log Out</a>)";
	
	/*
	Important to set balance information AFTER any actions have been taken to reflect
	proper info
	*/
	$balance = getBalance();
	$displayBalance = "Your cash account balance is: $ $balance";

	/* display the main page */
    displayMenu();
    
    /* display a list of our stocks */
    displayStocks();
}

/* include view which handles page formatting */
include(V . "view.php");

?>
