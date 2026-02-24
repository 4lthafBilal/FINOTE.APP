<?php
session_start();
session_destroy();
// Pastikan mengarah ke auth.php karena file login.php tidak ada di VS Code Anda
header("Location: auth.php");
exit();
?>