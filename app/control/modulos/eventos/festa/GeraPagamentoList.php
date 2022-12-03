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
class GeraPagamentoList extends TPage
{
    private $form;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle('Gerar Pagamentos Por Festa');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        $pagamento_list = new TCheckList('pagamento_list');
        
        $pagamento_list->addColumn('id',          'Pgto N°',          'center',  '10%');
        $pagamento_list->addColumn('pessoa->nome_fantasia', 'pessoa', 'left',    '50%');
        //$pagamento_list->addColumn('VendedorLogin',          'Vendedor',          'center',  '50%');
        //$column_ultima_fatura = $pagamento_list->addColumn('ultima_fatura', 'Última fatura', 'left',    '50%');
        $column_total = $pagamento_list->addColumn('valor',  'Valor',       'right',    '40%');
        
        $column_total->setTransformer( function($value) {
            if (is_numeric($value)) {
                return 'R$&nbsp;'.number_format($value, 2, ',', '.');
            }
            return $value;
        });

        $input_search = new TEntry('search');
        $input_search->placeholder = 'Pesq. Nome do Colaborador';
        $input_search->setSize('100%');
        $pagamento_list->enableSearch($input_search, 'pessoa->nome_fantasia');
        
        $hbox = new THBox;
        $hbox->style = 'border-bottom: 1px solid gray;padding-bottom:10px';
        $hbox->add( new TLabel('Listagens dos pagamentos a gerar') );
        $hbox->add( $input_search )->style = 'float:right;width:30%;';
        
        // load order items
        TTransaction::open('DBUNIDADE');
        $pagamentos = ContratoPagamento::where('status', '=', 'N')->load();
        $pagamento_list->addItems( $pagamentos );
        TTransaction::close();
        
        $this->form->addContent( [$hbox] );
        $this->form->addFields( [$pagamento_list] );
        
        $this->form->addHeaderAction( 'Gerar Pagamento', new TAction([$this, 'onGenerate']), 'fa:clipboard-check red');
        
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
            
            $pagamentos_ids = $data->pagamento_list;
            
            if ($pagamentos_ids)
            {
                foreach ($pagamentos_ids as $pagamento_id)
                {
                    $Pagamento = ContratoPagamento::find($pagamento_id);
                    
                    if ($Pagamento)
                    {
                        $ContaPagar = new ContaPagar;
                        $ContaPagar->dt_emissao             = date('Y-m-d');
                        $ContaPagar->dt_vencimento          = date('Y-m-d');
                        $ContaPagar->ContaFinanceiraId      = $Pagamento->ContaPagamento;
                        $ContaPagar->pessoa_id              = $Pagamento->pessoa_id;
                        $ContaPagar->contrato_id            = $Pagamento->contrato_id;
                        $ContaPagar->valor_total            = $Pagamento->valor;
                        $ContaPagar->valor                  = $Pagamento->valor;
                        $ContaPagar->ano                    = date('Y');  
                        $ContaPagar->mes                    = date('m'); 
                        $ContaPagar->ativo                  = 'Y';  
                        $ContaPagar->origem                 = 'E';
                        $ContaPagar->store();

                        $Pagamento->status = 'G';
                        $Pagamento->store();

                        // não funcional, faz requisição do metodo onReload na página (verificar em um momento posterior)
                        //$pos_action = new TAction(['GeraContaReceberList', 'onReload'], ['register_state' => 'true']);
                        //new TMessage('info', 'Conta a receber gerada(s) com sucesso!', $pos_action);
                        echo '<meta http-equiv="refresh" content="1">';
                        new TMessage('info', 'Pagamento gerado com sucesso!');
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
