<?php

namespace Common\Model;

use Common\Model\CommonModel;

class Article2Model extends CommonModel
{
    protected $trueTableName = 'article_2';
    
    protected $connection = array(
        'db_type'  => 'mysqli',
        'db_user'  => 'icner',
        'db_pwd'   => 'Eovhd^#9Edd139E',
        'db_host'  => '10.0.0.188',
        'db_port'  => '3306',
        'db_name'  => 'test',
        'db_charset' => 'utf8mb4',
    );

}

?>