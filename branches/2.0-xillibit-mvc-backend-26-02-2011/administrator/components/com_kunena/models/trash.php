<?php
/**
 * @version $Id$
 * Kunena Component
 * @package Kunena
 *
 * @Copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

jimport ( 'joomla.application.component.model' );
kimport('kunena.model');
kimport('kunena.forum.topic.helper');
kimport('kunena.forum.message.helper');

/**
 * Trash Model for Kunena
 *
 * @package		Kunena
 * @subpackage	com_kunena
 * @since		1.6
 */
class KunenaAdminModelTrash extends KunenaModel {
	protected $__state_set = false;
	protected $_items = false;
	protected $_items_order = false;
	protected $_object = false;

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState() {
		$app = JFactory::getApplication ();

		// List state information
		$value = $this->getUserStateFromRequest ( "com_kunena.trash.list.limit", 'limit', $app->getCfg ( 'list_limit' ), 'int' );
		$this->setState ( 'list.limit', $value );

		$value = $this->getUserStateFromRequest ( 'com_kunena.trash.list.ordering', 'filter_order', 'ordering', 'cmd' );
		$this->setState ( 'list.ordering', $value );

		$value = $this->getUserStateFromRequest ( "com_kunena.trash.list.start", 'limitstart', 0, 'int' );
		$this->setState ( 'list.start', $value );

		$value = $this->getUserStateFromRequest ( 'com_kunena.trash.list.direction', 'filter_order_Dir', 'asc', 'word' );
		if ($value != 'asc')
			$value = 'desc';
		$this->setState ( 'list.direction', $value );

		$value = $this->getUserStateFromRequest ( 'com_kunena.trash.list.search', 'search', '', 'string' );
		$this->setState ( 'list.search', $value );

		$value = $this->getUserStateFromRequest ( "com_kunena.trash.list.levels", 'levellimit', 10, 'int' );
		$this->setState ( 'list.levels', $value );
	}

	/**
	 * Method to get all deleted messages.
	 *
	 * @return	Array
	 * @since	1.6
	 */
	 public function getMessagesItems() {
	 	kimport('kunena.error');
		$kunena_db = JFactory::getDBO ();

		$orderby = '';
		if ( $this->getState('list.ordering') && $this->getState('list.direction') )	$orderby = ' ORDER BY '. $this->getState('list.ordering') .' '. $this->getState('list.direction');

		$where 	= ' WHERE hold=2 ';
		$query = 'SELECT a.*, b.name AS cats_name, c.username FROM #__kunena_messages AS a
		INNER JOIN #__kunena_categories AS b ON a.catid=b.id
		LEFT JOIN #__users AS c ON a.userid=c.id'
		.$where
		.$orderby;

		$kunena_db->setQuery ( $query );
		$messages = $kunena_db->loadObjectList ();
		if (KunenaError::checkDatabaseError()) return;

		return $messages;
	}

	/**
	 * Method to get all deleted topics.
	 *
	 * @return	Array
	 * @since	1.6
	 */
	 public function getTopicsItems() {
		return array();
	 }

	/**
	 * Method to get details on selected items.
	 *
	 * @return	Array
	 * @since	1.6
	 */
	public function getPurgeItems() {
		kimport('kunena.error');

		$app = JFactory::getApplication ();

		$ids = $app->getUserState ( 'com_kunena.purge' );
		$topic = $app->getUserState('com_kunena.topic');
		$message = $app->getUserState('com_kunena.message');

		$ids = implode ( ',', $ids );

		if ( $topic ) {
			$items = KunenaForumTopicHelper::getTopics($ids);
		} elseif ( $message ) {
			$items = KunenaForumMessageHelper::getMessages($ids);
		} else {

		}

		return $items;
	}

	/**
	 * Method to hash datas.
	 *
	 * @return	hash
	 * @since	1.6
	 */
	public function getMd5() {
		$app = JFactory::getApplication ();
		$ids = $app->getUserState ( 'com_kunena.purge' );

		return md5(serialize($ids));
	}

	public function getNavigation() {
		jimport ( 'joomla.html.pagination' );
		$navigation = new JPagination ($this->getState ( 'list.total'), $this->getState ( 'list.start'), $this->getState ( 'list.limit') );
		return $navigation;
	}
}
