<?php
$package->cache_private();

$event = $package->noun();
if ($event['cancelled']) {
    $cms->helper('notifications')->printWarning('This event has been CANCELLED');
}
echo "<div class='digraph-card incidental'>";
echo $event->metaCell();
echo '<div>Managed by: ' . $event->organization()->link() . '</div>';
echo "</div>";
