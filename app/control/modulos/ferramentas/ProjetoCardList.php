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
class ProjetoCardList extends TPage
{
    private $form;
    
    public function __construct()
    {
        parent::__construct();
        
        $cards = new TCardView;
        $cards->setUseButton();
        
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            $projetos_usuario = ProjetoUsuario::where('system_user_id','=',TSession::getValue('userid'))->getIndexedArray('projeto_id');
            $projetos = Projeto::where('id', 'IN', $projetos_usuario)->load();
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        
        foreach ($projetos as $projeto)
        {
            $cards->addItem($projeto);
        }
        
        //$cards->setTitleAttribute('nome');
        
        $cards->setItemTemplate('{nome}');
        $action   = new TAction([$this, 'onSelect'], ['id'=> '{id}']);
        $cards->addAction($action,   'Seleciona',   'fa:check blue');
        
        parent::add($cards);
    }
    
    /**
     * Item edit action
     */
    public static function onSelect($param = NULL)
    {
        TSession::setValue('projeto_id', $param['id']);
        TScript::create('$("body").addClass("ls-closed");');
        AdiantiCoreApplication::loadPage('KanbanView', 'onLoad');
    }
}
