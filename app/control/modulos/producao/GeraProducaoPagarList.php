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
class GeraProducaoPagarList extends TPage
{
    private $form;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle('Gerar Pagamento Produção');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        $fatura_list = new TCheckList('fatura_list');
        
        $fatura_list->addColumn('id',          'Id',          'center',  '10%');
        $fatura_list->addColumn('cliente->nome_fantasia', 'Cliente', 'left',    '50%');
        $column_total = $fatura_list->addColumn('total',  'Valor',       'right',    '40%');
        
        $column_total->setTransformer( function($value) {
            if (is_numeric($value)) {
                return 'R$&nbsp;'.number_format($value, 2, ',', '.');
            }
            return $value;
        });
        
        $input_search = new TEntry('search');
        $input_search->placeholder = _t('Search');
        $input_search->setSize('100%');
        $fatura_list->enableSearch($input_search, 'cliente->nome');
        
        $hbox = new THBox;
        $hbox->style = 'border-bottom: 1px solid gray;padding-bottom:10px';
        $hbox->add( new TLabel('Produção') );
        $hbox->add( $input_search )->style = 'float:right;width:30%;';
        
        // load order items
        TTransaction::open(TSession::getValue('unit_database'));
        $faturas = Producao::where('ativo', '=', 'Y')->where('financeiro_gerado', '=', 'N')->load();
        $fatura_list->addItems( $faturas );
        TTransaction::close();
        
        $this->form->addContent( [$hbox] );
        $this->form->addFields( [$fatura_list] );
        
        $this->form->addHeaderAction( 'Gerar', new TAction([$this, 'onGenerate']), 'fa:clipboard-check green');
        
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
            
            $faturas_ids = $data->fatura_list;
            
            if ($faturas_ids)
            {
                foreach ($faturas_ids as $fatura_id)
                {
                    $fatura = Producao::find($fatura_id);
                    
                    if ($fatura)
                    {
                        $conta_receber = new ContaPagar;
                        $conta_receber->dt_emissao = date('Y-m-d');
                        $conta_receber->dt_vencimento = date("Y-m-d", strtotime("+1 month") );
                        $conta_receber->pessoa_id = $fatura->cliente_id;
                        $conta_receber->valor = $fatura->total;
                        $conta_receber->forma_recebimento = 1;
                        $conta_receber->ano = date('Y', strtotime("+1 month") );
                        $conta_receber->mes = date('m', strtotime("+1 month") );
                        $conta_receber->ativo = 'Y';
                        $conta_receber->store();
                        
                        $fatura->financeiro_gerado = 'Y';
                        $fatura->store();
                        
                        $fatura_conta_receber = new ProducaoContaPagar;
                        $fatura_conta_receber->conta_receber_id = $conta_receber->id;
                        $fatura_conta_receber->fatura_id        = $fatura->id;
                        $fatura_conta_receber->store();
                    }
                }
                
                new TMessage('info', 'Produção(s) a pagar gerada(s) com sucesso!');
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
