<?php
/**
 * ContratoDashboard TSession::getValue('DBUNIDADE')
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class ServicoForm extends TPage
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
        $this->setAfterSaveAction( new TAction(['ServicoList', 'onReload'], ['register_state' => 'true']) );

        $this->setDatabase('DBUNIDADE');              // defines the database
        $this->setActiveRecord('Servico');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Servico');
        $this->form->setFormTitle('Cadastro Itens Festa');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $id                     = new TEntry('id');
        $nome                   = new TEntry('nome');
        $nome_relatorio         = new TEntry('nome_relatorio');
        $valor                  = new TNumeric('valor', 2, ',', '.', true);
        $valor_custo            = new TNumeric('valor_custo', 2, ',', '.', true);
        $tipo_servico_id        = new TDBUniqueSearch('tipo_servico_id', 'DBUNIDADE', 'TipoServico', 'id', 'nome');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 3), TExpression::OR_OPERATOR); 
        $tipo_favorecido_id = new TDBUniqueSearch('tipo_favorecido_id', 'DBUNIDADE', 'Favorecido', 'id', 'nome_fantasia', 'grupo_id ', $criteria);
        $ativo                  = new TRadioGroup('ativo');
        $quantidade             = new TEntry('quantidade');


        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new Tlabel('Nome Relatório')], [ $nome_relatorio ] );
        $this->form->addFields( [ new TLabel('Valor') ], [ $valor ] );
        $this->form->addFields( [ new TLabel('Custo') ], [ $valor_custo ] );
        $this->form->addFields( [ new TLabel('Quantidade:') ], [ $quantidade ]);
        $this->form->addFields( [ new TLabel('Tipo') ], [ $tipo_servico_id ] );
        $this->form->addFields( [ new TLabel('Fornecedor') ], [ $tipo_favorecido_id ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );

        $nome->addValidation('Nome', new TRequiredValidator);
        $valor->addValidation('Valor', new TRequiredValidator);
        $valor_custo->addValidation('Valor', new TRequiredValidator);
        $quantidade->addValidation('Quantidade', new TRequiredValidator);
        $tipo_servico_id->addValidation('Tipo de Item', new TRequiredValidator);
        $tipo_favorecido_id->addValidation('Fornecedor', new TRequiredValidator);
        $ativo->addValidation('Ativo', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $nome_relatorio->setSize('100%');
        $valor->setSize('100%');
        $valor_custo->setSize('100%');
        $quantidade->setSize('100%');
        $tipo_servico_id->setSize('100%');
        $tipo_favorecido_id->setSize('100%');
        $ativo->setSize('100%');
        $ativo->addItems( ['Y' => 'Sim', 'N' => 'Não'] );
        $ativo->setLayout('horizontal');
        $tipo_servico_id->setMinLength(0);
        $tipo_favorecido_id->setMinLength(0);
        $ativo->setValue('Y');
        
        $id->setEditable(FALSE);

        $nome->forceUpperCase();
        $nome_relatorio->forceUpperCase();
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
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
}
