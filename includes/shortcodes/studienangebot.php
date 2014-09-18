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
    
    private $request_query;
        
    public function __construct() {
        add_shortcode('studienangebot', array($this, 'shortcode'));
    }
    
    public function shortcode($atts) {

        if (!class_exists('FAU_Studienangebot')) {
            return 'Das Plugin FAU-Studienangebot wurde nicht aktiviert. Bitte aktivieren Sie dieses Plugin, wenn Sie das Studienangebot-Shortcode verwenden möchten.';
        }

        self::$textdomain = FAU_Studienangebot::textdomain;
                
        $this->taxonomies = FAU_Studienangebot::$taxonomies;
        
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
        
        $auswahl = !empty($auswahl) ? sprintf('<p class="studienangebot-auswahl"><b>%1$s</b> %2$s</p>', __('Sie haben ausgewählt:', self::$textdomain), implode(' + ', $auswahl)) : '';
                
        $prefix = '?';
        $suffix = !empty($request_query) ? '&' : '';
        $this->request_query = $prefix . implode('&', $request_query) . $suffix;

        ob_start();
        ?>
        <div class="row">
            <?php $this->form(); ?>
            <div class="span9">
                <div id="studienangebot-result">
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
            $post_id = $post->ID;

            $terms = wp_get_object_terms($post_id, $this->taxonomies);

            $faechergruppe = array();
            $fakultaet = array();
            $abschluss = array();
            $semester = array();
            $studienort = array();

            foreach ($terms as $term) {

                $term_link = sprintf('%1$s/?%2$s%3$s[]=%4$s', self::$base_permalink, self::prefix, $term->taxonomy, $term->slug);
                ${$term->taxonomy}[] = '<a href="' . $term_link . '">' . $term->name . '</a>';
                
            }

            $faechergruppe = isset($faechergruppe) ? implode(', ', $faechergruppe) : '';
            $fakultaet = isset($fakultaet) ? implode(', ', $fakultaet) : '';
            $abschluss = isset($abschluss) ? implode(', ', $abschluss) : '';
            $semester = isset($semester) ? implode(', ', $semester) : '';
            $studienort = isset($studienort) ? implode(', ', $studienort) : '';

            $regelstudienzeit = get_post_meta($post_id, 'sa_regelstudienzeit', true);
            $studiengang_info = get_post_meta($post_id, 'sa_studiengang_info', true);
            $kombination_info = get_post_meta($post_id, 'sa_kombination_info', true);
            $kombination_info = trim($kombination_info);
            $kombination_info = !empty($kombination_info) ? $kombination_info : '-';
            
            $zvs_anfaenger = array();
            $zvs_hoeheres_semester = array();
            $zvs_terms = wp_get_object_terms($post_id, 'sazvs');
            if(!empty($zvs_terms)) {
                if(!is_wp_error($zvs_terms )) {
                    foreach($zvs_terms as $term) {
                        $t_id = $term->term_id;
                        $meta = get_option("sazvs_category_$t_id");                       
                        if($meta && !empty($meta['linkurl'])) {
                            $sp = sprintf('<a href="%2$s">%1$s</a>', $meta['linktext'], $meta['linkurl']);
                        } elseif($meta) {
                            $sp = $meta['linktext'];
                        }
                        if(strpos($term->slug, 'studienanfaenger') === 0) {
                            $zvs_anfaenger[] = $sp;
                        } elseif(strpos($term->slug, 'hoeheres-semester') === 0) {
                            $zvs_hoeheres_semester[] = $sp;
                        }
                    }
                }
            }
            $zvs_anfaenger = !empty($zvs_anfaenger) ? implode(', ', $zvs_anfaenger) : '-';
            $zvs_hoeheres_semester = !empty($zvs_hoeheres_semester) ? implode(', ', $zvs_hoeheres_semester) : '-';
            
            $zvs_weiteres = get_post_meta($post_id, 'sa_zvs_weiteres', true);
            $zvs_weiteres = trim($zvs_weiteres);
            $zvs_weiteres = !empty($zvs_weiteres) ? $zvs_weiteres : '-';
            
            $schwerpunkte = get_post_meta($post_id, 'sa_schwerpunkte', true);
            $sprachkenntnisse = get_post_meta($post_id, 'sa_sprachkenntnisse', true);

            $deutschkenntnisse = get_post_meta($post_id, 'sa_de_kenntnisse_info', true);
            $pruefungsamt = get_post_meta($post_id, 'sa_pruefungsamt_info', true);
            $pruefungsordnung = get_post_meta($post_id, 'sa_pruefungsordnung_info', true);

            $besondere_hinweise = get_post_meta($post_id, 'sa_besondere_hinweise', true);
            $besondere_hinweise = trim($besondere_hinweise);
            $besondere_hinweise = !empty($besondere_hinweise) ? $besondere_hinweise : '-';

            $fach = get_post_meta($post_id, 'sa_fach_info', true);
            
            $sb_allgemein_info = get_post_meta($post_id, 'sa_sb_allgemein_info', true);
            $ssc = get_post_meta($post_id, 'sa_ssc_info', true);
            $gebuehren = get_post_meta($post_id, 'sa_gebuehren', true);
            $bewerbung = get_post_meta($post_id, 'sa_bewerbung', true);
            $studiengangskoordination = get_post_meta($post_id, 'sa_studiengangskoordination', true);
            
            $einfuehrung = get_post_meta($post_id, 'sa_einfuehrung_info', true);
            
            $constant_terms = wp_get_object_terms($post_id, 'saconstant');

            $attribut_terms = wp_get_object_terms($post_id, 'saattribut');
            
            echo '<h3>' . esc_html($post->post_title) . '</h3>';

            echo '<table>';
            echo '<tbody>';

            echo '<tr><td>' . __('Fächergruppe', self::$textdomain) . '</td><td>' . $faechergruppe . '</td></tr>';
            echo '<tr><td>' . __('Fakultät', self::$textdomain) . '</td><td>' . $fakultaet . '</td></tr>';
            echo '<tr><td>' . __('Abschluss', self::$textdomain) . '</td><td>' . $abschluss . '</td></tr>';
            echo '<tr><td>' . __('Regelstudienzeit', self::$textdomain) . '</td><td>' . $regelstudienzeit . '</td></tr>';
            echo '<tr><td>' . __('Studienbeginn', self::$textdomain) . '</td><td>' . $semester . '</td></tr>';
            echo '<tr><td>' . __('Studienort', self::$textdomain) . '</td><td>' . $studienort . '</td></tr>';
            echo '<tr><td>' . __('Kurzinformationen zum Studiengang', self::$textdomain) . '</td><td>' . $studiengang_info . '</td></tr>';
                        
            if(!isset($attribut_terms[0]->slug) || $attribut_terms[0]->slug != 'weiterbildungsstudiengang') {
                echo '<tr><td colspan="2">' . __('Zugangsvoraussetzungen', self::$textdomain) . '</td></tr>';
                echo '<tr><td style="padding-left: 2em">' . __('für Studienanfänger', self::$textdomain) . '</td><td>' . $zvs_anfaenger . '</td></tr>';
                echo '<tr><td style="padding-left: 2em">' . __('höheres Semester', self::$textdomain) . '</td><td>' . $zvs_hoeheres_semester . '</td></tr>';
                echo '<tr><td style="padding-left: 2em">' . __('weitere Voraussetzungen', self::$textdomain) . '</td><td>' . $zvs_weiteres . '</td></tr>';
                
                echo '<tr><td>' . __('Kombination', self::$textdomain) . '</td><td>' . $kombination_info . '</td></tr>';
                echo '<tr><td>' . __('Studienrichtungen/ -schwerpunkte/ -inhalte', self::$textdomain) . '</td><td>' . $schwerpunkte . '</td></tr>';
                echo '<tr><td>' . __('Sprachkenntnisse', self::$textdomain) . '</td><td>' . $sprachkenntnisse . '</td></tr>';
                echo '<tr><td>' . __('Deutschkenntnisse für ausländische Studierende', self::$textdomain) . '</td><td>' . $deutschkenntnisse . '</td></tr>';
                echo '<tr><td>' . __('Studien-und Prüfungsordnung mit Studienplan', self::$textdomain) . '</td><td>' . $pruefungsordnung . '</td></tr>';
                echo '<tr><td>' . __('Prüfungsamt/Prüfungsbeauftragte', self::$textdomain) . '</td><td>' . $pruefungsamt . '</td></tr>';
                echo '<tr><td>' . __('Besondere Hinweise', self::$textdomain) . '</td><td>' . $besondere_hinweise . '</td></tr>';
                echo '<tr><td>' . __('Link zum Fach', self::$textdomain) . '</td><td>' . $fach . '</td></tr>';

                echo '<tr><td colspan="2">' . __('Studienberatung', self::$textdomain) . '</td></tr>';
                echo '<tr><td style="padding-left: 2em">' . __('Studienberatung allgemein', self::$textdomain) . '</td><td>' . $sb_allgemein_info . '</td></tr>';
                echo '<tr><td style="padding-left: 2em">' . __('Studien-Service-Center', self::$textdomain) . '</td><td>' . $ssc . '</td></tr>';

                echo '<tr><td>' . __('Einführungsveranstaltungen für Studienanfänger /Vorkurse', self::$textdomain) . '</td><td>' . $einfuehrung . '</td></tr>';

                if(!empty($constant_terms)) {
                    if(!is_wp_error($constant_terms )) {
                        foreach($constant_terms as $term) {
                            $t_id = $term->term_id;
                            $name = $term->name;
                            $meta = get_option("saconstant_category_$t_id");                       
                            if($meta && !empty($meta['linkurl'])) {
                                printf('<tr><td>%1$s</td><td><a href="%3$s">%2$s</a></td></tr>', $term->name, $meta['linktext'], $meta['linkurl']);
                            } elseif($meta) {
                                printf('<tr><tr><td>%1$s</td><td>%2$s</td></tr>', $term->name, $meta['linktext']);
                            } 
                        }
                    }
                }
            
            } else {
                echo '<tr><td>' . __('Voraussetzungen', self::$textdomain) . '</td><td>' . $zvs_weiteres . '</td></tr>';
                
                echo '<tr><td>' . __('Bewerbung', self::$textdomain) . '</td><td>' . $bewerbung . '</td></tr>';
                
                echo '<tr><td>' . __('Studienrichtungen/ -schwerpunkte/ -inhalte', self::$textdomain) . '</td><td>' . $schwerpunkte . '</td></tr>';
                echo '<tr><td>' . __('Sprachkenntnisse', self::$textdomain) . '</td><td>' . $sprachkenntnisse . '</td></tr>';                
                echo '<tr><td>' . __('Studien-und Prüfungsordnung mit Studienplan', self::$textdomain) . '</td><td>' . $pruefungsordnung . '</td></tr>';
                echo '<tr><td>' . __('Prüfungsamt/Prüfungsbeauftragte', self::$textdomain) . '</td><td>' . $pruefungsamt . '</td></tr>';
                echo '<tr><td>' . __('Besondere Hinweise', self::$textdomain) . '</td><td>' . $besondere_hinweise . '</td></tr>';
                echo '<tr><td>' . __('Link zum Fach', self::$textdomain) . '</td><td>' . $fach . '</td></tr>';

                echo '<tr><td>' . __('Studienberatung allgemein', self::$textdomain) . '</td><td>' . $sb_allgemein_info . '</td></tr>';
                echo '<tr><td>' . __('Studienfachberatung/Studienkoordination', self::$textdomain) . '</td><td>' . $studiengangskoordination . '</td></tr>';
                echo '<tr><td>' . __('Studiengebühren und Studentenwerksbeiträge', self::$textdomain) . '</td><td>' . $gebuehren . '</td></tr>';

            }
            echo '</tbody>';
            echo '</table>';
        } 

        else {
            echo '<p>' . __('Es konnte nichts gefunden werden.', self::$textdomain) . '</p>';
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
                'studienort' => self::$the_permalink . $this->request_query . 'orderby=studienort&order=' . $order
            );

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th><a href="' . $th_links['studiengang'] . '">' . __('Studiengang') . '</a></th>', '<th><a href="' . $th_links['abschluss'] . '">' . __('Abschluss') . '</a></th>', '<th><a href="' . $th_links['semester'] . '">' . __('Studienbeginn') . '</a></th>', '<th><a href="' . $th_links['studienort'] . '">' . __('Ort') . '</a></th>';
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
                }

                $abschluss = isset($abschluss) ? implode(', ', $abschluss) : '';
                $semester = isset($semester) ? implode(', ', $semester) : '';
                $studienort = isset($studienort) ? implode(', ', $studienort) : '';

                echo '<tr>';
                echo '<td>' . $studiengang . '</td>',
                '<td>' . $abschluss . '</td>',
                '<td>' . $semester . '</td>',
                '<td>' . $studienort . '</td>';
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
        <div class="span3">
            <style>
                #studienangebot label { 
                    float: none !important; 
                    display: inline !important;
                } 
                #studienangebot br { 
                    display: none;
                }
            </style>
            <form id="studienangebot" action="<?php the_permalink(); ?>" method="get">
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
