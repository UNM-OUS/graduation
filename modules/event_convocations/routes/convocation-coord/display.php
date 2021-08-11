<?php
$coord = $package->noun();

echo "<div class='digraph-card'>";
echo implode(" | ", [$coord['netid'], $coord['email'], $coord['phone']]);
echo "</div>";
