<?php

namespace Digraph\Modules\event_commencement\DegreeChunks;

use Digraph\Modules\ous_event_management\Chunks\AbstractChunk;

abstract class AbstractDegrees extends AbstractChunk
{
    protected $label = 'Degree information';
    const WEIGHT = 200;

    public function instructions(): ?string
    {
        return '<div class="notification notification-notice">Degree records are pulled periodically from Banner. If what you see here isn\'t what you expected, please check first with your academic advisor.</div>';
    }

    public function hook_update()
    {
        parent::hook_update();
        $this->pullDegrees();
    }

    public function pullDegrees(): array
    {
        $degrees = [];
        foreach ($this->signup->allUserListUsers() as $degree) {
            $degrees[md5(serialize([
                $degree['college'],
                $degree['program'],
                $degree['major'],
            ]))] = $degree;
        }
        $this->signup['degrees'] = $degrees;
        return $degrees;
    }

    public function degrees(): array
    {
        $degrees = $this->signup['degrees'] ?? $this->pullDegrees();
        if ($degree = $this->signup->customDegree()) {
            $degrees[md5(serialize([
                $degree['college'],
                $degree['program'],
                $degree['major'],
            ]))] = $degree;
        }
        return array_filter($degrees);
    }

    public static function degreesHTML(array $degrees)
    {
        $out = '';
        if (!$degrees) {
            return '';
        }
        foreach ($degrees as $d) {
            $out .= '<div class="digraph-card">';
            if (@$d['name']) {
                $out .= '<div><strong>' . $d['name'] . '</strong></div>';
            }
            $out .= '<div><strong>' . @$d['program'] . '</strong></div>';
            if (@$d['major']) {
                $out .= '<div><em>Major:</em> ' . $d['major'];
                if (@$d['second major']) {
                    $out .= ', ' . $d['second major'];
                }
                $out .= "</div>";
            }
            if (@$d['first minor']) {
                $out .= '<div><em>Minor:</em> ' . $d['first minor'];
                if (@$d['second minor']) {
                    $out .= ', ' . $d['second minor'];
                }
                $out .= "</div>";
            }
            if (@$d['honors']) {
                $out .= '<div><em>University Honors:</em> ' . $d['honors'] . '</div>';
            }
            if (@$d['semester']) {
                $out .= '<div><em>' . $d['graduation status'] . ' ' . $d['semester'] . '</em></div>';
            }
            $out .= '</div>';
        }
        return $out;
    }

    protected function form_map(): array
    {
        $this->pullDegrees();
        return [];
    }
}
