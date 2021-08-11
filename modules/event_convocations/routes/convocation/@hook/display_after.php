<?php
$package->cache_noStore();
$event = $package->noun();
$regaliaNames = [
    'required' => 'Required',
    'optional' => 'Optional',
    'none' => 'Informal/none',
];
echo "<div class='digraph-card incidental'>";
echo "<strong>Regalia requirements</strong>";
echo '<br>Faculty: ' . (@$regaliaNames[$event['regalia.faculty']] ?? $event['regalia.faculty']);
echo '<br>Students: ' . (@$regaliaNames[$event['regalia.faculty']] ?? $event['regalia.faculty']);
echo "</div>";

if ($org = $event->organization()) {
    echo $org->contactCard();
}
