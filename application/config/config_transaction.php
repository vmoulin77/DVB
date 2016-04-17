<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Actions without global transaction
|--------------------------------------------------------------------------
|
| It's a two-dimensional array whose format is :
|     - the key of the first level is the name of the controller class
|     - the value associated to each of these keys is an array of action names
|
| The keys corresponding to the controllers and the associated arrays are sorted in ascending order
|
| Exemple :
| $config['actions_without_global_trans'] = array(
|     'Home_controller' => array(
|         'index',
|     ),
|     'Invoice_controller' => array(
|         'create',
|         'edit',
|     ),
| );
*/
$config['actions_without_global_trans'] = array();
