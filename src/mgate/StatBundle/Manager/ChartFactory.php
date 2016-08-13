<?php


namespace mgate\StatBundle\Manager;

use mgate\StatBundle\Controller\IndicateursController;
use Ob\HighchartsBundle\Highcharts\Highchart;

/**
 * Class ChartFactory
 * @package mgate\StatBundle\Manager
 * A service to create more easily charts and factoring common code.
 * Each parameter define here can be overwritten or deleted (setted to null)
 */
class ChartFactory
{

    public function newColumnChart($series, $categories){
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
}