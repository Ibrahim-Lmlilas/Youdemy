<?php
session_start();
session_destroy();
header('Location: /Youdemy/views/auth/login.php');
exit;
