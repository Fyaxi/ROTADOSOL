<?php
/**
 * DecoracaoDashboard TSession::getValue('unit_database')
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class DecoracaoDashboard extends TPage
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
            $total_ativos  = Decoracao::where('ativo','=','Y')->count();
            $total_inativos  = Decoracao::where('ativo','=','N')->count();
            //$total_renovar = Decoracao::where('ativo','=','Y')->where('dt_fim', '<=', date('Y-m-d'))->count();
            $contratos_grupo = ViewDecoracao::where('ativo','=','Y')->groupBy('nome_grupo')->sumBy('total');
            $contratos_tipo  = ViewDecoracao::where('ativo','=','Y')->groupBy('tipo_contrato')->sumBy('total');
            $top_clientes    = ViewDecoracao::where('ativo','=','Y')->groupBy('nome_cliente')->orderBy('total', 'desc')->sumBy('total');
            $old_clientes    = ViewDecoracao::where('ativo','=','Y')->take(5)->orderBy('dt_inicio', 'desc')->load();
            TTransaction::close();
            
            
            $indicator1 = new THtmlRenderer('app/resources/info-box.html');
            $indicator1->enableSection('main', ['title' => 'Decorações a Realizar', 'icon' => 'check-double', 'background' => 'green', 'value' => $total_ativos ] );
            
            
            $indicator2 = new THtmlRenderer('app/resources/info-box.html');
            $indicator2->enableSection('main', ['title' => 'Orçamentos Pendentes', 'icon' => 'hourglass-start', 'background' => 'orange', 'value' => $total_inativos ] );

            $table1 = TTable::create( [ 'class' => 'table table-striped table-hover', 'style' => 'border-collapse:collapse' ] );
            $table1->addSection('thead');
            $table1->addRowSet('Cliente', 'Valor');
            
            if ($top_clientes)
            {
                $table1->addSection('tbody');
                foreach ($top_clientes as $top_cliente)
                {
                    $row = $table1->addRow();
                    $row->addCell($top_cliente->nome_cliente);
                    $row->addCell('R$&nbsp;' . number_format($top_cliente->total,2,',','.'))->style = 'text-align:left';
                }
            }
            
            $table2 = TTable::create( [ 'class' => 'table table-striped table-hover', 'style' => 'border-collapse:collapse' ] );
            $table2->addSection('thead');
            $table2->addRowSet('Cliente', 'Data')->style = 'text-align:left';
            
            if ($old_clientes)
            {
                $table2->addSection('tbody');
                foreach ($old_clientes as $old_cliente)
                {
                    $row = $table2->addRow();
                    $row->addCell($old_cliente->nome_cliente);
                    $row->addCell(TDate::convertToMask($old_cliente->dt_inicio, 'yyyy-mm-dd', 'dd/mm/yyyy'))->style = 'text-align:left';
                }
            }
            
            $div->add( TElement::tag('div', $indicator1, ['class' => 'col-sm-6']) );
            $div->add( TElement::tag('div', $indicator2, ['class' => 'col-sm-6']) );
            $div->add( TElement::tag('div', TPanelGroup::pack('Decorações Por Valor', $table1),     ['class' => 'col-sm-6']) );
            $div->add( TElement::tag('div', TPanelGroup::pack('Listagem De Decorações', $table2),     ['class' => 'col-sm-6']) );
            
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
