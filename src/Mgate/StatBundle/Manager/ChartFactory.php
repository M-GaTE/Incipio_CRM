<?php

namespace Mgate\StatBundle\Manager;

use Mgate\StatBundle\Controller\IndicateursController;
use Ob\HighchartsBundle\Highcharts\Highchart;

/**
 * Class ChartFactory.
 */
class ChartFactory
{
    public function newColumnChart($series, $categories)
    {
        $ob = new Highchart();
        // OTHERS
        $ob->chart->type('column');
        $ob->yAxis->min(0);
        $ob->yAxis->max(100);
        $style = IndicateursController::$defaultStyle;
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->xAxis->labels(array('style' => $style));
        $ob->yAxis->labels(array('style' => $style));
        $ob->credits->enabled(false);
        $ob->legend->enabled(false);

        $ob->series($series);
        $ob->xAxis->categories($categories);

        $ob->title->text('Title');
        $ob->yAxis->title(array('text' => 'Title y', 'style' => $style));
        $ob->xAxis->title(array('text' => 'Title x', 'style' => $style));
        $ob->tooltip->headerFormat('<b>header Format</b><br />');
        $ob->tooltip->pointFormat('Point format');

        return $ob;
    }

    public function newPieChart($series){
        $ob = new Highchart();

        $ob->plotOptions->pie(array('allowPointSelect' => true, 'cursor' => 'pointer', 'showInLegend' => true, 'dataLabels' => array('enabled' => false)));
        $ob->series($series);
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->credits->enabled(false);
        $ob->title->text('Répartition des dépenses selon les comptes comptables (Mandat en cours)');
        $ob->tooltip->pointFormat('{point.percentage:.1f} %');

        return $ob;
    }
}
