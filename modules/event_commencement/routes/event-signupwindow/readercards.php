<?php

use Digraph\Modules\event_commencement\Signup;

$package->cache_noStore();

/** @var \Digraph\Modules\event_commencement\SignupWindow */
$signupwindow = $package->noun();
$perPage = 6;

$signups = $signupwindow->allSignups();
//filter incomplete signups
$signups = array_filter($signups, function ($e) {
    return $e->complete() && $e->primaryEvents();
});
//sort
if (stripos($signupwindow->name(), 'undergrad') === false) {
    // graduates sort by name
    usort($signups, function ($a, $b) {
        $sortOrder = [
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
} else {
    // undergrads sort by category/college, then name
    usort($signups, function ($a, $b) {
        $sortOrder = [
            'degree.degree_val.category',
            'degree.degree_val.college',
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
}
//turn into an array of fronts/backs of pages
$pageCount = ceil(count($signups) / $perPage);
$pages = [];
for ($i = 0; $i < $pageCount; $i++) {
    $pages[$i * 2] = [null, null, null, null, null, null];
    $pages[$i * 2 + 1] = [null, null, null, null, null, null];
}
for ($pos = 0; $pos < $perPage; $pos++) {
    for ($page = 0; $page < $pageCount; $page++) {
        if ($signup = array_shift($signups)) {
            $pages[$page * 2][$pos] = buildCardFront($signup);
            $pages[$page * 2 + 1][$pos] = buildCardBack($signup);
        }
    }
}

// output pages
foreach ($pages as $pid => $page) {
    echo "<div class='reader-card-page'>";
    foreach ($page as $card) {
        echo $card;
    }
    echo "</div>";
}

function buildCardFront(Signup $signup): string
{
    $classes = cardClasses($signup);
    $out = '<div class="reader-card-front ' . implode(' ', $classes) . '">';
    $out .=  '<div class="readercard-letter letter-' . strtolower(substr($signup['contact.lastname'], 0, 1)) . '">' . strtoupper(substr($signup['contact.lastname'], 0, 1)) . '</div>';
    $out .= '<div class="readercard-category readercard-category-' . preg_replace('/[^a-z]/', '', strtolower($signup->degreeCategory())) . '">' . $signup->degreeCategory() . '</div>';
    $out .= '<div class="readercard-name">' . $signup->name() . '</div>';
    $out .= '<div class="readercard-pronunciation">' . $signup['contact.pronunciation'] . '</div>';
    if ($signup->degreeCategory() == 'Doctoral/Terminal') {
        $out .= '<div class="readercard-program">' . $signup['degree.degree_val.program'] . '</div>';
        if ($signup['hooder.signup'] && $hooder = $signup->cms()->read($signup['hooder.signup'])) {
            if (stripos($hooder->signupWindow()->name(), 'platform') !== false) {
                $out .= '<div class="readercard-hooder hooder-platform">Hooded by Dr. ' . $hooder->name() . '</div>';
            } else {
                $out .= '<div class="readercard-hooder">Hooded by Dr. ' . $hooder->name() . '</div>';
            }
        } else {
            $out .= '<div class="readercard-hooder hooder-missing">Hooded by <span class="blank-space"></span></div>';
        }
    } else {
        $out .= '<div class="readercard-college">' . $signup['degree.degree_val.college'] . '</div>';
    }
    $out .= '</div>';
    return $out;
}

function buildCardBack(Signup $signup): string
{
    $classes = cardClasses($signup);
    $out = '<div class="reader-card-back ' . implode(' ', $classes) . '">';
    $out .= '<div class="readercard-email">' . $signup['contact.email'] . '</div>';
    $out .= '<div class="readercard-instructions">';
    $out .= '<p><strong>Keep this card with you.</strong> You will need to hand it to the readers as you walk across the stage so that they can read your name.</p>';
    $out .= '<p>';
    switch ($signup->degreeCategory()) {
        case 'Doctoral/Terminal':
            $out .= 'Wait in the nearby area until you are prompted by the marshals to line up and process into the ceremony seating area.';
            break;
        case 'Master':
            $out .= 'Wait in the nearby area until you are prompted by the marshals to line up and process into the ceremony seating area.';
            break;
        case 'Bachelor':
            $out .= 'Sit in the bleachers in the area designated for your school or college, the marshals will prompt you when it is time to process into the ceremony seating area.';
            break;
    }
    $out .= ' Once the ceremony begins you will be instructed by the Marshals on when and where to walk.</p>';
    $out .= '<p><strong>Keep your belongings with you.</strong> There is nowhere for you to store personal belongings, and you may not return to the same seat after walking across the stage.</p>';
    $out .= '<p>Marshals will instruct you on when and where to exit the seating area at the end of the ceremony.</p>';
    $out .= '</div>';
    $out .= '</div>';
    return $out;
}

function cardClasses(Signup $signup): array
{
    $classes = [
        'reader-card'
    ];
    if ($signup['spa.required']) {
        $classes[] = 'reader-card-spa';
    }
    if ($signup->degreeCategory() == 'Doctoral/Terminal') {
        if ($signup['hooder.signup'] && $hooder = $signup->cms()->read($signup['hooder.signup'])) {
            if (stripos($hooder->signupWindow()->name(), 'platform') !== false) {
                $classes[] = 'reader-card-hooder-platform';
            }
        } else {
            $classes[] = 'reader-card-hooder-missing';
        }
    }
    return $classes;
}

?>
<style>
    .reader-card-page {
        position: relative;
        height: 9in;
        width: 8in;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-content: flex-start;
        margin: 0 auto;
        margin-top: 0.75in;
    }

    .reader-card-page:nth-child(odd) {
        flex-direction: row-reverse;
    }

    .reader-card {
        position: relative;
        height: 3in;
        width: 4in;
        font-size: 12pt;
        line-height: 1.2;
        text-align: center;
        padding: 0.25in;
        box-sizing: border-box;
        font-family: sans-serif;
    }

    .readercard-id {
        font-family: monospace;
        font-size: 0;
        float: right;
    }

    .readercard-id svg {
        width: 0.75in;
        height: 0.75in;
        display: block;
    }

    .readercard-letter {
        position: absolute;
        top: 0;
        left: 0;
        padding: 10pt;
        font-size: 2em;
        font-weight: bold;
        color: #999;
    }

    .readercard-category {
        position: absolute;
        top: 0;
        right: 0;
        padding: 10pt;
        color: #999;
    }

    .readercard-name {
        font-weight: bold;
        font-size: 1.5em;
        margin-top: 0.5in;
    }

    .readercard-pronunciation {
        margin-bottom: 10pt;
        font-style: italic;
    }

    .readercard-hooder {
        margin-top: 10pt;
    }

    .hooder-missing,
    .hooder-platform {
        padding: 10pt;
        background: rgba(255, 255, 255, 0.5);
    }

    .hooder-missing {
        text-align: left;
    }


    .readercard-college {
        position: absolute;
        bottom: 0;
        right: 0;
        padding: 10pt;
        color: #999;
    }

    .readercard-email {
        margin-bottom: 5pt;
        border-bottom: 1pt solid #999;
        height: 20pt;
        text-align: left;
    }

    .readercard-instructions {
        text-align: left;
        font-size: 10pt;
    }

    .reader-card-front:after {
        position: absolute;
        left: 10pt;
        right: 10pt;
        bottom: 10pt;
        display: block;
        content: '';
    }

    .reader-card-spa:after {
        border-bottom: 20pt solid #dfefff;
    }

    .reader-card-hooder-platform:after {
        border-top: 20pt solid #fdf;
    }

    .reader-card-hooder-missing:after {
        border-top: 20pt solid #fee;
    }

    @media print {

        article.type_event-signupwindow>h1,
        #digraph-breadcrumb,
        #digraph-navbar,
        #digraph-actionbar,
        #digraph-masthead,
        #digraph-xsitenav,
        #digraph-unm,
        #digraph-loboalerts,
        #digraph-debug-dump {
            display: none;
        }

        .digraph-area {
            margin: 0;
            padding: 0;
        }

        .reader-card-page {
            page-break-after: always;
        }
    }
</style>