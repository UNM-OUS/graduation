<?php
$package->cache_noStore();

$search = $cms->factory()->search();
$search->where('${dso.type} = "convocation-org"');
$search->order('${digraph.name}');
$orgs = array_filter(
    $search->execute(),
    function ($e) {
        return $e->isEditable();
    }
);

if ($orgs) {
    $cms->helper('notifications')->printNotice('You are listed as a coordinator for the following organizations.');
} else {
    $cms->helper('notifications')->printError('You are not listed as a coordinator for any organizations.');
}
foreach ($orgs as $org) {
    echo "<div class='digraph-card'>";
    echo "<h2>" . $org->name() . "</h2>";
    echo "<p>";
    echo implode(' | ', [
        $org->link('manage department', 'manage'),
        $org->link('edit department', 'edit'),
        $org->link('public page', 'display'),
    ]);
    echo "</p>";
    echo "</div>";
}
