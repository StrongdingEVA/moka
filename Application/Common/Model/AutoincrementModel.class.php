<?php
namespace Common\Model;

/**
 * 自增主键模型
 */
class AutoincrementModel {
    	
	public function getAutoincrementId($table){
	    return D('Autoincrement'.ucfirst($table))->add(array('table' => ''), array(), true);
	}
	
}