<?php
new FAU_Studienangebot_Shortcode();

class FAU_Studienangebot_Shortcode {

    const prefix = '_';

    private static $textdomain;

    private static $permalink_structure;

    private static $the_permalink;

    private static $base_permalink;

    private static $url_path;

    private static $post_type;

    private $taxonomies;

    private $taxs;

    private $mitnc;
    private $zulassungsfrei;

    private $request_query;

    public function __construct() {
        add_shortcode('studienangebot', array($this, 'shortcode'));
    }

    public function shortcode($atts) {

        if (!class_exists('FAU_Studienangebot')) {
            return '<p class="notice-attention">Das Plugin FAU-Studienangebot wurde nicht aktiviert. Bitte aktivieren Sie dieses Plugin, wenn Sie das Studienangebot-Shortcode verwenden möchten.</p>';
        }


        self::$textdomain = FAU_Studienangebot::textdomain;

        $this->taxonomies = FAU_Studienangebot::$taxonomies;



	add_action('wp_footer', array( 'FAU_Studienangebot' , 'print_script'));

        self::$permalink_structure = get_option('permalink_structure');
        self::$url_path = parse_url("//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_PATH);
        self::$the_permalink = empty(self::$permalink_structure) ? get_permalink() : site_url(self::$url_path);
        self::$base_permalink = site_url(basename(get_permalink()));

        self::$post_type = 'studienangebot';

        $default = array();
        $atts = shortcode_atts($default, $atts);

        $this->taxs = array();
        foreach($this->taxonomies as $taxonomy) {
            $get_tax = isset($_GET[self::prefix . $taxonomy]) ? (array) $_GET[self::prefix . $taxonomy] : array();
            if(!empty($get_tax[0])) {
                $this->taxs[$taxonomy] = array_map('trim', $get_tax);
            }
        }

        $this->mitnc = isset($_GET[self::prefix . 'mitnc']) ? 1 : 0;
        $this->zulassungsfrei = isset($_GET[self::prefix . 'zulassungsfrei']) ? 1 : 0;

        $request_query = array();
        $auswahl = array();
        foreach($this->taxs as $key => $tax) {
            foreach($tax as $value) {
                $request_query[] = self::prefix . $key . '[]='. $value;
                $term = get_term_by('slug', $value, $key);
                if($term) {
                    $auswahl_value[$key][] = $term->name;
                }
            }
            if(!empty($auswahl_value)) {
                $cat = get_taxonomy($key);
                //$auswahl[] = sprintf('%1$s: %2$s', $cat->labels->singular_name, implode(' + ', $auswahl_value[$key]));
                $auswahl[] = implode(' + ', $auswahl_value[$key]);
            }
        }

        if($this->mitnc) {
            $request_query[] = self::prefix . 'mitnc=1';
        }
        if($this->zulassungsfrei) {
            $request_query[] = self::prefix . 'zulassungsfrei=1';
        }

        $auswahl = !empty($auswahl) ? sprintf('<p class="sa-auswahl"><b>%1$s</b> %2$s</p>', __('Sie haben ausgewählt:', self::$textdomain), implode(' + ', $auswahl)) : '';

        $prefix = '?';
        $suffix = !empty($request_query) ? '&' : '';
        $this->request_query = $prefix . implode('&', $request_query) . $suffix;

        ob_start();
        ?>
	<div  class="fau-studienangebot">
	    <div class="row">
		<?php $this->form(); ?>
		<div class="col-xs-12 col-sm-9">
		    <div id="studienangebot-result" aria-live="polite">
			<?php
			if(get_query_var('studiengang')) {
			    $this->studiengang();
			}

			else {
			    echo $auswahl;
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

        $back_link = sprintf('<a href="%1$s">%2$s</a>', self::$the_permalink . $this->request_query, __('Zurück zur Liste', self::$textdomain));

        if (!empty($post)) {
            printf('<h3>%1$s <span class="sa-back-link">%2$s</span></h3>', $post->post_title, $back_link);
            echo FAU_Studienangebot::the_output($post->ID);
        }

        else {
            echo '<p class="sa-notfound">' . __('Es konnte nichts gefunden werden.', self::$textdomain) . '</p>';
            echo '<p>' . $back_link . '</p>';
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

        $terms = get_terms('sazvs');
         if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                if($this->mitnc && strpos($term->slug, 'studienanfaenger-nc') !== FALSE) {
                    $categories['sazvs'][] = $term->term_id;
                } elseif($this->zulassungsfrei && strpos($term->slug, 'studienanfaenger') === 0 && strpos($term->slug, 'zulassungsfrei') !== FALSE) {
                    $categories['sazvs'][] = $term->term_id;
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

            echo '<table class="sa-list">';
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

                    //$term_link = self::$the_permalink . (empty(self::$permalink_structure) ? $this->request_query . '&studiengang=' . $post->post_name : 'studiengang/' . $post->post_name . $this->request_query);
                    $term_link = self::$the_permalink . $this->request_query . 'studiengang=' . $post->post_name;
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
                        if(strpos($term->slug, 'studienanfaenger') === 0) {
                            $mitnc[] = $term->name;
                        }
                    }

                }

                $abschluss = isset($abschluss) ? implode(',<br>', $abschluss) : '';
                $semester = isset($semester) ? implode(',<br>', $semester) : '';
                $studienort = isset($studienort) ? implode(',<br>', $studienort) : '';
                $mitnc = isset($mitnc) ? implode(',<br>', $mitnc) : '';

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

    private function form() {
        $abschlussgruppe = FAU_Studienangebot::get_abschlussgruppe();

        $terms = get_terms('abschluss', array('pad_counts' => true, 'hide_empty' => 1));
        $abschluesse = array();
        foreach ($terms as $term) {
            $term_meta = get_option("abschluss_category_{$term->term_id}");
            if ($term_meta && !empty($abschlussgruppe[$term_meta['abschlussgruppe']]))
                $abschluesse[$term_meta['abschlussgruppe']][$term->term_id] = (object) array(
                    'term_id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                );
        }

        uksort($abschluesse, 'strnatcasecmp');

        $abschluss = array();
        foreach ($abschlussgruppe as $key => $val) {
            if (isset($abschluesse[$key])) {
                $abschluss[$key] = $abschluesse[$key];
            }
        }
        ?>
        <div class="col-xs-12 col-sm-3">

            <form id="studienangebot" action="<?php the_permalink(); ?>" method="get" role="search" aria-controls="studienangebot-result" data-filtertoggle="<?php _e('Filter ein/ausblenden', self::$textdomain); ?>">
                <h3><?php _e('Studiengang', self::$textdomain); ?></h3>
                <?php $terms = get_terms('studiengang', array('pad_counts' => true, 'hide_empty' => 1)); ?>
                <p>
                    <select name="<?php echo self::prefix; ?>studiengang[]" id="studiengang_category">
                        <option value="0"><?php _e('Alle Studiengänge', self::$textdomain); ?></option>
                        <?php foreach ($terms as $term): ?>
                            <?php $selected = in_array($term->slug, isset($this->taxs['studiengang']) ? $this->taxs['studiengang'] : array()); ?>
                            <option value="<?php echo $term->slug; ?>" <?php selected($selected); ?>><?php echo $term->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <h3><?php _e('Fächergruppe', self::$textdomain); ?></h3>
                <?php $terms = get_terms('faechergruppe', array('pad_counts' => true, 'hide_empty' => 1)); ?>
                <?php foreach ($terms as $term): ?>
                    <?php $checked = in_array($term->slug, isset($this->taxs['faechergruppe']) ? $this->taxs['faechergruppe'] : array()); ?>
                    <p>
                        <input type="checkbox" name="<?php echo self::prefix; ?>faechergruppe[]" value="<?php echo $term->slug; ?>" id="faechergruppe-<?php echo $term->term_id; ?>" <?php checked($checked); ?>>
                        <label for="faechergruppe-<?php echo $term->term_id; ?>"><?php echo $term->name; ?></label>
                    </p>
                <?php endforeach; ?>
                <h3><?php _e('Fakultät', self::$textdomain); ?></h3>
                <?php $terms = get_terms('fakultaet', array('pad_counts' => true, 'hide_empty' => 1)); ?>
                <?php foreach ($terms as $term): ?>
                    <?php $checked = in_array($term->slug, isset($this->taxs['fakultaet']) ? $this->taxs['fakultaet'] : array()); ?>
                    <p>
                        <input type="checkbox" name="<?php echo self::prefix; ?>fakultaet[]" value="<?php echo $term->slug; ?>" id="fakultaet-<?php echo $term->term_id; ?>" <?php checked($checked); ?>>
                        <label for="fakultaet-<?php echo $term->term_id; ?>"><?php echo $term->name; ?></label>
                    </p>
                <?php endforeach; ?>

                <h3><?php _e('Abschluss', self::$textdomain); ?></h3>

                <?php foreach ($abschluss as $key => $terms): ?>
                    <h4><?php echo $abschlussgruppe[$key]; ?></h4>
                    <?php foreach ($terms as $term): ?>
                        <?php $checked = in_array($term->slug, isset($this->taxs['abschluss']) ? $this->taxs['abschluss'] : array()); ?>
                        <p>
                            <input type="checkbox" name="<?php echo self::prefix; ?>abschluss[]" value="<?php echo $term->slug; ?>" id="abschluss-<?php echo $term->term_id; ?>" <?php checked($checked); ?>>
                            <label for="abschluss-<?php echo $term->term_id; ?>"><?php echo $term->name; ?></label>
                        </p>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <h3><?php _e('Studienbeginn', self::$textdomain); ?></h3>
                <?php $terms = get_terms('semester', array('pad_counts' => true, 'hide_empty' => 1)); ?>
                <?php foreach ($terms as $term): ?>
                    <?php $checked = in_array($term->slug, isset($this->taxs['semester']) ? $this->taxs['semester'] : array()); ?>
                    <p>
                        <input type="checkbox" name="<?php echo self::prefix; ?>semester[]" value="<?php echo $term->slug; ?>" id="semester-<?php echo $term->term_id; ?>" <?php checked($checked); ?>>
                        <label for="semester-<?php echo $term->term_id; ?>"><?php echo $term->name; ?></label>
                    </p>
                <?php endforeach; ?>
                <h3><?php _e('Studienort', self::$textdomain); ?></h3>
                <?php $terms = get_terms('studienort', array('pad_counts' => true, 'hide_empty' => 1)); ?>
                <?php foreach ($terms as $term): ?>
                    <?php $checked = in_array($term->slug, isset($this->taxs['studienort']) ? $this->taxs['studienort'] : array()); ?>
                    <p>
                        <input type="checkbox" name="<?php echo self::prefix; ?>studienort[]" value="<?php echo $term->slug; ?>" id="studienort-<?php echo $term->term_id; ?>" <?php checked($checked); ?>>
                        <label for="studienort-<?php echo $term->term_id; ?>"><?php echo $term->name; ?></label>
                    </p>
                <?php endforeach; ?>
               <h3><?php _e('NC für Studienanfänger', self::$textdomain); ?></h3>
                <p>
                    <input type="checkbox" name="<?php echo self::prefix; ?>mitnc" value="1" id="mitnc" <?php checked($this->mitnc); ?>>
                    <label for="mitnc"><?php _e('mit NC', self::$textdomain); ?></label>
                </p>
                <p>
                    <input type="checkbox" name="<?php echo self::prefix; ?>zulassungsfrei" value="1" id="zulassungsfrei" <?php checked($this->zulassungsfrei); ?>>
                    <label for="zulassungsfrei"><?php _e('zulassungsfrei', self::$textdomain); ?></label>
                </p>
                <h3><?php _e('Weitere Eigenschaften', self::$textdomain); ?></h3>
                <?php $terms = get_terms('saattribut', array('pad_counts' => true, 'hide_empty' => 1)); ?>
                <?php foreach ($terms as $term): ?>
                    <?php $checked = in_array($term->slug, isset($this->taxs['saattribut']) ? $this->taxs['saattribut'] : array()); ?>
                    <p>
                        <input type="checkbox" name="<?php echo self::prefix; ?>saattribut[]" value="<?php echo $term->slug; ?>" id="saattribut-<?php echo $term->term_id; ?>" <?php checked($checked); ?>>
                        <label for="saattribut-<?php echo $term->term_id; ?>"><?php echo $term->name; ?></label>
                    </p>
                <?php endforeach; ?>
                 <p>
                    <input class="submit-button" type="submit" value="<?php _e('Auswählen', self::$textdomain); ?>">
                </p>
            </form>
        </div>
        <?php
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
