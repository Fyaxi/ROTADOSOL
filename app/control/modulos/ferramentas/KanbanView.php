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
class KanbanView extends TPage
{
	private $kanban;
	
	public function __construct()
	{
		parent::__construct();
		
		TTransaction::open(TSession::getValue('unit_database'));
		$fases = Fase::where('projeto_id','=',TSession::getValue('projeto_id'))->orderBy('ordem')->load();
		$atividades = [];
		foreach ($fases as $fase)
		{
		    $atividades = array_merge($atividades, Atividade::where('fase_id','=',$fase->id)->load());
		}
		TTransaction::close();
		
		$this->kanban = new TKanban;
		foreach ($fases as $key => $fase)
		{
			$this->kanban->addStage($fase->id, $fase->nome, $fase);
		}
		
		foreach ($atividades as $key => $atividade)
		{
			$this->kanban->addItem($atividade->id, $atividade->fase_id, $atividade->nome, $atividade->conteudo, $atividade->cor, $atividade);
		}
		
		
		$this->kanban->addStageAction('Edita', new TAction(['KanbanFaseForm', 'onEdit']),   'far:edit blue fa-fw');
		$this->kanban->addStageAction('Exclui', new TAction([$this, 'onDeleteFase'], ['register_state' => 'false']),   'far:trash-alt red fa-fw');
		$this->kanban->addStageAction('Adiciona atividade', new TAction(['KanbanAtividadeForm', 'onStartEdit'], ['register_state' => 'false']),   'fa:plus green fa-fw');
		
		$this->kanban->addStageShortcut('Adiciona', new TAction(['KanbanAtividadeForm', 'onStartEdit'], ['register_state' => 'false']),   'fa:plus fa-fw');
		
		$this->kanban->addItemAction('Edita', new TAction(['KanbanAtividadeForm', 'onEdit'], ['register_state' => 'false']), 'far:edit bg-blue');
		$this->kanban->addItemAction('Exclui', new TAction([$this, 'onDeleteAtividade']), 'far:trash-alt bg-red');
		
		$this->kanban->setItemDropAction(new TAction([__CLASS__, 'onUpdateItemDrop']));
		$this->kanban->setStageDropAction(new TAction([__CLASS__, 'onUpdateStageDrop']));
		
		$add = new TActionLink('Fase', new TAction(array('KanbanFaseForm', 'onEdit')), 'gray', 10, null, 'fa:plus');
		$add->class = 'btn btn-success';
		$add->style = 'float:left;margin:5px';
		
		$this->kanban->style = 'float:left;width: calc(100% - 100px)';
		
		parent::add($this->kanban);
		parent::add($add);
	}
	
	/**
	 *
	 */
	public function onLoad($param)
	{
	}
	
    /**
     * Update fase on drop
     */
	public static function onUpdateStageDrop($param)
	{
		if (empty($param['order']))
		{
			return;
		}
		
		try
		{
    		TTransaction::open(TSession::getValue('unit_database'));
    		
    		foreach ($param['order'] as $key => $id)
    		{
    			$sequence = ++ $key;
    
    			$fase = new Fase($id);
    			$fase->ordem = $sequence;
    
    			$fase->store();
    		}
    		
    		TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
	}
	
    /**
     * Update item on drop
     */
	public static function onUpdateItemDrop($param)
	{
		if (empty($param['order']))
		{
			return;
		}

        try
        {
    		TTransaction::open(TSession::getValue('unit_database'));
    
    		foreach ($param['order'] as $key => $id)
    		{
    			$sequence = ++$key;
    
    			$item = new Atividade($id);
    			$item->ordem = $sequence;
    			$item->fase_id = $param['stage_id'];
    			$item->store();
    		}
    		
    		TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
	}
	
	/**
	 * 
	 */
	public static function onDeleteFase($param)
	{
        // define the delete action
        $action = new TAction(array(__CLASS__, 'DeleteFase'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
	}
	
    /**
     * method Delete()
     * Delete a record
     */
    public static function DeleteFase($param)
    {
        try
        {
            // instantiates object and delete
            TTransaction::open(TSession::getValue('unit_database'));
            $object = new Fase( $param['key'] );
            $object->delete();
            TTransaction::close();
            
            AdiantiCoreApplication::loadPage(__CLASS__, 'onLoad');
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }


	/**
	 * 
	 */
	public static function onDeleteAtividade($param)
	{
        // define the delete action
        $action = new TAction(array(__CLASS__, 'DeleteAtividade'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
	}
	
    /**
     * method Delete()
     * Delete a record
     */
    public static function DeleteAtividade($param)
    {
        try
        {
            // instantiates object and delete
            TTransaction::open(TSession::getValue('unit_database'));
            $object = new Atividade( $param['key'] );
            $object->delete();
            TTransaction::close();
            
            AdiantiCoreApplication::loadPage(__CLASS__, 'onLoad');
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Display condition
     */
	public static function teste($param = NULL)
	{
		return TRUE;
	}
}
