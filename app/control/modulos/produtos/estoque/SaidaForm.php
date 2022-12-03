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
class SaidaForm extends TWindow
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
        $this->form = new BootstrapFormBuilder('form_Saida');
        $this->form->setFormTitle('Saída Estoque');
        $this->form->setClientValidation(true);
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO

        $SuporteLogin = substr(TSession::getValue('login'), 0, 3);
        
        // master fields
        $id = new TEntry('id');
        
        $criteria = new TCriteria; # exemplo 1
        $criteria->add(new TFilter('id', '>', 0), TExpression::OR_OPERATOR); 
        $cliente_id = new TDBUniqueSearch('cliente_id', TSession::getValue('unit_database'), 'Pessoa', 'id', 'nome_fantasia', null, $criteria );

        $dt_fatura = new TDate('dt_fatura');

        // sizes
        $id->setSize('100%');
        $cliente_id->setSize('100%');
        $dt_fatura->setSize('100%');
        
        $cliente_id->addValidation('Cliente', new TRequiredValidator);
        $dt_fatura->addValidation('Dt Saída', new TRequiredValidator);

        $id->setEditable(FALSE);
        $cliente_id->setMinLength(0);
        
        $dt_fatura->setMask('dd/mm/yyyy');
        $dt_fatura->setDatabaseMask('yyyy-mm-dd');
        
        // add form fields to the form
        $this->form->addFields( [new TLabel('Id')], [$id] );
        $this->form->addFields( [new TLabel('Cliente')], [$cliente_id] );
        $this->form->addFields( [new TLabel('Dt Saída')], [$dt_fatura] );
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist-> width = '100%';
        $this->fieldlist->enableSorting();
        
        $servico_id = new TDBUniqueSearch('list_servico_id[]', TSession::getValue('unit_database'), 'Produto', 'id', 'nome' );
        $servico_id->setChangeAction(new TAction(array($this, 'onChangeServico')));

        $valor = new TNumeric('list_valor[]', 2, ',', '.');
        //$valor->setEditable(FALSE);

        $quantidade = new TNumeric('list_quantidade[]', 2, ',', '.');
        
        $servico_id->setSize('100%');
        $valor->setSize('100%');
        $quantidade->setSize('100%');
        $servico_id->setMinLength(0);

        $this->fieldlist->addField( '<b>Produto</b>', $servico_id, ['width' => '40%']);
        $this->fieldlist->addField( '<b>Valor</b>', $valor, ['width' => '30%']);
        $this->fieldlist->addField( '<b>Quantidade</b>', $quantidade, ['width' => '30%']);

        $this->form->addField($servico_id);
        $this->form->addField($valor);
        $this->form->addField($quantidade);
        
        $detail_wrapper = new TElement('div');
        $detail_wrapper->add($this->fieldlist);
        $detail_wrapper->style = 'overflow-x:auto';
        
        $this->form->addContent( [ TElement::tag('h5', 'Itens da fatura', [ 'style'=>'background: whitesmoke; padding: 5px; border-radius: 5px; margin-top: 5px'] ) ] );
        $this->form->addContent( [ $detail_wrapper ] );

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $SuporteLogin = substr(TSession::getValue('login'), 0, 4);
        if( $SuporteLogin == 'sic.' )
        {
            // create actions
            $this->form->addAction( 'Salvar (admin)',  new TAction( [$this, 'onSave'] ),  'fa:save green' );
            $this->form->addAction( 'Limpar (admin)', new TAction( [$this, 'onClear'] ), 'fa:eraser red' );    
        }
        else
        {
            // create actions
            $this->form->addAction( 'Salvar',  new TAction( [$this, 'onSave'] ),  'fa:save green' );
            $this->form->addAction( 'Limpar', new TAction( [$this, 'onClear'] ), 'fa:eraser red' );
        }

        
        
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:window-close red');
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }

    public static function onVazio($param)
    {

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
                //$register_state = $param['register_state'];
                
                $object = new Saida($key);
                $this->form->setData($object);
                
                $items  = SaidaItem::where('fatura_id', '=', $key)->load();
                
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

                    $this->fieldlist->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
                }
                
                TTransaction::close(); // close transaction  

                $SuporteLogin = substr(TSession::getValue('login'), 0, 4);
                if( $SuporteLogin == 'sic.' )
                {
                    
                }
                else
                {
                    TDate::disableField('form_Saida', 'dt_fatura');
                    TScript::create(' $("select[name=\'cliente_id\'").prop("disabled", true); ');
                    TButton::disableField('form_Saida', 'btn_salvar'); 
                    TButton::disableField('form_Saida', 'btn_limpar');
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
                
                $servico = Produto::find($servico_id);
                $response->{'list_quantidade_'.$unique_id} = '1,00';
                $response->{'list_valor_'.$unique_id} = number_format($servico->valor,2,',', '.');
                
                TForm::sendData('form_Saida', $response);
                TTransaction::close();
            }
            catch (Exception $e)
            {
                TTransaction::rollback();
            }
        }
    }

    /**
     * Save the Saida and the SaidaItem's
     */
    public static function onSave($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            
            $id = (int) $param['id'];
            $Venda = new Saida;
            $Venda->fromArray( $param);
            $Venda->dt_fatura = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            $Venda->mes = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'mm');
            $Venda->ano = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'yyyy');
            
            if (empty($Venda->id))
            {
                $Venda->ativo = 'Y';
                $Venda->financeiro_gerado = 'N';
            }
            
            $Venda->store(); // save Venda object
            
            // delete details
            SaidaItem::where('fatura_id', '=', $Venda->id)->delete();
            
            if( !empty($param['list_servico_id']) AND is_array($param['list_servico_id']) )
            {
                $total = 0;
                foreach( $param['list_servico_id'] as $row => $servico_id)
                {
                    if(!empty($param['list_servico_id'][0]))
                    {
                        
                        //////////////////////////////////////////////////////////////////
                        $parametroRotinaCaixa = Parametros::find(1);
                        //////////////////////////////////////////////////////////////////
                        //////////////////////////////////////////////////////////////////
                        if( $parametroRotinaCaixa->valor == 1)
                        {
                            $pega_caixa = Caixa::last();

                            if($pega_caixa->aberto == 'N'){
                                throw new Exception('<span style="font-weight: bold;">CAiXA FECHADO PARA MOVIMENTAÇÕES!</span>');
                            }
                        }
                        //////////////////////////////////////////////////////////////////

                        $detail = new SaidaItem;
                        $detail->fatura_id = $Venda->id;
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
                            $estoque_novo = $estoque_atual - $qnt_produto[0];
                            //echo 'Novo Estoque (>0): '.$estoque_novo;
                        }
                        else{
                            //echo 'Saida Produto: '.$qnt_produto[0].'<br>';
                            $estoque_novo = $estoque_atual - 1;
                            //echo 'Novo Estoque (=0): '.$estoque_novo;
                        }
                        
                        $produto->estoque = $estoque_novo;

                        $produto->store();
                        $detail->store();

                        
                        //////////////////////////////////////////////////////////////////
                        //////////////////////////////////////////////////////////////////
                        if( $parametroRotinaCaixa->valor == 1)
                        {
                            // momvimentação no caixa
                            $movimentar_caixa = new CaixaMov;
                            $movimentar_caixa->caixa_id = $pega_caixa->id;
                            $movimentar_caixa->venda_id = $Venda->id;
                            $movimentar_caixa->mov_valor = $detail->valor;
                            $movimentar_caixa->mov_desconto = 0;
                            $movimentar_caixa->mov_tipo = 'P';
                            $movimentar_caixa->mov_data = $Venda->dt_fatura;
                            $movimentar_caixa->mov_total = $detail->total;
                            $movimentar_caixa->store();

                            // passa id caixa no Venda
                            $Venda->caixa = $pega_caixa->id;

                            $Venda->origem = 'C';
                        }
                        else
                        {
                            $Venda->origem = 'V';
                        }
                        //////////////////////////////////////////////////////////////////
                        //////////////////////////////////////////////////////////////////
                        
                        $total += $detail->total;
                    }
                    else
                    {
                        new TMessage('info', 'erro');
                    }
                }

                $Venda->total = $total;

                //////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////
                if( $parametroRotinaCaixa->valor == 1)
                {
                    $pega_caixa = Caixa::last();

                    if( $pega_caixa->aberto == 'Y' && $pega_caixa->situacao == 'A' )
                    {
                        if( $Venda->dt_fatura == date('Y-m-d') )
                        {
                            $Venda->store(); // save Venda object
                        }
                        else
                        {
                            throw new Exception('<span style="font-weight: bold;">NÃO É PERMITIDO MOVIMENTAÇÕES<BR>COM DATAS FUTURAS</span>');
                        }
                    }
                    else
                    {
                        throw new Exception('<span style="font-weight: bold;">O CAIXA NÃO ESTÁ ABERTO</span>');
                    }
                }
                else
                {
                    $Venda->store(); // save Venda object
                }
                //////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////
                
                $data = new stdClass;
                $data->id = $Venda->id;
                TForm::sendData('form_Saida', $data);
                TTransaction::close(); // close the transaction
                
                $pos_action = new TAction(['SaidaList', 'onReload']);
                new TMessage('info', 'Saída Registrada!', $pos_action);
                parent::closeWindow();
            }

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
        TScript::create(' $("select[name=\'cliente_id\'").prop("disabled", false); ');
    }   
}
