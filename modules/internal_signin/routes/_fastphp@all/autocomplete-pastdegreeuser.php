<?php
$package->cache_noStore();

$results = [];

$words = preg_split('/ +/', strtolower(trim($package['url.args.term'])));
$words = array_slice($words, 0, 3);
/** @var \PDO */
$pdo = $package->cms()->pdo('degrees');

if ($package['url.args._definitive']) {
    // for definitive results pull by ID so it's mad fast
    $query = $pdo->prepare('SELECT * FROM degree WHERE id = :q');
    if ($query->execute(['q' => $package['url.args.term']])) {
        $results[] = $query->fetch(PDO::FETCH_ASSOC);
    }
} else {
    // otherwise do a text search and all the ranking
    // locate all results
    foreach ($words as $word) {
        $query = $pdo->prepare('SELECT * FROM degree WHERE firstname LIKE :q OR lastname LIKE :q ORDER BY id desc LIMIT 50');
        if ($query->execute(['q' => "$word%"])) {
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $results[$row['id']] = $row;
            }
        }
    }

    // score and sort results
    $query = $package['url.args.term'];
    $results = array_map(
        function (array $row) use ($query, $words) {
            $row['score'] = 0;
            foreach ($words as $word) {
                $row['score'] += similar_text(metaphone($query), metaphone($row['firstname'] . ' ' . $row['lastname']));
            }
            $row['score'] += 10 * similar_text(metaphone($query), metaphone($row['firstname'] . ' ' . $row['lastname']));
            return $row;
        },
        $results
    );
    usort(
        $results,
        function ($a, $b) {
            return $b['score'] - $a['score'];
        }
    );
}

// turn into final ready-to-display results
$results = array_map(
    function (array $row) use ($words) {
        return [
            'label' => $row['firstname'] . ' ' . $row['lastname'] . ' (' . $row['semester'] . ')',
            'value' => intval($row['id'])
        ];
    },
    $results
);

// shift off one result for definitive result
if ($package['url.args._definitive']) {
    $results = array_shift($results);
}

$package->makeMediaFile('results.json');
echo json_encode($results);
