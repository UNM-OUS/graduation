<?php

use Digraph\Modules\event_commencement\Signup;

$package->cache_noStore();

/** @var \Digraph\Modules\event_commencement\SignupWindow */
$signupwindow = $package->noun();

/** @var Signup[] */
$signups = $signupwindow->allSignups();
//filter incomplete signups, or signups that aren't for a primary event
$signups = array_filter($signups, function (Signup $e) {
    if ($e->degreeCategory() != 'Doctoral/Terminal') {
        return false;
    }
    if (!$e->complete()) {
        return false;
    }
    if (!$e->primaryEvents()) {
        return false;
    }
    return true;
});
//sort
usort($signups, function (Signup $a, Signup $b) {
    $sortOrder = [
        'hooder.signup',
        'degree.degree_val.program',
        'contact.lastname',
        'contact.firstname'
    ];
    foreach ($sortOrder as $k) {
        if ($a[$k] != $b[$k]) {
            return strcasecmp($a[$k], $b[$k]);
        }
    }
    return 0;
});

// filter by whether students have hooder
$hasHooder = array_filter(
    $signups,
    function (Signup $signup) {
        return $signup['hooder.signup'] && $signup['hooder.signup'] !== '--none--';
    }
);
$noHooder = array_filter(
    $signups,
    function (Signup $signup) {
        return !$signup['hooder.signup'] || $signup['hooder.signup'] === '--none--';
    }
);

$hooders = [];
$hooderID = '--none--';
$hooder = null;
foreach ($hasHooder as $student) {
    if ($hooderID != $student['hooder.signup']) {
        $hooderID = $student['hooder.signup'];
        $hooder = $cms->read($student['hooder.signup']);
        $hooders[$hooderID] = [
            'hooder' => $hooder,
            'students' => []
        ];
    }
    $hooders[$hooderID]['students'][$student['dso.id']] = $student;
}
usort($hooders, function ($a, $b) {
    return strcasecmp($a['hooder']['contact.lastname'], $b['hooder']['contact.lastname']);
});

echo "<h2>Hooding faculty</h2>";
echo "<div style='columns: 3'>";
foreach ($hooders as $h) {
    $hooder = '<strong>' . $h['hooder']->name() . '</strong>';
    if ($h['hooder']['unm.college']) {
        $hooder .= '<br>' . $h['hooder']['unm.college'];
    }
    if ($h['hooder']['unm.department']) {
        $hooder .= '<br>' . $h['hooder']['unm.department'];
    }
    printf(
        '<div style="break-inside:avoid;margin-bottom: 1em;"><p style="margin-bottom:0">%s</p><ul style="margin-top:0">%s</ul></div>',
        $hooder,
        implode('', array_map(
            function (Signup $student): string {
                return '<li class="incidental">' . $student->name() . '</li>';
            },
            $h['students']
        ))
    );
}
echo "</div>";

if ($noHooder) {
    echo "<h2>No hooder assigned (" . count($noHooder) . ")</h2>";
}
