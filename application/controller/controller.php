<?php
// Start session tracking
session_start();

// Prepare data and make functions available
include(M . "model.php");

// Check if we have a session

	// if we do then check for actions

	// if no actions display main dashboard
	
// if we have no session, display login page with option to register


connectDb();

$quote = new stock;
$quote->get_stock_quote("MSFT");

$renderView = "$quote->name: $quote->last";

// render view
include(V . "view.php");

?>
