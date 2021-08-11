<?php
namespace Digraph\Modules\graduation_program;

class GradList
{
    protected $categories = [];
    protected $grads = [];
    protected $sorted = true;

    public function count(): int
    {
        return count($this->grads);
    }

    public function grads(): array
    {
        return $this->grads;
    }

    public function add(Graduate $grad)
    {
        $this->grads[$grad->hash()] = $grad;
        $this->addCategory('college', $grad->college());
        $this->addCategory('campus', $grad->campus());
        $this->addCategory('semester', $grad->semester());
        $this->addCategory('program', $grad->program());
        $this->addCategory('status', $grad->status());
        $this->sorted = false;
    }

    public function filter(callable $filter): GradList
    {
        $subset = new GradList();
        foreach ($this->grads as $grad) {
            if ($filter($grad)) {
                $subset->add($grad);
            }
        }
        return $subset;
    }

    protected function addCategory($category, $value)
    {
        $this->categories[$category][] = $value;
        $this->categories[$category] = array_values(array_unique($this->categories[$category]));
    }

    public function colleges(): array
    {
        $colleges = $this->category('college');
        usort($colleges, function ($a, $b) {
            return strcasecmp($this->comparableCollegeName($a), $this->comparableCollegeName($b));
        });
        return $colleges;
    }

    public function semesters(): array
    {
        $semesters = $this->category('semester');
        usort($semesters, function ($a, $b) {
            return strcasecmp($this->semesterCode($b), $this->semesterCode($a));
        });
        return $semesters;
    }

    protected function semesterCode(string $semester)
    {
        $code = ['0000', '00'];
        $semester = explode(' ', strtolower(trim($semester)));
        $code[0] = $semester[1];
        switch ($semester[0]) {
            case 'spring':
                $code[1] = '10';
                break;
            case 'summer':
                $code[1] = '20';
                break;
            case 'fall':
                $code[1] = '30';
                break;
        }
        return implode('', $code);
    }

    protected function comparableCollegeName(string $college): string
    {
        $college = trim($college);
        $college = preg_replace('/^(school|college) of /i', '', $college);
        $college = trim($college);
        return $college;
    }

    public function category(string $name): array
    {
        if (!isset($this->categories[$name])) {
            return [];
        }
        $this->sort();
        $values = $this->categories[$name];
        return $values;
    }

    protected function sort()
    {
        if ($this->sorted) {
            return;
        }
        usort($this->grads, function (Graduate $a, Graduate $b) {
            return strcasecmp($a->lastName(), $b->lastName()) ?? strcasecmp($a->firstName(), $b->firstName()) ?? 0;
        });
        foreach ($this->categories as $name => $values) {
            usort($this->categories[$name], function ($a, $b) {
                return strcasecmp($a, $b);
            });
        }
        $this->sorted = true;
    }
}
