<?php
$noun = $package->noun();

echo "<h2>Events</h2>";
foreach ($noun->events() as $event) {
    echo "<div class='digraph-card'>";
    echo "<div><strong>" . $event->link() . '</strong></div>';
    echo $event->metaCell();
    echo "</div>";
}
