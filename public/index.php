<?php
// Load configurations
// require_once '../app/config/config.php';
require_once '../app/config/Database.php';

$db = new Database();
$connection = $db->getConnection();

echo "Connected!";