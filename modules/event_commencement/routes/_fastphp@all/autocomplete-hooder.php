<?php

use Digraph\Modules\event_commencement\Signup;

$package->cache_noStore();
$package->makeMediaFile('hooders.json');
$q = $package['url.args.term'];
$definitive = $package['url.args._definitive'] == 'true';
$pdo = $package->cms()->pdo();

// get windows
$windows = explode(',', $package['url.args.windows']);
$signupIDs = [];
foreach ($windows as $window) {
    $signupIDs = array_merge(
        $signupIDs,
        $package->cms()->helper('graph')->childIDs($window, 'event-signupwindow-signup', 1)
    );
}
$signupIDs = array_map(
    [$pdo, 'quote'],
    $signupIDs
);
$signupIDs = '(' . implode(',', $signupIDs) . ')';

// first try fast search
$terms = array_filter(preg_split('/ +/', strtolower($q)));
$search = $package->cms()->factory()->search();
$where = '${dso.id} in ' . $signupIDs;
$where .= ' AND (';
$t = [];
foreach ($terms as $term) {
    if ($definitive) {
        $t[] = '${dso.id} = ' . $pdo->quote($term);
    } else {
        $t[] = '${contact.firstname} LIKE ' . $pdo->quote("$term");
        $t[] = '${contact.lastname} LIKE ' . $pdo->quote("$term");
    }
}
$where .= implode(' OR ', $t);
$where .= ')';
$search->where($where);
$search->order('${contact.firstname} asc, ${contact.lastname} asc');
$results = array_filter(
    $search->execute(),
    function (Signup $signup) {
        return $signup->complete();
    }
);

// if nothing is found try slow search
if (!$results) {
    $search = $package->cms()->factory()->search();
    $where = '${dso.id} in ' . $signupIDs;
    $where .= ' AND (';
    $t = [];
    foreach ($terms as $term) {
        $t[] = '${contact.firstname} LIKE ' . $pdo->quote("%$term%");
        $t[] = '${contact.lastname} LIKE ' . $pdo->quote("%$term%");
    }
    $where .= implode(' OR ', $t);
    $where .= ')';
    $search->where($where);
    $search->order('${contact.firstname} asc, ${contact.lastname} asc');
    $results = array_filter(
        $search->execute(),
        function (Signup $signup) {
            return $signup->complete();
        }
    );
}

// prepare results
$results = array_filter(array_map(
    function (Signup $signup) use ($terms) {
        $score = 0;
        if (in_array(strtolower($signup['dso.id']), $terms)) {
            $score += 100;
        }
        if (in_array(strtolower($signup['contact.firstname']), $terms)) {
            $score++;
        }
        if (in_array(strtolower($signup['contact.lastname']), $terms)) {
            $score++;
        }
        if (in_array(strtolower($signup['contact.email']), $terms)) {
            $score += 2;
        }
        if (in_array(strtolower($signup['signup.for']), $terms)) {
            $score += 2;
        }
        return [
            'value' => $signup['dso.id'],
            'label' => $signup->contactInfo()->name(),
            'desc' => implode(', ', array_filter([
                $signup['unm.college'],
                $signup['unm.department']
            ])),
            'score' => $score
        ];
    },
    $results
));

usort(
    $results,
    function ($a, $b) {
        return $b['score'] - $a['score'];
    }
);

$results[] = [
    'value' => '--none--',
    'label' => '<em>Choose a hooder for me</em>'
];

if ($definitive) {
    $results = array_shift($results);
}

echo json_encode($results);
