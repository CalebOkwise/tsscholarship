<?php
require_once dirname(__DIR__) . '/includes/auth.php';
logout_admin();
redirect_to('login.php');
