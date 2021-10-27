<?php

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Digraph\Modules\event_commencement\Signup;

$package->cache_noStore();

/** @var \Digraph\Modules\event_commencement\SignupWindow */
$signupwindow = $package->noun();

$signups = $signupwindow->allSignups();
//filter incomplete signups
$signups = array_filter($signups, function ($e) {
    return $e->complete();
});
//sort
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
//turn into an array of fronts/backs of pages
$pageCount = ceil(count($signups) / 6);
$pages = [];
for ($i = 0; $i < $pageCount; $i++) {
    $pages[$i * 2] = [null, null, null, null, null, null];
    $pages[$i * 2 + 1] = [null, null, null, null, null, null];
}
for ($pos = 0; $pos < 6; $pos++) {
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
        echo '<div class="reader-card">';
        echo $card;
        echo '</div>';
    }
    echo "</div>";
}

function buildCardFront(Signup $signup): string
{
    $out =  '<div class="readercard-letter letter-' . strtolower(substr($signup['contact.lastname'], 0, 1)) . '">' . strtoupper(substr($signup['contact.lastname'], 0, 1)) . '</div>';
    $out .= '<div class="readercard-category readercard-category-' . preg_replace('/[^a-z]/', '', strtolower($signup->degreeCategory())) . '">' . $signup->degreeCategory() . '</div>';
    $out .= '<div class="readercard-name">' . $signup->name() . '</div>';
    $out .= '<div class="readercard-pronunciation">' . $signup['contact.pronunciation'] . '</div>';
    if ($signup->degreeCategory() == 'Doctoral/Terminal') {
        $out .= '<div class="readercard-program">' . $signup['degree.degree_val.program'] . '</div>';
        if ($signup['hooder.signup'] && $hooder = $signup->cms()->read($signup['hooder.signup'])) {
            $out .= '<div class="readercard-hooder">Hooded by Dr. ' . $hooder->name() . '</div>';
        } else {
            $out .= '<div class="readercard-hooder hooder-missing">Hooded by</div>';
        }
    } else {
        $out .= '<div class="readercard-college">' . $signup['degree.degree_val.college'] . '</div>';
    }
    return $out;
}

function buildCardBack(Signup $signup): string
{
    $qr = new Writer(new ImageRenderer(
        new RendererStyle(400),
        new SvgImageBackEnd()
    ));
    $out = '<div class="readercard-id">' . $qr->writeString($signup['dso.id']) . '</div>';
    $out .= '<div class="readercard-email">' . $signup['contact.email'] . '</div>';
    $out .= '<div class="readercard-instructions">';
    $out .= 'TODO: instructions';
    $out .= '</div>';
    return $out;
}

?>
<style>
    .reader-card-page {
        position: relative;
        height: 9in;
        width: 8in;
        display: flex;
        flex-wrap: wrap;
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

    .hooder-missing {
        padding: 10pt;
        background: #fee;
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