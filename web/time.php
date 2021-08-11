<?php
$_SESSION['random_time_offset'] = $_SESSION['random_time_offset'] ?? random_int(0,120);
echo time() + $_SESSION['random_time_offset'];
