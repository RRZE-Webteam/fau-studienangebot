<?php

/**
 * Nutzung des Studiengaenge-Shortcode:
 * [studiengaenge name-der-taxonomy="titelform1,titleform2,..."]
 *
 * Beispiele:
 * [studiengaenge]
 * [studiengaenge saattribut="weiterbildungsstudiengang"]
 * [studiengaenge saattribut="weiterbildungsstudiengang" studienort="nuernberg"]
 * [studiengaenge saattribut="weiterbildungsstudiengang" studienort="nuernberg,erlangen"]

 * Erlaubte Taxonomien:
 * studiengang
 * semester
 * abschluss
 * faechergruppe
 * fakultaet
 * studienort
 * saattribut
 */

new FAU_Studiengaenge_Shortcode();

class FAU_Studiengaenge_Shortcode {

    const prefix = '_';

    private static $textdomain;

    private static $permalink_structure;

    private static $the_permalink;

    private static $base_permalink;

    private static $url_path;

    private static $post_type;

    private $taxonomies;

    private $taxs;

    private $request_query;

    public function __construct() {
        add_shortcode('studiengaenge', array($this, 'shortcode'));
    }

    public function shortcode($atts) {

        if (!class_exists('FAU_Studienangebot')) {
            return __('<p class="notice-attention">Das Plugin FAU-Studienangebot wurde nicht aktiviert. Bitte aktivieren Sie dieses Plugin, wenn Sie das Studiengaenge-Shortcode verwenden möchten.</p>');
        }


        self::$textdomain = FAU_Studienangebot::textdomain;

        $this->taxonomies = FAU_Studienangebot::$taxonomies;

	add_action('wp_footer', array('FAU_Studienangebot', 'print_script'));


        self::$permalink_structure = get_option('permalink_structure');
        self::$url_path = parse_url("//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_PATH);
        self::$the_permalink = empty(self::$permalink_structure) ? get_permalink() : site_url(self::$url_path);
        self::$base_permalink = site_url(basename(get_permalink()));

        self::$post_type = 'studienangebot';

        $default = array();
        foreach($this->taxonomies as $taxonomy) {
            $default[$taxonomy] = '';
        }

        $atts = shortcode_atts($default, $atts);

        $this->taxs = array();
        foreach($this->taxonomies as $taxonomy) {
            if(!empty($atts[$taxonomy])) {
                $this->taxs[$taxonomy] = array_map('trim', explode(',', $atts[$taxonomy]));
            }
        }

        $request_query = array();
        foreach($this->taxs as $key => $tax) {
            foreach($tax as $value) {
                $request_query[] = self::prefix . $key . '='. $value;
            }
        }
        $prefix = '?';
        $suffix = !empty($request_query) ? '&' : '';
        $this->request_query = $prefix . implode('&', $request_query) . $suffix;

        ob_start();
        ?>
        <div class="row">
            <div class="span9">
                <div id="studienangebot-result">
                    <?php
                    if(get_query_var('studiengang')) {
                        $this->studiengang();
                    }

                    else {
                        $this->search();
                    }
                    ?>
                </div>
            </div>
        </div>

        <div id="loading">
            <div id="loading-background"></div>
            <div id="loading-spinner"></div>
        </div>

        <?php
        return ob_get_clean();
    }

    private function studiengang() {
        $args = array(
            'name' => get_query_var('studiengang'),
            'post_type' => self::$post_type,
            'post_status' => 'publish',
            'posts_per_page' => 1
        );

        $posts = get_posts($args);

        if(isset($posts[0])) {
            $post = $posts[0];
        }

        if (isset($post)) {
            printf('<h3>%s</h3>', $post->post_title);
            echo FAU_Studienangebot::the_output($post->ID);
        }

        else {
            echo '<p class="notice-attention">' . __('Es konnte nichts gefunden werden.', self::$textdomain) . '</p>';
        }

    }

    private function search() {
        add_filter('posts_orderby', array($this, 'posts_orderby'), 10, 2);

        $categories = array();
        $tax_query = array();

        foreach($this->taxs as $key => $tax) {
            foreach($tax as $value) {
                $term = get_term_by('slug', $value, $key);
                if($term) {
                    $categories[$key][] = $term->term_id;
                }

                else {
                    $categories[$key][] = 0;
                }

            }

        }

        if (!empty($categories)) {

            $tax_query['relation'] = 'AND';

            foreach ($categories as $key => $value) {

                $tax_query[] = array(
                    'taxonomy' => $key,
                    'terms' => $value
                );

            }

        }

        $args = array(
            'nopaging' => true,
            'post_type' => self::$post_type,
            'tax_query' => $tax_query
        );

        $the_query = new WP_Query($args);

        if ($the_query->have_posts()) {

            $order = ('desc' == strtolower(get_query_var('order')) && get_query_var('orderby')) ? 'asc' : 'desc';
            $th_links = array(
                'studiengang' => self::$the_permalink . $this->request_query . 'orderby=studiengang&order=' . $order,
                'abschluss' => self::$the_permalink . $this->request_query . 'orderby=abschluss&order=' . $order,
                'semester' => self::$the_permalink . $this->request_query . 'orderby=semester&order=' . $order,
                'studienort' => self::$the_permalink . $this->request_query . 'orderby=studienort&order=' . $order,
                'mitnc' => self::$the_permalink . $this->request_query . 'orderby=sazvs&order=' . $order
            );

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th><a href="' . $th_links['studiengang'] . '">' . __('Studiengang') . '</a></th>';
            echo '<th><a href="' . $th_links['abschluss'] . '">' . __('Abschluss') . '</a></th>';
            echo '<th><a href="' . $th_links['semester'] . '">' . __('Studienbeginn') . '</a></th>';
            echo '<th><a href="' . $th_links['studienort'] . '">' . __('Ort') . '</a></th>';
            echo '<th><a href="' . $th_links['mitnc'] . '">' . __('NC für Studienanfänger') . '</a></th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($the_query->have_posts()) {

                $the_query->the_post();
                $post = get_post();
                $terms = wp_get_object_terms($post->ID, $this->taxonomies);
                $studiengang = array();
                $abschluss = array();
                $semester = array();
                $studienort = array();
                $mitnc = array();

                foreach ($terms as $term) {

                    $term_link = self::$the_permalink . (empty(self::$permalink_structure) ? '&studiengang=' . $post->post_name : 'studiengang/' . $post->post_name);
                    $studiengang = '<a href="' . $term_link . '">' . $post->post_title . '</a>';

                    if ($term->taxonomy == 'abschluss') {
                        $abschluss[] = $term->name;
                    }

                    elseif ($term->taxonomy == 'semester') {
                        $semester[] = $term->name;
                    }

                    elseif ($term->taxonomy == 'studienort') {
                        $studienort[] = $term->name;
                    }

                    elseif ($term->taxonomy == 'sazvs') {
                        if(strpos(strrev($term->slug), 'cn-') === 0) {
                            $mitnc[] = $term->name;
                        }
                    }

                }

                $abschluss = isset($abschluss) ? implode(', ', $abschluss) : '';
                $semester = isset($semester) ? implode(', ', $semester) : '';
                $studienort = isset($studienort) ? implode(', ', $studienort) : '';
                $mitnc = isset($mitnc) ? implode(', ', $mitnc) : '';

                echo '<tr>';
                echo '<td>' . $studiengang . '</td>',
                '<td>' . $abschluss . '</td>',
                '<td>' . $semester . '</td>',
                '<td>' . $studienort . '</td>',
                '<td>' . $mitnc . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        }

        else {
            echo '<p>' . __('Es konnte nichts gefunden werden.') . '</p>';
        }

        wp_reset_postdata();
    }

    public function posts_orderby($orderby, $wp_query) {
        global $wpdb;

        $taxonomy = get_query_var('orderby') ? strtolower(get_query_var('orderby')) : 'studiengang';

        $orderby = "(
            SELECT GROUP_CONCAT(name ORDER BY name ASC)
            FROM $wpdb->term_relationships
            INNER JOIN $wpdb->term_taxonomy USING (term_taxonomy_id)
            INNER JOIN $wpdb->terms USING (term_id)
            WHERE $wpdb->posts.ID = object_id
            AND taxonomy = '{$taxonomy}'
            GROUP BY object_id
        ) ";

        $orderby .= ('DESC' == strtoupper(get_query_var('order')) && get_query_var('orderby')) ? 'DESC' : 'ASC';

        return $orderby;
    }

}
