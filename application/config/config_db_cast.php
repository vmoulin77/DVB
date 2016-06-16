<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['db_cast_data'] = array(
    'campaign' => array(
        'id'          => 'int',
        'name'        => 'string',
        'created_at'  => 'datetime',
    ),
    'campaign_card' => array(
        'id_campaign'  => 'int',
        'id_card'      => 'int',
        'is_done'      => 'bool',
        'review_date'  => 'datetime',
    ),
    'card' => array(
        'id'                       => 'int',
        'num'                      => 'int',
        'is_deleted'               => 'bool',
        'id_version_when_deleted'  => 'int',
    ),
    'card_content' => array(
        'id'                 => 'int',
        'id_card'            => 'int',
        'word_english'       => 'string',
        'word_french'        => 'string',
        'is_active_english'  => 'bool',
        'is_active_french'   => 'bool',
        'id_version'         => 'int',
        'is_last'            => 'bool',
    ),
    'card_deck_version' => array(
        'id_card'      => 'int',
        'id_deck'      => 'int',
        'id_version'   => 'int',
        'type'         => 'string',
        'is_last'      => 'bool',
    ),
    'deck' => array(
        'id'                       => 'int',
        'num'                      => 'int',
        'name'                     => 'string',
        'id_version_when_created'  => 'int',
    ),
    'version' => array(
        'id'                => 'int',
        'database_version'  => 'int',
        'app_version_code'  => 'int',
        'app_version_name'  => 'string',
        'created_at'        => 'datetime',
    ),
);
