<?php
session_start();
session_destroy();
header('Location: /yooudemy/views/auth/login.php');
exit;
