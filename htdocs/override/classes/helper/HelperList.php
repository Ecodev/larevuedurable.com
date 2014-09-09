<?php

class HelperList extends HelperListCore
{

    /** @var array Number of results in list per page (used in select field) */
    protected $_pagination = array(1000, 300, 100, 50, 20);
}