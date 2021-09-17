<?php

namespace Sejoli_Rest_Api;

class CLI {

    /**
     * Render data
     * @param  array    $data
     * @param  string   $view
     * @param  mixed    $fields
     * @return void
     */
    protected function render($data, $view = 'table', $fields) {

        \WP_CLI\Utils\format_items(
            $view,
            $data,
            $fields
        );

    }

    /**
     * Display single or multiple messages in CLI
     * @since   1.3.0
     * @param   string|WP_Error|array   $messages
     * @param   string                  $type     Value can be success, warning or error
     * @return  void
     */
    protected function message($messages, $type = 'error') {

        if(is_array($messages)) :

            foreach($messages as $message) :
                \WP_CLI::$type($message);
            endforeach;

        else :

            \WP_CLI::$type($messages);

        endif;
    
    }

}
