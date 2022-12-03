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
class GeraFaturasList2 extends TPage
{
    private $form;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle('Gerar Fatura Por Decoração');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        $contrato_list = new TCheckList('contrato_list');
        
        $contrato_list->addColumn('id',          'N° Festa',          'center',  '10%');
        $contrato_list->addColumn('cliente->nome_fantasia', 'Cliente', 'left',    '50%');
        $contrato_list->addColumn('VendedorLogin',          'Vendedor',          'center',  '50%');
        //$column_ultima_fatura = $contrato_list->addColumn('ultima_fatura', 'Última fatura', 'left',    '50%');
        $column_total = $contrato_list->addColumn('valor',  'Valor',       'right',    '40%');
        
        $column_total->setTransformer( function($value) {
            if (is_numeric($value)) {
                return 'R$&nbsp;'.number_format($value, 2, ',', '.');
            }
            return $value;
        });
        /*
        $column_ultima_fatura->setTransformer( function($value) {
            $value_br = TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
            
            $month = substr($value,5,2);
            $year  = substr($value,0,4);
            
            $label = ( ($month == date('m') ) && ( $year==date('Y') ) ) ? 'success' : 'warning';
            
            if ($value)
            {
                $div = new TElement('span');
                $div->class="label label-" . $label;
                $div->style="text-shadow:none; font-size:12px";
                $div->add( $value_br );
                return $div;
            }
        });
        */
        $input_search = new TEntry('search');
        $input_search->placeholder = 'Pesq. Nome do Cliente';
        $input_search->setSize('100%');
        $contrato_list->enableSearch($input_search, 'cliente->nome');
        
        $hbox = new THBox;
        $hbox->style = 'border-bottom: 1px solid gray;padding-bottom:10px';
        $hbox->add( new TLabel('Listagens dos faturamentos a gerar') );
        $hbox->add( $input_search )->style = 'float:right;width:30%;';
        
        // load order items
        TTransaction::open(TSession::getValue('unit_database'));
        $contratos = Decoracao::where('ativo', '=', 'Y')->load();
        $contrato_list->addItems( $contratos );
        TTransaction::close();
        
        $this->form->addContent( [$hbox] );
        $this->form->addFields( [$contrato_list] );
        
        $this->form->addHeaderAction( 'Gerar Fatura', new TAction([$this, 'onGenerate']), 'fa:clipboard-check green');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        parent::add($vbox);
    }
    
    /**
     * Simulates an save button
     * Show the form content
     */
    public function onGenerate($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            
            $data = $this->form->getData();
            
            $contratos_ids = $data->contrato_list;
            
            if ($contratos_ids)
            {
                foreach ($contratos_ids as $contrato_id)
                {
                    $contrato = Decoracao::find($contrato_id);

                    if ($contrato)
                    {
                        $Faturamento  = Fatura::where('DecoracaoID', '=', $contrato_id)->where('ativo', 'like', 'Y')->load();
                        //print_r($Faturamento);
                        if($Faturamento)
                        {
                            TToast::show('error', 'Já existe uma <b>fatura</b> em aberto para esse contrato!', 'top center', 'fas:window-close' );
                        }
                        elseif (empty($contrato->PagamentoEntrada))
                        {
                            TToast::show('error', 'Forma de recebimento da entrada não é válido para gerar uma fatura.', 'top center', 'fas:window-close' );
                        }
                        else
                        {
                            $fatura = new Fatura;
                            $fatura->cliente_id         = $contrato->cliente_id;
                            $fatura->dt_fatura          = date('Y-m-d');
                            $fatura->mes                = date('m');
                            $fatura->ano                = date('Y');
                            $fatura->ativo              = 'Y';
                            $fatura->financeiro_gerado  = 'N';
                            
                            $fatura->FormaRecebimento   = $contrato->PagamentoEntrada;
                            $fatura->ValorEntrada       = $contrato->ValorEntrada;
                            $fatura->ValorDesconto      = $contrato->ValorDesconto;
                            $fatura->ValorContrato      = $contrato->ValorContrato;
                            $fatura->ValorTotal         = $contrato->ValorTotal;
                            $fatura->total              = $contrato->valor;
                            $fatura->DecoracaoID        = $contrato->id;
                            
                            $fatura->VendedorID         = $contrato->IdVendedor;
                            $fatura->VendedorLogin      = $contrato->VendedorLogin;

                            $fatura->store();

                            new TMessage('info', 'Fatura geradas com sucesso');
                            //$pos_action = new TAction(['GeraFaturasList', 'onReload']);
                            //new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);

                            $fatura_contrato = new Decoracao($contrato_id);
                            $fatura_contrato->FaturaID = $fatura->id;
                            $fatura_contrato->ultima_fatura = TDateTime::convertToMask(date('Y-m-d'), 'dd/mm/yyyy', 'yyyy-mm-dd');
                            $fatura_contrato->store();
                            
                            //print_r($fatura);
                        }
                        
                        //$contrato_items = DecoracaoItem::where('contrato_id', '=', $contrato->id)->load();
                        //if ($contrato_items)
                        //{
                        //    foreach ($contrato_items as $contrato_item)
                        //    {
                        //        $fatura_item = new FaturaItem;
                        //        $fatura_item->servico_id = $contrato_item->servico_id;
                        //        $fatura_item->fatura_id  = $fatura->id;
                        //        $fatura_item->valor      = $contrato_item->valor;
                        //        $fatura_item->quantidade = $contrato_item->quantidade;
                        //        $fatura_item->total      = $contrato_item->total;
                        //        $fatura_item->store();
                        //    }
                        //}
                    }
                }
                
                
            }
            
            // put the data back to the form
            $this->form->setData($data);
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
