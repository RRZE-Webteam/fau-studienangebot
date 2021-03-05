<?php

$shortcode_data = '';
$output = '';
$content_blick = '<dl id="auf-einen-blick">'
    //. '<dt>' . __('Fächergruppe', self::textdomain) . '</dt><dd>' . $faechergruppe . '</dd>'
    . '<dt>' . __('Fakultät', self::textdomain) . '</dt><dd>' . $fakultaet . '</dd>'
    . '<dt>' . __('Abschluss', self::textdomain) . '</dt><dd>' . $abschluss . '</dd>'
    . '<dt>' . __('Regelstudienzeit', self::textdomain) . '</dt><dd>' . $regelstudienzeit . '</dd>'
    . '<dt>' . __('Studienbeginn', self::textdomain) . '</dt><dd>' . $semester . '</dd>'
    . '<dt>' . __('Studienort', self::textdomain) . '</dt><dd>' . $studienort . '</dd>'
    . '<dt>' . __('Kurzinformationen zum Studiengang', self::textdomain) . '</dt><dd>' . $studiengang_info . '</dd>'
    . '<dt>' . __('Studiengangsgebühren', self::textdomain) . '</dt><dd>' . $sa_gebuehren . '</dd>'
    . '<dt>' . __('Semesterbeitrag', self::textdomain) . '</dt><dd>' . $gebuehren . '</dd>'
    . '<dt>' . __('Link zum Studiengang', self::textdomain) . '</dt><dd>' . $fach . '</dd>'    
    . '</dl>';
$shortcode_data .= do_shortcode('[collapse title="' . __('Auf einen Blick', self::textdomain) . '" name="auf-einen-blick" load="open"]' . $content_blick . '[/collapse]');

if(empty($attribut_terms) || !in_array('weiterbildungsstudiengang', $attribut_terms)) {

    $content_aufbau =  '<dl id="aufbau-und-struktur">'
        . '<dt>' . __('Studieninhalte', self::textdomain) . '</dt><dd>' . $schwerpunkte . '</dd>'
        . '<dt>' . __('Besondere Hinweise', self::textdomain) . '</dt><dd>' . $besondere_hinweise . '</dd>';
    if(!empty($kombination_info)) :
        $content_aufbau .= '<dt>' . __('Kombinationsmöglichkeiten', self::textdomain) . '</dt><dd>' . $kombination_info . '</dd>';
    endif;
    $content_aufbau .= '</dl>';
    $shortcode_data .= do_shortcode('[collapse title="' . __('Aufbau und Struktur', self::textdomain) . '" name="aufbau-und-struktur"]' . $content_aufbau . '[/collapse]');

    $content_zugang = '<dl id="zugangsvoraussetzungen">'
        . '<dt>' . __('für Studienanfänger', self::textdomain) . '</dt><dd>' . $zvs_anfaenger . '</dd>'
        . '<dt>' . __('höheres Semester', self::textdomain) . '</dt><dd>' . $zvs_hoeheres_semester . '</dd>'
        . '<dt>' . __('Details', self::textdomain) . '</dt><dd>' . $zvs_weiteres . '</dd>'
        . '<dt>' . __('Sprachkenntnisse', self::textdomain) . '</dt><dd>' . $sprachkenntnisse . '</dd>'
        . '<dt>' . __('Deutschkenntnisse für ausländische Studierende', self::textdomain) . '</dt><dd>' . $deutschkenntnisse . '</dd>'
        . '<dt>' . __('Termine', self::textdomain) . '</dt><dd>' . $termine . '</dd>'
        . '</dl>';
    $shortcode_data .= do_shortcode('[collapse title="' . __('Zugangsvoraussetzungen, Bewerbung und Einschreibung', self::textdomain) . '"]' . $content_zugang . '[/collapse]');

    $content_orga = '<dl id="organisation">'
        . '<dt>' . __('Studienbeginn', self::textdomain) . '</dt><dd>' . $einfuehrung . '</dd>'
        . '<dt>' . __('Prüfungsangelegenheiten', self::textdomain) . '</dt><dd>' . $pruefung . '</dd>'
        . '<dt>' . __('Link zum Studiengang', self::textdomain) . '</dt><dd>' . $fach . '</dd>'
        . '<dt>' . __('Studienberatung', self::textdomain) . '</dt><dd>' . $studienberatung . '</dd>'
        . '<dt>' . __('Studentenvertretung/ Fachschaft', self::textdomain) . '</dt><dd>' . $studentenvertretung . '</dd>'
        . '<dt>' . __('Berufliche Möglichkeiten', self::textdomain) . '</dt><dd>' . $beruflich . '</dd>'
        . '</dl>';
    $shortcode_data .= do_shortcode('[collapse title="' . __('Organisation', self::textdomain) . '" name="organisation"]' . $content_orga . '[/collapse]');
    
} else {
    $content_zugang = '<dl id="voraussetzungen-und-bewerbung">'
        . '<dt>' . __('Zugangsvoraussetzungen', self::textdomain) . '</dt><dd>' . $zvs_weiteres . '</dd>'
        . '<dt>' . __('Sprachkenntnisse', self::textdomain) . '</dt><dd>' . $sprachkenntnisse . '</dd>'
        . '<dt>' . __('Bewerbungsverfahren', self::textdomain) . '</dt><dd>' . $bewerbung . '</dd>'
        . '</dl>';
    $shortcode_data .= do_shortcode('[collapse title="' . __('Voraussetzungen und Bewerbung', self::textdomain) . '" name="voraussetzungen-und-bewerbung"]' . $content_zugang . '[/collapse]');

    $content_aufbau = '<dl id="aufbau-und-struktur">'
        . '<dt>' . __('Studieninhalte', self::textdomain) . '</dt><dd>' . $schwerpunkte . '</dd>'
        . '<dt>' . __('Besondere Hinweise', self::textdomain) . '</dt><dd>' . $besondere_hinweise . '</dd>'
        . '</dl>';
    $shortcode_data .= do_shortcode('[collapse title="' . __('Aufbau und Struktur', self::textdomain) . '" name="aufbau-und-struktur"]' . $content_aufbau . '[/collapse]');

    $content_orga = '<dl id="organisation">'
        . '<dt>' . __('Prüfungsangelegenheiten', self::textdomain) . '</dt><dd>' . $pruefung . '</dd>'
        //. '<dt>' . __('Link zum Studiengang', self::textdomain) . '</dt><dd>' . $fach . '</dd>'
        . '<dt>' . __('Studiengangskoordination', self::textdomain) . '</dt><dd>' . $studiengangskoordination . '</dd>'
        . '</dl>';
    $shortcode_data .= do_shortcode('[collapse title="' . __('Organisation', self::textdomain) . '" name="organisation"]' . $content_orga . '[/collapse]');
}


$output .= do_shortcode('[collapsibles]' . $shortcode_data . '[/collapsibles]');

echo $output;
