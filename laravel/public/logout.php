<?php
session_start();
session_destroy();
header('Location: GI1.html');
exit();
?> 