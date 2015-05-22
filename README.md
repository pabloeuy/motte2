#Motte 

## What is Motte ?

Motte is a uruguayan framework for generation of web applications PHP based on the MVC model.

## Features

* MVC
* Internationalization (i18n)
* REST Managment
* CRUD
* Template system
* Support MySql
* Helpers

## Requirements

You need PHP >= 5.2.

## Installation

Download and extract Motte into your project directory.

Define global variables in your index file to use Motte, module y method default of your MVC application:

    define('MODULE_DEFAULT', 'home');
    define('METHOD_DEFAULT', 'index');
    
Paths of motte, views, models and controllers directories:

    define('DIR_ROOT', '.');
    define('DIR_MOTTE', DIR_ROOT.'/../motte2');
    define('DIR_TEMPLATES',  DIR_ROOT.'/view');
    define('DIR_MODEL',  DIR_ROOT.'/model');
    define('DIR_CONTROLLER', DIR_ROOT.'/controller');
    
Variables of DB config:

    define('DB_DRIVER', 'mySql'); // coming SQLite
    define('DB_HOST', 'localhost');
    define('DB_USER', 'user');
    define('DB_PASS', 'pass');
    define('DB_NAME', 'db_name');
    
And include Motte modules necessary in your application’s index.php file the :

    include_once(DIR_MOTTE.'/mteCtr.class.php');//for router and ajax
    include_once(DIR_MOTTE.'/mteModel.class.php');//for DB access
    include_once(DIR_MOTTE.'/mteTools.class.php');//for tools and helpers
    include_once(DIR_MOTTE.'/mteCrud.class.php');//for CRUD

## Getting Started with MVC App    

In index_example.php you can see an example of index file of a MVC Application.

** Controllers **

    class ctrName { }

Name file should be : name.controller.php
Return a view:

    $tpl =  mteCtr::get()->getTemplate('nameView.html');
    $tpl->setVar('VIEW_VARIABLE', $variable);
    return $tpl->getHtml();

Return a json:

    mteCtr::get()->getResponse()->responseSuccess('msg');

    mteCtr::get()->getResponse()->responseError('msg');

    mteCtr::get()->getResponse()->setStatusOk();
    mteCtr::get()->getResponse()->addBlock('response',$msg);


** Models **
    
    class mdlName { }

Name file should be : name.model.php

Get Table data:
    
    mteModel::get()->getTable('name_table')->getRecord(['where','order',...]);

    mteModel::get()->getTable('name_table')->getRecordSet(['fields','where','order',...]);

    mteModel::get()->getTable('name_table')->getValue(['field','where','order']);

Modify Data (Return a empty msg if success):

    mteModel::get()->getTable('name_table')->insertRecord($record_array);

    mteModel::get()->getTable('name_table')->updateRecord($record_array[,'where']);

    mteModel::get()->getTable('name_table')->updateValues($values_array[,'where']);

    mteModel::get()->getTable('name_table')->upsertRecord($record_array,'where');

Delete Data (Return a empty msg if success):

    mteModel::get()->getTable('name_table')->deleteRecord($record_array[,'where']);

    mteModel::get()->getTable('name_table')->deleteRecords('where');

Transactions:

    mteModel::get()->startTransaction();

    /*Code*/

    if(empty($error)){
        mteModel::get()->commit();
    } else {
        mteModel::get()->rollback();
    }

## Getting Started with API REST
   
Define routes:

    mteCtr::get()->getRM()->route('GET','routeForGet/:parameter');//it call a method and assign parameters to $_GET(defined by route)
    
    mteCtr::get()->getRM()->route('POST','routeForPost/:parameter');//it call a method and assign parameters to $_GET (defined by route) and $_POST (variables in the body)
    
    mteCtr::get()->getRM()->route('DELETE','routeForDelete');//it call a method and assign parameters to $_GET(defined by route)
    
And run:

    mteCtr::get()->getRM()->run();
    
To response:

    mteCtr::get()->getRM()->responseSuccess("msg");//for success, 200 HTTP Response
    
    mteCtr::get()->getRM()->responseError("msg");//for error, 406 HTTP Response
    
    

    










