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
class ProducaoForm extends TWindow
{
    protected $form; // form
    protected $fieldlist;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct($param);
        parent::setSize(0.8, null);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Producao');
        $this->form->setFormTitle('Produção');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        // master fields
        $id = new TEntry('id');
        $cliente_id = new TDBUniqueSearch('cliente_id', TSession::getValue('unit_database'), 'Empresa', 'id', 'nome_fantasia');
        $dt_fatura = new TDate('dt_fatura');

        // sizes
        $id->setSize('100%');
        $cliente_id->setSize('100%');
        $dt_fatura->setSize('100%');
        
        $cliente_id->addValidation('Cliente', new TRequiredValidator);
        $dt_fatura->addValidation('Dt Produção', new TRequiredValidator);

        $id->setEditable(FALSE);
        $cliente_id->setMinLength(0);
        
        $dt_fatura->setMask('dd/mm/yyyy');
        $dt_fatura->setDatabaseMask('yyyy-mm-dd');
        
        // add form fields to the form
        $this->form->addFields( [new TLabel('Id')], [$id] );
        $this->form->addFields( [new TLabel('Cliente')], [$cliente_id] );
        $this->form->addFields( [new TLabel('Dt Produção')], [$dt_fatura] );
        
        
        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist-> width = '100%';
        $this->fieldlist->enableSorting();
        
        $servico_id = new TDBUniqueSearch('list_servico_id[]', TSession::getValue('unit_database'), 'Produto', 'id', 'nome', null, TCriteria::create( ['ativo' => 'Y'] ));
        $servico_id->setChangeAction(new TAction(array($this, 'onChangeServico')));

        $valor = new TNumeric('list_valor[]', 2, ',', '.');
        $valor->setEditable(FALSE);

        $quantidade = new TNumeric('list_quantidade[]', 2, ',', '.');
        
        $servico_id->setSize('100%');
        $valor->setSize('100%');
        $quantidade->setSize('100%');
        $servico_id->setMinLength(0);

        $this->fieldlist->addField( '<b>Produtos</b>', $servico_id, ['width' => '40%']);
        $this->fieldlist->addField( '<b>Valores</b>', $valor, ['width' => '30%']);
        $this->fieldlist->addField( '<b>Quantidade</b>', $quantidade, ['width' => '30%']);

        $this->form->addField($servico_id);
        $this->form->addField($valor);
        $this->form->addField($quantidade);
        
        $detail_wrapper = new TElement('div');
        $detail_wrapper->add($this->fieldlist);
        $detail_wrapper->style = 'overflow-x:auto';
        
        $this->form->addContent( [ TElement::tag('h5', 'Itens da Produção', [ 'style'=>'background: whitesmoke; padding: 5px; border-radius: 5px; margin-top: 5px'] ) ] );
        $this->form->addContent( [ $detail_wrapper ] );
        
        // create actions
        $this->form->addAction( _t('Save'),  new TAction( [$this, 'onSave'] ),  'fa:save green' );
        $this->form->addAction( _t('Clear'), new TAction( [$this, 'onClear'] ), 'fa:eraser red' );

        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:window-close red');
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    /**
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new Producao($key);
                $this->form->setData($object);
                
                $items  = ProducaoItem::where('fatura_id', '=', $key)->load();
                
                if ($items)
                {
                    $this->fieldlist->addHeader();
                    foreach($items  as $item )
                    {
                        $detail = new stdClass;
                        $detail->list_servico_id = $item->servico_id;
                        $detail->list_valor = $item->valor;
                        $detail->list_quantidade = $item->quantidade;
                        $this->fieldlist->addDetail($detail);
                    }

                    //$this->fieldlist->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
                }
                
                TTransaction::close(); // close transaction  

                TTransaction::close(); // close transaction  

                $SuporteLogin = substr(TSession::getValue('login'), 0, 4);
                if( $SuporteLogin == 'sic.' )
                {
                    
                }
                else
                {
                    TDate::disableField('form_Producao', 'dt_fatura');
                    TScript::create(' $("select[name=\'cliente_id\'").prop("disabled", true); ');
                    TButton::disableField('form_Producao', 'btn_salvar'); 
                    TButton::disableField('form_Producao', 'btn_limpar');
                }
                
                
            }
            else
            {
                $this->onClear($param);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * Change servico
     */
    public static function onChangeServico($param)
    {
        $input_id = $param['_field_id'];
        $servico_id = $param['_field_value'];
        $input_pieces = explode('_', $input_id);
        $unique_id = end($input_pieces);
        
        if ($servico_id)
        {
            $response = new stdClass;
            
            try
            {
                TTransaction::open(TSession::getValue('unit_database'));
                
                $produto = Produto::find($servico_id);
                $response->{'list_quantidade_'.$unique_id} = '1,00';
                $response->{'list_valor_'.$unique_id} = number_format($produto->custo,2,',', '.');
                
                TForm::sendData('form_Producao', $response);
                TTransaction::close();
            }
            catch (Exception $e)
            {
                TTransaction::rollback();
            }
        }
    }

    /**
     * Save the Producaoa and the ProducaoItem's
     */
    public static function onSave($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            
            $id = (int) $param['id'];
            $master = new Producao;
            $master->fromArray( $param);
            $master->dt_fatura = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            $master->mes = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'mm');
            $master->ano = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'yyyy');
            
            if (empty($master->id))
            {
                $master->ativo = 'Y';
                $master->financeiro_gerado = 'N';
            }
            
            $master->store(); // save master object
            
            // delete details
            ProducaoItem::where('fatura_id', '=', $master->id)->delete();
            
            if( !empty($param['list_servico_id']) AND is_array($param['list_servico_id']) )
            {
                $total = 0;
                foreach( $param['list_servico_id'] as $row => $servico_id)
                {
                    if (!empty($servico_id))
                    {
                        $detail = new ProducaoItem;
                        $detail->fatura_id = $master->id;
                        $detail->servico_id = $param['list_servico_id'][$row];
                        $detail->valor =      (float) str_replace(['.',','], ['','.'], $param['list_valor'][$row]);
                        $detail->quantidade = (float) str_replace(['.',','], ['','.'], $param['list_quantidade'][$row]);
                        $detail->total = round($detail->valor * $detail->quantidade, 2);

                        $qnt_produto = explode(".", $detail->quantidade);
                        if(!empty($qnt_produto[1]))
                        {
                            if($qnt_produto[1] > 0)
                            {
                                $qnt_produto[0] += 1;
                            }
                                
                        }

                        $produto = new Produto($param['list_servico_id'][$row]);
                            
                        $estoque_atual = $produto->estoque;
                        //echo 'Estoque Atual: '.$estoque_atual.'<br>';

                        if(!$qnt_produto[0] == 0)
                        {
                            //echo 'Saida Produto: '.$qnt_produto[0].'<br>';
                            $estoque_novo = $estoque_atual + $qnt_produto[0];
                            //echo 'Novo Estoque (>0): '.$estoque_novo;
                        }
                        else{
                            //echo 'Saida Produto: '.$qnt_produto[0].'<br>';
                            $estoque_novo = $estoque_atual + 1;
                            //echo 'Novo Estoque (=0): '.$estoque_novo;
                        }

                        $produto->estoque = $estoque_novo;
                        $produto->store();

                        $detail->store();
                        
                        $total += $detail->total;
                    }
                }

                $master->total = $total;
                $master->store(); // save master object
            }
            
            $data = new stdClass;
            $data->id = $master->id;
            TForm::sendData('form_Producao', $data);
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction(['ProducaoList', 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
            parent::closeWindow();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->fieldlist->addHeader();
        $this->fieldlist->addDetail( new stdClass );
        $this->fieldlist->addCloneAction();
    }
    
    /**
     * Close
     */
    public static function onClose($param)
    {
        parent::closeWindow();
    }   
}
