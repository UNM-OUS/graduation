<?php

namespace Digraph\Modules\graduation_program;

use Digraph\DSO\Noun;
use Digraph\Helpers\AbstractHelper;
use Digraph\Modules\ous_event_management\EventGroup;

class GradProgramHelper extends AbstractHelper
{
    /**
     * Return a GradList of graduates of the given category(s)
     *
     * Known types are: Certificate, Associate, Bachelor, Master, Doctoral
     *
     * @param Noun $page
     * @param string|array $category
     * @return GradList
     */
    public function gradList(Noun $page, $category = null, $originalStrings = false): GradList
    {
        $gradlist = new GradList();
        $graph = $this->cms->helper('graph');
        $group = $graph->nearest($page['dso.id'], 'event-group');
        foreach ($graph->children($page['dso.id'], 'event-program-userlist') as $list) {
            $list->map(function ($row) use ($gradlist, $category, $group, $originalStrings) {
                if ($category) {
                    if (is_array($category)) {
                        if (!in_array($row['category'], $category)) {
                            return false;
                        }
                    } elseif ($row['category'] != $category) {
                        return false;
                    }
                }
                if (!$originalStrings && $group) {
                    $this->correctStrings($row, $group);
                }
                $gradlist->add(new Graduate($row));
            });
        }
        return $gradlist;
    }

    public function correctStrings(array &$row, EventGroup $group)
    {
        foreach ($group['stringcorrections'] ?? [] as $key => $replacements) {
            if (@$row[$key]) {
                foreach ($replacements as $r) {
                    if ($row[$key] == $r[0]) {
                        $row[$key] = $r[1];
                        break;
                    }
                }
            }
        }
    }

    public function sortNames(string $text): array
    {
        $names = preg_split('/[\r\n]+/', $text);
        $names = array_map('trim', $names);
        $names = array_filter($names);
        $names = array_map(
            function ($line) {
                $line = preg_split('/ *[\,\|] */', $line);
                $name = $line[0];
                $lastName = preg_replace('/.* /', '', $name);
                $text = @$line[1];
                return [
                    'name' => $name,
                    'lastName' => $lastName,
                    'text' => $text,
                ];
            },
            $names
        );
        usort(
            $names,
            function ($a, $b) {
                return strcasecmp($a['lastName'], $b['lastName']);
            }
        );
        return $names;
    }
}
