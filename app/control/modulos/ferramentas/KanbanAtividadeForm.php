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
class KanbanAtividadeForm extends TPage
{
    protected $form; // form
    
    // trait with onSave, onClear, onEdit
    use Adianti\Base\AdiantiStandardFormTrait;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct()
    {
        parent::__construct();
        parent::setTargetContainer("adianti_right_panel");
        
        $this->setDatabase(TSession::getValue('unit_database'));    // defines the database
        $this->setActiveRecord('Atividade');   // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Atividade');
        $this->form->setFormTitle('Atividade');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        // create the form fields
        $id       = new THidden('id');
        $nome     = new TEntry('nome');
        $conteudo = new THtmlEditor('conteudo');
        $cor      = new TColor('cor');
        $ordem    = new THidden('ordem');
        $fase_id  = new THidden('fase_id');
        
        $id->setEditable(FALSE);
        $nome->setSize('100%');
        $cor->setSize('100%');
        $conteudo->setSize('100%', 250);
        
        // add the form fields
        $this->form->addFields( [$id] );
        $this->form->addFields( [new TLabel('Nome',null,null, 'b')], [$nome], [new TLabel('Cor', null, null, 'b')], [$cor] );
        $this->form->addFields( [$conteudo] );
        $this->form->addFields( [$ordem] );
        $this->form->addFields( [$fase_id] );
        
        // define the form action
        $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        $this->setAfterSaveAction( new TAction( ['KanbanView', 'onLoad'] ) );
        $this->setUseMessages(FALSE);
        
        TScript::create('$("body").trigger("click")');
        TScript::create('$("[name=nome]").focus()');
        
        parent::add($this->form);
    }
    
    /**
     *
     */
    public function onStartEdit($param)
    {
        $data = new stdClass;
        $data->fase_id = $param['id'];
        $data->ordem = 999;
        $this->form->setData($data);
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
