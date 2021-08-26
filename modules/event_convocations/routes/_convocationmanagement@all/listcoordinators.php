<?php

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

$search = $cms->factory()->search();
$search->where('${dso.type} = "convocation-org"');
$search->order('${digraph.name}');
/** @var Digraph\Modules\event_convocations\Organization[] */
$orgs = $search->execute();

$people = [];
foreach ($orgs as $org) {
    foreach ($org->coordinators() as $coordinator) {
        $people[] = [
            $org->name(),
            $coordinator->name(),
            $coordinator['email']
        ];
    }
}

if ($package['url.args.spreadsheet']) {
    $package->cache_noStore();
    $package->makeMediaFile('coordinators.xlsx');
    $writer = WriterEntityFactory::createXLSXWriter();
    $writer->openToBrowser('coordinators.xlsx');
    foreach ($people as $row) {
        $writer->addRow(
            WriterEntityFactory::createRow(array_map(
                [WriterEntityFactory::class, 'createCell'],
                $row
            ))
        );
    }
    $writer->close();
    return;
} else {
    $url = $package->url();
    $url['args.spreadsheet'] = 1;
    echo "<table>";
    echo "<tr><th colspan='" . count(reset($people)) . "'><a href='$url'>Download as a spreadsheet</a></th></tr>";
    foreach ($people as $row) {
        echo "<tr><td>";
        echo implode('</td><td>', $row);
        echo "</td></tr>";
    }
    echo "</table>";
}
