<?php
$dir = getcwd();
chdir("../pages");
$i2ce_site_user_access_init = null;
$i2ce_site_user_database = null;
require_once( "../pages" . DIRECTORY_SEPARATOR . 'config.values.php');

$local_config = getcwd() . DIRECTORY_SEPARATOR .'local' . DIRECTORY_SEPARATOR . 'config.values.php';
if (file_exists($local_config)) {
    require_once($local_config);
}

if(!isset($i2ce_site_i2ce_path) || !is_dir($i2ce_site_i2ce_path)) {
    echo "Please set the \$i2ce_site_i2ce_path in $local_config";
    exit(55);
}

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');

I2CE::raiseMessage("Connecting to DB");
putenv('nocheck=1');
if (isset($i2ce_site_dsn)) {
    @I2CE::initializeDSN($i2ce_site_dsn,   $i2ce_site_user_access_init,    $i2ce_site_module_config);         
} else if (isset($i2ce_site_database_user)) {    
    I2CE::initialize($i2ce_site_database_user,
                     $i2ce_site_database_password,
                     $i2ce_site_database,
                     $i2ce_site_user_database,
                     $i2ce_site_module_config         
        );
} else {
    die("Do not know how to configure system\n");
}

I2CE::raiseMessage("Connected to DB");

require_once($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'CLI.php');
$ff = I2CE_FormFactory::instance();
$facilities=array("facility|100","facility|103","facility|11","facility|112","facility|12","facility|120","facility|13","facility|138","facility|14","facility|154","facility|16","facility|164","facility|171","facility|172","facility|174","facility|178","facility|179","facility|180","facility|181","facility|182","facility|19","facility|2","facility|3","facility|31","facility|32","facility|33","facility|34","facility|35","facility|46","facility|5","facility|56","facility|6","facility|63","facility|68","facility|72","facility|83","facility|90");
foreach ($facilities as $facility) {
	$facObj=$ff->createContainer($facility);
	$facObj->populate();
	$facObj->delete(false,true);
	}
?>