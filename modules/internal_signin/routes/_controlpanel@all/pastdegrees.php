<?php

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Digraph\Forms\Form;
use Formward\Fields\FileMulti;

$package->cache_noStore();

$form = new Form('');
$form['files'] = new FileMulti('Spreadsheet files');

if ($form->handle()) {
    ini_set('max_execution_time', 300);
    $count = 0;
    /** @var \PDO */
    $pdo = $cms->pdo('degrees');
    $pdo->beginTransaction();
    foreach ($form['files']->value() as $file) {
        switch (pathinfo($file['name'], PATHINFO_EXTENSION)) {
            case 'xlsx':
                $reader = ReaderEntityFactory::createXLSXReader();
                break;
            case 'csv':
                $reader = ReaderEntityFactory::createCSVReader();
                break;
            default:
                throw new \Exception("Only xlsx and csv files are supported");
        }
        $reader->open($file['file']);
        $headers = null;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->getCells();
                if (!$headers) {
                    $headers = array_flip(array_map(function ($cell) {
                        return $cell->getValue();
                    }, $cells));
                } else {
                    // row data keyed by headers in first row
                    $rowData = array_map(function ($i) use ($cells) {
                        return @$cells[$i]->getValue();
                    }, $headers);
                    // dumbly try to insert, and just ignore errors
                    // that should be fine since awarded degrees are stable
                    if ($rowData['NetID'] && $rowData['Graduation Status'] == 'Awarded') {
                        $stmt = $pdo->prepare(
                            'INSERT INTO degree ' .
                                '(semester,netid,firstname,lastname,honors,category,degree,campus,college,department,program,major1,major2,minor1,minor2) ' .
                                'VALUES (:semester,:netid,:firstname,:lastname,:honors,:category,:degree,:campus,:college,:department,:program,:major1,:major2,:minor1,:minor2)'
                        );
                        $stmt->execute([
                            "semester" => $rowData['Academic Period'],
                            "netid" => $rowData["NetID"],
                            "firstname" => $rowData["Student First Name"],
                            "lastname" => $rowData["Student Last Name"],
                            "honors" => $rowData["Commencement Honors Flag"],
                            "category" => $rowData["Award Category"],
                            "degree" => $rowData["Degree"],
                            "campus" => $rowData["Campus"],
                            "college" => $rowData["College"],
                            "department" => $rowData["Department"],
                            "program" => $rowData["Program"],
                            "major1" => $rowData["Major"],
                            "major2" => $rowData["Second Major"],
                            "minor1" => $rowData["First Minor"],
                            "minor2" => $rowData["Second Minor"]
                        ]);
                        $count++;
                    }
                }
            }
        }
        $reader->close();
    }
    $pdo->commit();
    $cms->helper('notifications')->flashConfirmation('Added/updated ' . $count . ' degree records');
    $package->redirect($package->url());
} else {
    echo $form;
}
