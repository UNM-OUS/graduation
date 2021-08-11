<?php
$package->cache_noStore();
$noun = $package->noun();

echo "<div class='digraph-card'>";
echo implode(" | ", [
    $noun->link(null, 'add-convocation'),
    $noun->link(null, 'edit'),
]);
echo "</div>";

echo "<h2>Coordinators</h2>";
echo "<p class='incidental'>Contact the Office of the University Secretary if you need to have a coordinator added, removed, or edited. To add a coordinator you'll need to provide their name, main campus NetID, email address, and phone number.</p>";
echo "<p class='incidental'>Coordinators' contact information is not made public, and is only for the OUS to contact you if necessary. To change your department's public contact information " . $noun->link(null, 'edit') . ".</p>";
echo "<ul>";

foreach ($noun->coordinators() as $coord) {
    echo "<li>";
    echo "<strong>" . $coord->link() . "</strong> | ";
    echo implode(" | ", array_filter([$coord['netid'], $coord['email'], $coord['phone']]));
    echo "</li>";
}
echo "</ul>";

echo "<h2>Events</h2>";
foreach ($noun->events() as $event) {
    echo "<div class='digraph-card'>";
    echo "<div><strong>" . $event->link() . '</strong></div>';
    echo implode(" | ", [
        $event->link('Edit event', 'edit'),
        $event->link('Reports', 'reports'),
    ]);
    echo "</div>";
}
