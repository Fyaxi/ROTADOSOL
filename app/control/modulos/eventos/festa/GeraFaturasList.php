<?php
/**
 * ContratoDashboard 'DBUNIDADE'
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class GeraFaturasList extends TPage
{
    private $form;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle('Gerar Fatura Por Festa');
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

        $input_search = new TEntry('search');
        $input_search->placeholder = 'Pesq. Nome do Cliente';
        $input_search->setSize('100%');
        $contrato_list->enableSearch($input_search, 'cliente->nome_fantasia');
        
        $hbox = new THBox;
        $hbox->style = 'border-bottom: 1px solid gray;padding-bottom:10px';
        $hbox->add( new TLabel('Listagens dos faturamentos a gerar') );
        $hbox->add( $input_search )->style = 'float:right;width:30%;';
        
        // load order items
        TTransaction::open('DBUNIDADE');
        $contratos = Contrato::where('ativo', '=', 'Y')->where('FaturaID', 'IS', NULL)->load();
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
            TTransaction::open('DBUNIDADE');
            
            $data = $this->form->getData();
            
            $contratos_ids = $data->contrato_list;
            
            if ($contratos_ids)
            {
                foreach ($contratos_ids as $contrato_id)
                {
                    $contrato = Contrato::find($contrato_id);
                    
                    if ($contrato)
                    {
                        $Faturamento  = Fatura::where('FestaID', '=', $contrato_id)->where('ativo', 'like', 'Y')->load();
                        //print_r($Faturamento);
                        if($Faturamento)
                        {
                            TToast::show('error', 'Já existe uma <b>fatura</b> em aberto para esse contrato!', 'top center', 'fas:window-close' );
                        }
                        elseif(empty($contrato->PagamentoEntrada))
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
                            $fatura->FestaID            = $contrato->id;
                            
                            $fatura->VendedorID         = $contrato->IdVendedor;
                            $fatura->VendedorLogin      = $contrato->VendedorLogin;

                            $fatura->store();

                            $fatura_contrato = new Contrato($contrato_id);
                            $fatura_contrato->FaturaID = $fatura->id;
                            $fatura_contrato->ultima_fatura = TDateTime::convertToMask(date('Y-m-d'), 'dd/mm/yyyy', 'yyyy-mm-dd');
                            $fatura_contrato->store();

                            $pos_action = new TAction(['ContratoList', 'onReload']);
                            new TMessage('info', 'Fatura gerada com sucesso!', $pos_action); 
                            
                            //print_r($fatura);
                        }

                        //$contrato_items = ContratoItem::where('contrato_id', '=', $contrato->id)->load();
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
                
                
                //$pos_action = new TAction(['GeraFaturasList', 'onReload']);
                //new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
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
