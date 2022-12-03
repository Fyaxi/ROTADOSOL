<?php
/**
 * ContratoDashboard TSession::getValue('unit_database')
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class VendaDashboard extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        
        $div = new TElement('div');
        $div->class = "row";
        
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            $vendas_mes  = Venda::where('ativo','=','Y')->where('ano','=', date('Y'))->where('mes','=', date('m'))->sumBy('total');
            $vendas_ano  = Venda::where('ativo','=','Y')->where('ano','=', date('Y'))->sumBy('total');
            $vendas_por_mes  = Venda::where('ativo','=','Y')->where('ano','=', date('Y'))->orderBy('mes')->groupBy('mes')->sumBy('total');
            $vendas_por_ano  = Venda::where('ativo','=','Y')->groupBy('ano')->orderBy('ano')->sumBy('total');
            TTransaction::close();
            
            
            $indicator1 = new THtmlRenderer('app/resources/info-box.html');
            $indicator1->enableSection('main', ['title' => 'Vendas no mês', 'icon' => 'calendar-check', 'background' => 'green', 'value' => 'R$ '.number_format($vendas_mes,2,',','.') ] );
            
            $indicator2 = new THtmlRenderer('app/resources/info-box.html');
            $indicator2->enableSection('main', ['title' => 'Vendas no ano', 'icon' => 'calendar-check', 'background' => 'blue', 'value' => 'R$ '.number_format($vendas_ano,2,',','.') ] );
            
            
            
            $meses = ['1' => 'Jan', '2' => 'Fev', '3' => 'Mar', '4' => 'Abr', '5' => 'Mai', '6' => 'Jun', '7' => 'Jul', '8' => 'Ago', '9' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez'];
            
            $data = [];
            $data[] = [ 'Mês', 'Vendas' ];
            if ($vendas_por_mes)
            {
                //var_dump($vendas_por_mes);
                foreach($vendas_por_mes as $venda_por_mes)
                {
                    $data[] = [ $meses[ $venda_por_mes->mes ], (float) $venda_por_mes->total ];
                }
            }
            $grafico1 = new THtmlRenderer('app/resources/google_column_chart.html');
            $grafico1->enableSection('main', ['data'   => json_encode($data), 'width'  => '100%', 'height'  => '350px',
                                              'title'  => 'Vendas por mês', 'ytitle' => 'Vendido', 'xtitle' => 'Mês', 'uniqid' => uniqid()]);
            
            
            $data = [];
            $data[] = [ 'Ano', 'Vendas' ];
            if ($vendas_por_ano)
            {
                foreach($vendas_por_ano as $venda_por_ano)
                {
                    $data[] = [ $venda_por_ano->ano, (float) $venda_por_ano->total ];
                }
            }
            $grafico2 = new THtmlRenderer('app/resources/google_column_chart.html');
            $grafico2->enableSection('main', ['data'   => json_encode($data), 'width'  => '100%', 'height'  => '350px',
                                              'title'  => 'Vendas por ano', 'ytitle' => 'Vendido', 'xtitle' => 'Ano', 'uniqid' => uniqid()]);
            
            $div->add( TElement::tag('div', $indicator1, ['class' => 'col-sm-6']) );
            $div->add( TElement::tag('div', $indicator2, ['class' => 'col-sm-6']) );
            $div->add( TElement::tag('div', $grafico1,   ['class' => 'col-sm-12']) );
            $div->add( TElement::tag('div', $grafico2,   ['class' => 'col-sm-12']) );
            
            //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            $vbox->add($div);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        
        parent::add($vbox);
    }
}
