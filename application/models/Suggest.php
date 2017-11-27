<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 * note  汽车定制
 */
class SuggestModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_suggest';
    }


}