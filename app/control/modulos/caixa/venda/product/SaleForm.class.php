<?php
/**
 * SaleForm Registration
 * @author  <your name here>
 */
class SaleForm extends TWindow
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        parent::setSize(0.8, null);
        parent::removePadding();
        parent::removeTitleBar();
        parent::disableEscape();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Sale');
        $this->form->setFormTitle('Venda');
        $this->form->setProperty('style', 'margin:0;border:0');
        $this->form->setClientValidation(true);
        
        // master fields
        $id          = new TEntry('id');
        $date        = new TDate('date');
        $date->setMask('dd/mm/yyyy');
        $date->setDatabaseMask('yyyy-mm-dd');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 1), TExpression::OR_OPERATOR); 
        $customer_id = new TDBUniqueSearch('customer_id', 'DBUNIDADE', 'Pessoa', 'id', 'nome_fantasia', 'grupo_id ', $criteria);
        $customer_id->setMinLength(0);
        $obs         = new TText('obs');
        
        $button = new TActionLink('', new TAction(['CustomerFormWindow', 'onEdit']), 'green', null, null, 'fa:plus-circle');
        $button->class = 'btn btn-default inline-button';
        $button->title = _t('New');
        $customer_id->after($button);
        
        // detail fields
        $product_detail_unqid      = new THidden('product_detail_uniqid');
        $product_detail_id         = new THidden('product_detail_id');
        $product_detail_product_id = new TDBUniqueSearch('product_detail_product_id', 'DBUNIDADE', 'Produto', 'id', 'description');
        $product_detail_price      = new TEntry('product_detail_price');
        //$product_detail_amount     = new TEntry('product_detail_amount');
        $product_detail_amount     = new TSpinner('product_detail_amount');
        $product_detail_discount   = new TNumeric('product_detail_discount', 2, ',', '.');
        $product_detail_total      = new TEntry('product_detail_total');

        $product_detail_amount->setRange(0, 100, 1.0);
        
        // adjust field properties
        $id->setEditable(false);
        $product_detail_price->setEditable(false);
        //$customer_id->setSize('100%');
        $customer_id->setSize('calc(100% - 30px)');
        $customer_id->setMinLength(1);
        $date->setSize('100%');
        $obs->setSize('100%', 80);
        $product_detail_product_id->setSize('100%');
        $product_detail_product_id->setMinLength(1);
        $product_detail_price->setSize('100%');
        $product_detail_amount->setSize('100%');
        $product_detail_discount->setSize('100%');
        
        // add validations
        $date->addValidation('Date', new TRequiredValidator);
        $customer_id->addValidation('Cliente', new TRequiredValidator);
        
        // change action
        $product_detail_product_id->setChangeAction(new TAction([$this,'onProductChange']));
        
        // add master form fields
        $this->form->addFields( [new TLabel('ID')], [$id], 
                                [new TLabel('Data (*)', '#FF0000')], [$date] );
        $this->form->addFields( [new TLabel('Cliente (*)', '#FF0000')], [$customer_id ] );
        $this->form->addFields( [new TLabel('Obs')], [$obs] );
        
        $this->form->addContent( ['<h4>Detalhes</h4><hr>'] );
        $this->form->addFields( [ $product_detail_unqid], [$product_detail_id] );
        $this->form->addFields( [ new TLabel('Produto (*)', '#FF0000') ], [$product_detail_product_id],
                                [ new TLabel('Quantidade (*)', '#FF0000') ],   [$product_detail_amount] );
        $this->form->addFields( [ new TLabel('Preço (*)', '#FF0000') ],   [$product_detail_price],
                                [ new TLabel('Desconto')],                [$product_detail_discount] );
        
        $add_product = TButton::create('add_product', [$this, 'onProductAdd'], 'Adicionar Produto', 'fa:plus-circle green');
        $add_product->getAction()->setParameter('static','1');
        $this->form->addFields( [], [$add_product] );
        
        $this->product_list = new BootstrapDatagridWrapper(new TDataGrid);
        $this->product_list->setHeight(150);
        $this->product_list->makeScrollable();
        $this->product_list->setId('products_list');
        $this->product_list->generateHiddenFields();
        $this->product_list->style = "min-width: 700px; width:100%;margin-bottom: 10px";
        
        $col_uniq   = new TDataGridColumn( 'uniqid', 'Uniqid', 'center', '10%');
        $col_id     = new TDataGridColumn( 'id', 'ID', 'center', '10%');
        $col_pid    = new TDataGridColumn( 'product_id', 'Prod. Nº', 'center', '10%');
        $col_descr  = new TDataGridColumn( 'product_id', 'Produto', 'left', '30%');
        $col_amount = new TDataGridColumn( 'amount', 'Qtd', 'left', '10%');
        $col_price  = new TDataGridColumn( 'sale_price', 'Preço', 'right', '15%');
        $col_disc   = new TDataGridColumn( 'discount', 'Desconto', 'center', '15%');
        $col_subt   = new TDataGridColumn( '=( {sale_price} * {amount} ) - {discount} ', 'Subtotal', 'right', '20%');
        
        $this->product_list->addColumn( $col_uniq );
        $this->product_list->addColumn( $col_id );
        $this->product_list->addColumn( $col_pid );
        $this->product_list->addColumn( $col_descr );
        $this->product_list->addColumn( $col_amount );
        $this->product_list->addColumn( $col_price );
        $this->product_list->addColumn( $col_disc );
        $this->product_list->addColumn( $col_subt );
        
        $col_descr->setTransformer(function($value) {
            return Produto::findInTransaction('DBUNIDADE', $value)->description;
        });
        
        $col_subt->enableTotal('sum', 'R$', 2, ',', '.');
        
        $col_id->setVisibility(false);
        $col_uniq->setVisibility(false);
        
        // creates two datagrid actions
        $action1 = new TDataGridAction([$this, 'onEditItemProduto'] );
        $action1->setFields( ['uniqid', '*'] );
        
        $action2 = new TDataGridAction([$this, 'onDeleteItem']);
        $action2->setField('uniqid');
        
        // add the actions to the datagrid
        $this->product_list->addAction($action1, 'Editar Produto', 'far:edit blue');
        $this->product_list->addAction($action2, 'Deletar Produto', 'far:trash-alt red');
        
        $this->product_list->createModel();
        
        $panel = new TPanelGroup;
        $panel->add($this->product_list);
        $panel->getBody()->style = 'overflow-x:auto';
        $this->form->addContent( [$panel] );
        
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, '.', ',');
            }
            return $value;
        };
        
        $col_price->setTransformer( $format_value );
        $col_disc->setTransformer( $format_value );
        $col_subt->setTransformer( $format_value );
        
        $this->form->addHeaderActionLink( 'Fechar',  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        $this->form->addAction( 'Salvar Venda',  new TAction([$this, 'onSave'], ['static'=>'1']), 'fa:save green');
        $this->form->addAction( 'Limpar Venda', new TAction([$this, 'onClear']), 'fa:eraser red');
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    /**
     * Pre load some data
     */
    public function onLoad($param)
    {
        $data = new stdClass;
        $data->customer_id   = $param['customer_id'];
        $this->form->setData($data);
    }
    
    
    /**
     * On product change
     */
    public static function onProductChange( $params )
    {
        if( !empty($params['product_detail_product_id']) )
        {
            try
            {
                TTransaction::open('DBUNIDADE');
                $product   = new Produto($params['product_detail_product_id']); #
                TForm::sendData('form_Sale', (object) ['product_detail_price' => $product->sale_price, 'product_detail_amount' => 1]);
                TTransaction::close();
            }
            catch (Exception $e)
            {
                new TMessage('error', $e->getMessage());
                TTransaction::rollback();
            }
        }
    }
    
    
    /**
     * Clear form
     * @param $param URL parameters
     */
    function onClear($param)
    {
        $this->form->clear();
    }
    
    /**
     * Add a product into item list
     * @param $param URL parameters
     */
    public function onProductAdd( $param )
    {
        try
        {
            $this->form->validate();
            $data = $this->form->getData();
           
            if( (! $data->product_detail_product_id) || (! $data->product_detail_amount) || (! $data->product_detail_price) )
            {
                throw new Exception('Insira todas as informações do produto.');
            }
            
            $uniqid = !empty($data->product_detail_uniqid) ? $data->product_detail_uniqid : uniqid();
            
            $grid_data = ['uniqid'      => $uniqid,
                          'id'          => $data->product_detail_id,
                          'product_id'  => $data->product_detail_product_id,
                          'amount'      => $data->product_detail_amount,
                          'sale_price'  => $data->product_detail_price,
                          'discount'    => $data->product_detail_discount];
            
            // insert row dynamically
            $row = $this->product_list->addItem( (object) $grid_data );
            $row->id = $uniqid;
            
            TDataGrid::replaceRowById('products_list', $uniqid, $row);
            
            // clear product form fields after add
            $data->date = TDateTime::convertToMask($param['date'], 'yyyy-mm-dd', 'yyyy-mm-dd');
            $data->product_detail_uniqid     = '';
            $data->product_detail_id         = '';
            $data->product_detail_product_id = '';
            $data->product_detail_name       = '';
            $data->product_detail_amount     = '';
            $data->product_detail_price      = '';
            $data->product_detail_discount   = '';
            
            // send data, do not fire change/exit events
            TForm::sendData( 'form_Sale', $data, false, false );
        }
        catch (Exception $e)
        {
            $this->form->setData( $this->form->getData());
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Edit a product from item list
     * @param $param URL parameters
     */
    public static function onEditItemProduto( $param )
    {
        $data = new stdClass;
        $data->product_detail_uniqid     = $param['uniqid'];
        $data->product_detail_id         = $param['id'];
        $data->product_detail_product_id = $param['product_id'];
        $data->product_detail_amount     = $param['amount'];
        $data->product_detail_price      = $param['sale_price'];
        $data->product_detail_discount   = $param['discount'];
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Sale', $data, false, false );
    }
    
    /**
     * Delete a product from item list
     * @param $param URL parameters
     */
    public static function onDeleteItem( $param )
    {
        $data = new stdClass;
        $data->product_detail_uniqid     = '';
        $data->product_detail_id         = '';
        $data->product_detail_product_id = '';
        $data->product_detail_amount     = '';
        $data->product_detail_price      = '';
        $data->product_detail_discount   = '';
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Sale', $data, false, false );
        
        // remove row
        TDataGrid::removeRowById('products_list', $param['uniqid']);
    }
    
    /**
     * Edit Sale
     */
    public function onEdit($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new Venda($key);
                $sale_items = VendaItem::where('sale_id', '=', $object->id)->load();

                TScript::create(' $("select[name=\'pessoa_id\'").prop("disabled", true); ');
                TDate::disableField('form_Sale', 'date');
                
                foreach( $sale_items as $item )
                {
                    $item->uniqid = uniqid();
                    $row = $this->product_list->addItem( $item );
                    $row->id = $item->uniqid;
                }

                $this->form->setData($object);
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Save the sale and the sale items
     */
    public function onSave($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            //////////////////////////////////////////////////////////////////
            $parametroRotinaCaixa = Parametros::find(1);
            //////////////////////////////////////////////////////////////////
            if( $parametroRotinaCaixa->valor == 1)
            {
                $pega_caixa = Caixa::last();

                if($pega_caixa->aberto == 'N'){
                    throw new Exception('<span style="font-weight: bold;">CAIXA FECHADO PARA MOVIMENTAÇÕES!</span>');
                }
            }
            //////////////////////////////////////////////////////////////////
            
            $data = $this->form->getData();
            $this->form->validate();
            
            //echo '<pre>';
            //print_r($param);
            //echo '<pre>';

            if($pega_caixa->situacao == 'P' || $pega_caixa->situacao == 'F' || $pega_caixa->situacao == 'C')
            {
                throw new Exception('<span style="font-weight: bold;">O CAIXA NÃO ESTÁ ABERTO PARA OPERAÇÕES<BR><SMALL>Realize a abetura novamente do caixa.</SMALL></span>');
            }
            else
            {
                $sale = new Venda;
                $sale->fromArray((array) $data);

                if (empty($sale->id))
                {
                    $sale->ativo = 'Y';
                    $sale->financeiro_gerado = 'N';
                    $sale->date = TDateTime::convertToMask($param['date'], 'dd/mm/yyyy', 'yyyy-mm-dd');
                    $sale->mes = TDateTime::convertToMask($param['date'], 'dd/mm/yyyy', 'mm');
                    $sale->ano = TDateTime::convertToMask($param['date'], 'dd/mm/yyyy', 'yyyy');
                    $sale->store();
                }

                /////////////////////////////////////////////////////////////////////////////////
                $pesq_venda = VendaItem::where('sale_id', '=', $sale->id)->load();
                if($pesq_venda)
                {
                    foreach ($pesq_venda as $venda_item)
                    {
                        Produto::EstoqueAdicionar($venda_item->product_id, $venda_item->amount);
                    }
                }
                /////////////////////////////////////////////////////////////////////////////////

                CaixaMov::where('venda_id', '=', $sale->id)->delete();
                VendaItem::where('sale_id', '=', $sale->id)->delete(); 
                
                $total = 0;
                if( !empty($param['products_list_product_id'] ))
                {
                    foreach( $param['products_list_product_id'] as $key => $item_id )
                    {
                        $item = new VendaItem;
                        $item->product_id  = $item_id;
                        $item->sale_price  = (float) $param['products_list_sale_price'][$key];
                        $item->amount      = (float) $param['products_list_amount'][$key];
                        $item->discount    = (float) $param['products_list_discount'][$key];
                        $item->total       = ( $item->sale_price * $item->amount ) - $item->discount;
                        
                        $item->sale_id = $sale->id;
                        $item->store();
                        $total += $item->total;

                        /////////////////////////////////////////////////////////////////////////////////
                        Produto::EstoqueRemover($item->product_id, $item->amount);
                        /////////////////////////////////////////////////////////////////////////////////
                    }

                    $ValorTotalVenda = VendaItem::where('sale_id', '=', $sale->id)->sumBy('total');
                    $ValorTotalDesconto = VendaItem::where('sale_id', '=', $sale->id)->sumBy('discount');

                    $ValorTotalMovimentacao = ($ValorTotalVenda + $ValorTotalDesconto);


                    //////////////////////////////////////////////////////////////////
                    if( $parametroRotinaCaixa->valor == 1)
                    {
                        // momvimentação no caixa
                        $movimentar_caixa = new CaixaMov;
                        $movimentar_caixa->caixa_id = $pega_caixa->id;
                        $movimentar_caixa->venda_id = $sale->id;
                        $movimentar_caixa->mov_valor = $ValorTotalVenda;
                        $movimentar_caixa->mov_desconto = $ValorTotalDesconto;
                        $movimentar_caixa->mov_tipo = 'E';
                        $movimentar_caixa->mov_data = $sale->date;
                        $movimentar_caixa->mov_total = $ValorTotalMovimentacao;
                        $movimentar_caixa->store();
                        
                        $sale->origem = 'C';
                    }
                    else
                    {
                        $sale->origem = 'V';
                    }
                    //////////////////////////////////////////////////////////////////
                }
            
                $sale->caixa = $pega_caixa->id;
                $sale->total = $total;

                if($sale->total == 0 || empty($sale->total))
                {
                    throw new Exception('<span style="font-weight: bold;">Cadastre pelo menos 1 produto para gerar uma venda</span>');
                }
                else
                {
                    $sale->store(); // stores the object
                    TForm::sendData('form_Sale', (object) ['id' => $sale->id]);
                    $pos_action = new TAction(['SaleList', 'onReload']);
                    new TMessage('info', 'Venda Registrada!', $pos_action);
                }
                
                TTransaction::close(); // close the transaction
            }  
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback();
        }
    }
    
    /**
     * Closes window
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
}
