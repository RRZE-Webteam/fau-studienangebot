<?php

add_filter('rrze_meta_boxes', 'studienangebot_metaboxes');

function studienangebot_metaboxes(array $meta_boxes) {

    $prefix = 'sa_';

    $meta_boxes['sa_taxonomy_metabox'] = array(
        'id' => 'sa_taxonomy',
        'title' => __('Taxonomien', SA_TEXTDOMAIN),
        'pages' => array('studienangebot'), // Post type
        'context' => 'normal',
        'priority' => 'low',
        'show_names' => true,
        'fields' => array(
            array(
                'name' => __('Studiengang', SA_TEXTDOMAIN),
                'id' => $prefix . 'studiengang_taxonomy',
                'taxonomy' => 'studiengang',
                'type' => 'taxonomy_select',
            ),
            array(
                'name' => __('Abschluss', SA_TEXTDOMAIN),
                'id' => $prefix . 'abschluss_taxonomy',
                'taxonomy' => 'abschluss',
                'type' => 'taxonomy_multicheck',
            ),
            array(
                'name' => __('Semester', SA_TEXTDOMAIN),
                'id' => $prefix . 'semester_taxonomy',
                'taxonomy' => 'semester',
                'type' => 'taxonomy_multicheck',
            ),
            array(
                'name' => __('Ort', SA_TEXTDOMAIN),
                'id' => $prefix . 'studienort_taxonomy',
                'taxonomy' => 'studienort',
                'type' => 'taxonomy_multicheck',
            ),
            array(
                'name' => __('Fächergruppe', SA_TEXTDOMAIN),
                'id' => $prefix . 'faechergruppe_taxonomy',
                'taxonomy' => 'faechergruppe',
                'type' => 'taxonomy_select',
            ),
            array(
                'name' => __('Fakultät', SA_TEXTDOMAIN),
                'id' => $prefix . 'fakultaet_taxonomy',
                'taxonomy' => 'fakultaet',
                'type' => 'taxonomy_select',
            ),
            array(
                'name' => __('Attribute', SA_TEXTDOMAIN),
                'id' => $prefix . 'saattribut_taxonomy',
                'taxonomy' => 'saattribut',
                'type' => 'taxonomy_multicheck',
            ),
        ),
    );

    $meta_boxes['sa_metadata_metabox'] = array(
        'id' => 'sa_metadata',
        'title' => __('Metadaten', SA_TEXTDOMAIN),
        'pages' => array('studienangebot'),
        'context' => 'normal',
        'priority' => 'low',
        'show_names' => true,
        'fields' => array(
            array(
                'name' => __('Regelstudienzeit', SA_TEXTDOMAIN),
                'id' => $prefix . 'regelstudienzeit',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),
            array(
                'name' => __('Studienrichtungen/ -schwerpunkte/ -inhalte', SA_TEXTDOMAIN),
                'id' => $prefix . 'schwerpunkte',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),
            array(
                'name' => __('Sprachkenntnisse', SA_TEXTDOMAIN),
                'id' => $prefix . 'sprachkenntnisse',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            array(
                'name' => __('Besondere Hinweise', SA_TEXTDOMAIN),
                'id' => $prefix . 'besondere_hinweise',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            array(
                'name' => __('Kurzinformationen zum Studiengang', SA_TEXTDOMAIN),
                'id' => $prefix . 'studiengang_info',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),
            array(
                'name' => __('Kombination', SA_TEXTDOMAIN),
                'id' => $prefix . 'kombination_info',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            array(
                'name' => __('Deutschkenntnisse für ausländische Studierende', SA_TEXTDOMAIN),
                'id' => $prefix . 'de_kenntnisse_info',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            array(
                'name' => __('Prüfungsamt/ Prüfungsbeauftragte', SA_TEXTDOMAIN),
                'id' => $prefix . 'pruefungsamt_info',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            array(
                'name' => __('Studien- und Prüfungsordnung mit Studienplan', SA_TEXTDOMAIN),
                'id' => $prefix . 'pruefungsordnung_info',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            array(
                'name' => __('Link zum Fach', SA_TEXTDOMAIN),
                'id' => $prefix . 'fach_info',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),
            array(
                'name' => __('Einführung', SA_TEXTDOMAIN),
                'id' => $prefix . 'einfuehrung_info',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            array(
                'name' => __('Englische Bezeichnung des Studiengangs', SA_TEXTDOMAIN),
                'id' => $prefix . 'englische_bezeichnung',
                'type' => 'text',
            ),
            array(
                'name' => __('Link zur englischen Webseite des Faches', SA_TEXTDOMAIN),
                'id' => $prefix . 'englische_url',
                'description' => __('Link-URL (bitte mit http:// eingeben)', SA_TEXTDOMAIN),
                'type' => 'text_url',
                'protocols' => array('http', 'https')
            ),
            array(
                'name' => __('Anzeige des Studienganges im englischen Webauftritt', SA_TEXTDOMAIN),
                'id' => $prefix . 'englisch_anzeige',
                'type' => 'checkbox'               
            ),            
            
        ),
    );
    
    $meta_boxes['sa_studienberatung_metabox'] = array(
        'id' => 'sa_studienberatung',
        'title' => __('Studienberatung', SA_TEXTDOMAIN),
        'pages' => array('studienangebot'),
        'context' => 'normal',
        'priority' => 'low',
        'show_names' => true,
        'fields' => array(
            array(
                'name' => __('Studienberatung allgemein', SA_TEXTDOMAIN),
                'id' => $prefix . 'sb_allgemein_info',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            array(
                'name' => __('Studien-Service-Center', SA_TEXTDOMAIN),
                'id' => $prefix . 'ssc_info',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            
        ),
    );
    
    $meta_boxes['sa_zvs_metabox'] = array(
        'id' => 'sa_zvs',
        'title' => __('Zugangsvoraussetzungen', SA_TEXTDOMAIN),
        'pages' => array('studienangebot'),
        'context' => 'normal',
        'priority' => 'low',
        'show_names' => true,
        'fields' => array(
            array(
                'name' => __('Voraussetzungen', SA_TEXTDOMAIN),
                'id' => $prefix . 'sazvs_taxonomy',
                'taxonomy' => 'sazvs',
                'type' => 'taxonomy_multicheck',
            ),           
            array(
                'name' => __('Weiteres', SA_TEXTDOMAIN),
                'id' => $prefix . 'zvs_weiteres',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),
            
        ),
    );
        
    $meta_boxes['sa_weiterbildung_metabox'] = array(
        'id' => 'sa_weiterbildung',
        'title' => __('Weiterbildung', SA_TEXTDOMAIN),
        'pages' => array('studienangebot'),
        'context' => 'normal',
        'priority' => 'low',
        'show_names' => true,
        'fields' => array(
            array(
                'name' => __('Studiengangsgebühren', SA_TEXTDOMAIN),
                'id' => $prefix . 'gebuehren',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            array(
                'name' => __('Bewerbungsverfahren', SA_TEXTDOMAIN),
                'id' => $prefix . 'bewerbung',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            array(
                'name' => __('Studiengangskoordination', SA_TEXTDOMAIN),
                'id' => $prefix . 'studiengangskoordination',
                'type' => 'wysiwyg',
                'options' => array(
                    'wpautop' => true,
                    'media_buttons' => true,
                    'textarea_rows' => 5,
                    'tabindex' => '',
                    'teeny' => true,
                    'quicktags' => true
                ),
            ),            
            
        ),
    );
    
    $meta_boxes['sa_constant_metabox'] = array(
        'id' => 'sa_constant',
        'title' => __('Konstanten', SA_TEXTDOMAIN),
        'pages' => array('studienangebot'),
        'context' => 'normal',
        'priority' => 'low',
        'show_names' => true,
        'fields' => array(
            array(
                'name' => __('Allgemein', SA_TEXTDOMAIN),
                'id' => $prefix . 'saconstant_taxonomy',
                'taxonomy' => 'saconstant',
                'type' => 'taxonomy_multicheck',
            ),           
            
        ),
    );
    
    return $meta_boxes;
}
