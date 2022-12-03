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
class CaixaForm extends TPage
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
        
        $this->setDatabase('DBUNIDADE');              // defines the database
        $this->setActiveRecord('Caixa');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Caixa');
        $this->form->setFormTitle('Caixa');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO

        // create the form fields
        $id = new TEntry('id');
        $abertura = new TDate('abertura');
        $fechamento = new TDate('fechamento');
        $usuario = new TEntry('usuario');
        //$aberto = new TEntry('aberto');
        $valorAbertura = new TNumeric('valorAbertura', 2, ',', '.');
        

        // add the fields
        $this->form->addFields( [ new TLabel('Caixa n°:') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Abertura:') ], [ $abertura ] );
        $this->form->addFields( [ new TLabel('Fechamento:') ], [ $fechamento ] );
        $this->form->addFields( [ new TLabel('Aberto Por:') ], [ $usuario ] );
        $this->form->addFields( [ new TFormSeparator('')] );
        $this->form->addFields( [ new TLabel('Saldo Inicial:') ], [ $valorAbertura ] );
        //$this->form->addFields( [ new TLabel('aberto') ], [ $aberto ] );


        // set sizes
        $id->setSize('100%');
        $abertura->setSize('100%');
        $fechamento->setSize('100%');
        $usuario->setSize('100%');
        //$aberto->setSize('100%');
        $valorAbertura->setSize('100%');

        $id->setEditable(false);
        $abertura->setEditable(false);
        $fechamento->setEditable(false);
        $usuario->setEditable(false);
        $valorAbertura->setEditable(true);

        $abertura->setMask('dd/mm/yyyy');
        $abertura->setDatabaseMask('yyyy-mm-dd');

        $fechamento->setMask('dd/mm/yyyy');
        $fechamento->setDatabaseMask('yyyy-mm-dd');
        
        // create the form actions
        $btn1 = $this->form->addAction('Abrir Caixa', new TAction([$this, 'onSave']), 'fa:save');
        $btn1->class = 'btn btn-sm btn-primary';
        
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
            TTransaction::open('DBUNIDADE');
            
            $object = new Caixa;
            $object->fromArray( $param );
            
            if( empty($object->id) )
            {              
                $pega_caixa = Caixa::last();
                if($pega_caixa->aberto == 'Y'){
                    new TMessage('danger', '<span style="font-weight: bold;">EXISTE UM CAIXA ABERTO, REALIZE O FECHAMENTO DO CAIXA</span>');
                }else{
                    $object->abertura = date("Y-m-d"); 
                    $object->aberto = 'Y';
                    $object->usuario = TSession::getValue('login');
                    $object->valorAbertura = (float) str_replace(['.',','], ['','.'], $param['valorAbertura']);
                    $object->store();
                    $pos_action = new TAction(['CaixaList', 'onReload']);
                    new TMessage('info', '<span style="font-weight: bold;">USUÁRIO <span style="color:red;text-transform: uppercase;">'.$object->usuario.'</span> REALIZOU A ABERTURA DO CAIXA</span>', $pos_action);
                }
            }else{
                new TMessage('warning', '<span style="font-weight: bold;">NÃO É POSSÍVEL ALTERAR O CAIXA!<span>');
            }

            $data = new stdClass;
            $data->id = $object->id;
            $data->usuario = $object->usuario;
            $data->abertura = $object->abertura;
            TForm::sendData('form_Caixa', $data);
            
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
