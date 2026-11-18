<?php
echo "Home file exists: " . (file_exists('views/home.php') ? 'Yes' : 'No');
echo "<br>Content length: " . strlen(file_get_contents('views/home.php'));