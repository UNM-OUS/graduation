<?php
$package->cache_private();

$event = $package->noun();
if ($event['cancelled']) {
    $cms->helper('notifications')->printWarning('This event has been CANCELLED');
}
echo '<p class="incidental">Managed by: ' . $event->organization()->link() . '</p>';