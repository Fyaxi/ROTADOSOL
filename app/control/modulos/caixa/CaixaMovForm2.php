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
class CaixaMovForm2 extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['CaixaList', 'onReload'], ['register_state' => 'true']) );
        
        $this->setDatabase(TSession::getValue('unit_database'));              // defines the database
        $this->setActiveRecord('CaixaMov');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_CaixaMov');
        $this->form->setFormTitle('Movimentação');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO

        // create the form fields
        $id = new TEntry('id');
        $caixa_id = new TDBUniqueSearch('caixa_id', TSession::getValue('unit_database'), 'Caixa', 'id', 'id');
        $venda_id = new TDBUniqueSearch('venda_id', TSession::getValue('unit_database'), 'Venda', 'id', 'id');
        $mov_valor = new TNumeric('mov_valor', 2, ',', '.', true);
        $mov_desconto = new TNumeric('mov_desconto', 2, ',', '.', true);
        $mov_data = new TDate('mov_data');
        $mov_baixa = new TDate('mov_baixa');
        $mov_total = new TNumeric('mov_total', 2, ',', '.', true);
        

        // add the fields
        $this->form->addFields( [ new TLabel('Mov. n°:') ], [ $id ], [ new TLabel('Caixa nº') ], [ $caixa_id ] );
        $this->form->addFields( [ new TLabel('Venda nº') ], [ $venda_id ], [ new TLabel('Valor Total') ], [ $mov_total ] );
        $this->form->addFields( [ new TLabel('Lançamento') ], [ $mov_data ], [ new TLabel('Data Baixa') ], [ $mov_baixa ] );
        $this->form->addFields( [ new TLabel('Valor') ], [ $mov_valor ], [ new TLabel('Desconto') ], [ $mov_desconto ] );


        // set sizes
        $id->setSize('100%');
        $caixa_id->setSize('100%');
        $venda_id->setSize('100%');
        $mov_valor->setSize('100%');
        $mov_desconto->setSize('100%');
        $mov_data->setSize('100%');
        $mov_baixa->setSize('100%');
        $mov_total->setSize('100%');

        $SuporteLogin = substr(TSession::getValue('login'), 0, 8);
        //print_r($SuporteLogin);
        if( $SuporteLogin == 'suporte.' )
        {
            $id->setEditable(true);
            $caixa_id->setEditable(true);
            $venda_id->setEditable(true);
            $mov_valor->setEditable(true);
            $mov_desconto->setEditable(true);
            $mov_data->setEditable(true);
            $mov_baixa->setEditable(true);
            $mov_total->setEditable(true);

            $mov_data->setMask('dd/mm/yyyy');
            $mov_data->setDatabaseMask('yyyy-mm-dd');

            $mov_baixa->setMask('dd/mm/yyyy');
            $mov_baixa->setDatabaseMask('yyyy-mm-dd');

            $caixa_id->setMinLength(0);
            $venda_id->setMinLength(0);
            
            // create the form actions
            $btn1 = $this->form->addAction('Salvar <b>(admin)</b>', new TAction([$this, 'onSave']), 'fa:save');
            $btn1->class = 'btn btn-sm btn-primary';
        }
        else
        {
            $id->setEditable(false);
            $caixa_id->setEditable(false);
            $venda_id->setEditable(false);
            $mov_valor->setEditable(false);
            $mov_desconto->setEditable(false);
            $mov_data->setEditable(false);
            $mov_baixa->setEditable(false);
            $mov_total->setEditable(false);

            $mov_data->setMask('dd/mm/yyyy');
            $mov_data->setDatabaseMask('yyyy-mm-dd');

            $mov_baixa->setMask('dd/mm/yyyy');
            $mov_baixa->setDatabaseMask('yyyy-mm-dd');
        }
        
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }

    public static function onSave($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            
            $object = new CaixaMov;
            $object->fromArray( $param );
            $object->mov_data = TDateTime::convertToMask($param['mov_data'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            $object->mov_baixa = TDateTime::convertToMask($param['mov_baixa'], 'dd/mm/yyyy', 'yyyy-mm-dd');

            $object->store();
            $pos_action = new TAction(['CaixaMovList', 'onReload']);
            new TMessage('info', '<span style="font-weight: bold;">Movimentação Salva!</span>', $pos_action);

            $data = new stdClass;
            $data->id = $object->id;
            TForm::sendData('form_CaixaMov', $data);
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
}
