<?php

// Organized the same as Peter Myer Nore did in section
define('PROJECT1', '/home/jharvard/vhosts/project1/');
define('APP', PROJECT1 . 'application/');
define('M',   APP      . 'model/');
define('V',   APP      . 'view/');
define('C',   APP      . 'controller/');

// start controller
require(C . "controller.php");
?>