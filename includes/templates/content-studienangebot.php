<?php
echo '<div class="studiengang">';
echo '<a href="#"><h4>' . __('Auf einen Blick', self::textdomain) . '</h4></a>';
echo '<dl class="studiengang-list" id="auf-einen-blick">';
//echo '<dt>' . __('Fächergruppe', self::textdomain) . '</dt><dd>' . $faechergruppe . '</dd>';
echo '<dt>' . __('Fakultät', self::textdomain) . '</dt><dd>' . $fakultaet . '</dd>';
echo '<dt>' . __('Abschluss', self::textdomain) . '</dt><dd>' . $abschluss . '</dd>';
echo '<dt>' . __('Regelstudienzeit', self::textdomain) . '</dt><dd>' . $regelstudienzeit . '</dd>';
echo '<dt>' . __('Studienbeginn', self::textdomain) . '</dt><dd>' . $semester . '</dd>';
echo '<dt>' . __('Studienort', self::textdomain) . '</dt><dd>' . $studienort . '</dd>';
echo '<dt>' . __('Kurzinformationen zum Studiengang', self::textdomain) . '</dt><dd>' . $studiengang_info . '</dd>';
echo '<dt>' . __('Studiengangsgebühren', self::textdomain) . '</dt><dd>' . $sa_gebuehren . '</dd>';
echo '<dt>' . __('Semesterbeitrag', self::textdomain) . '</dt><dd>' . $gebuehren . '</dd>';
echo '<dt>' . __('Link zum Studiengang', self::textdomain) . '</dt><dd>' . $fach . '</dd>';
echo '</dl>';
if (!isset($attribut_terms[0]->slug) || $attribut_terms[0]->slug != 'weiterbildungsstudiengang') {
    echo '<a href="#"><h4>' . __('Aufbau und Struktur', self::textdomain) . '</h4></a>';
    echo '<dl class="studiengang-list" id="aufbau-und-struktur">';
    echo '<dt>' . __('Studieninhalte', self::textdomain) . '</dt><dd>' . $schwerpunkte . '</dd>';
    echo '<dt>' . __('Besondere Hinweise', self::textdomain) . '</dt><dd>' . $besondere_hinweise . '</dd>';

    if (!empty($kombination_info)) :
        echo '<dt>' . __('Kombinationsmöglichkeiten', self::textdomain) . '</dt><dd>' . $kombination_info . '</dd>';
    endif;

    echo '</dl>';
    echo '<a href="#"><h4>' . __('Zugangsvoraussetzungen, Bewerbung und Einschreibung', self::textdomain) . '</h4></a>';
    echo '<dl class="studiengang-list" id="zugangsvoraussetzungen">';
    echo '<dt>' . __('für Studienanfänger', self::textdomain) . '</dt><dd>' . $zvs_anfaenger . '</dd>';
    echo '<dt>' . __('höheres Semester', self::textdomain) . '</dt><dd>' . $zvs_hoeheres_semester . '</dd>';
    echo '<dt>' . __('Details', self::textdomain) . '</dt><dd>' . $zvs_weiteres . '</dd>';
    echo '<dt>' . __('Sprachkenntnisse', self::textdomain) . '</dt><dd>' . $sprachkenntnisse . '</dd>';
    echo '<dt>' . __('Deutschkenntnisse für ausländische Studierende', self::textdomain) . '</dt><dd>' . $deutschkenntnisse . '</dd>';
    echo '<dt>' . __('Termine', self::textdomain) . '</dt><dd>' . $termine . '</dd>';
    echo '</dl>';
    echo '<a href="#"><h4>' . __('Organisation', self::textdomain) . '</h4></a>';
    echo '<dl class="studiengang-list" id="organisation">';
    echo '<dt>' . __('Studienbeginn', self::textdomain) . '</dt><dd>' . $einfuehrung . '</dd>';
    echo '<dt>' . __('Prüfungsangelegenheiten', self::textdomain) . '</dt><dd>' . $pruefung . '</dd>';
    echo '<dt>' . __('Link zum Studiengang', self::textdomain) . '</dt><dd>' . $fach . '</dd>';
    echo '<dt>' . __('Studienberatung', self::textdomain) . '</dt><dd>' . $studienberatung . '</dd>';
    echo '<dt>' . __('Studentenvertretung/ Fachschaft', self::textdomain) . '</dt><dd>' . $studentenvertretung . '</dd>';
    echo '<dt>' . __('Berufliche Möglichkeiten', self::textdomain) . '</dt><dd>' . $beruflich . '</dd>';
    echo '</dl>';
} else {
    echo '<a href="#"><h4>' . __('Voraussetzungen und Bewerbung', self::textdomain) . '</h4></a>';
    echo '<dl class="studiengang-list" id="voraussetzungen-und-bewerbung">';
    echo '<dt>' . __('Zugangsvoraussetzungen', self::textdomain) . '</dt><dd>' . $zvs_weiteres . '</dd>';
    echo '<dt>' . __('Sprachkenntnisse', self::textdomain) . '</dt><dd>' . $sprachkenntnisse . '</dd>';
    echo '<dt>' . __('Bewerbungsverfahren', self::textdomain) . '</dt><dd>' . $bewerbung . '</dd>';
    echo '</dl>';
    echo '<a href="#"><h4>' . __('Aufbau und Struktur', self::textdomain) . '</h4></a>';
    echo '<dl class="studiengang-list" id="aufbau-und-struktur">';
    echo '<dt>' . __('Studieninhalte', self::textdomain) . '</td><td>' . $schwerpunkte . '</dd>';
    echo '<dt>' . __('Besondere Hinweise', self::textdomain) . '</dt><dd>' . $besondere_hinweise . '</dd>';
    echo '</dl>';
    echo '<a href="#"><h4>' . __('Organisation', self::textdomain) . '</h4></a>';
    echo '<dl class="studiengang-list" id="organisation">';
    echo '<dt>' . __('Prüfungsangelegenheiten', self::textdomain) . '</dt><dd>' . $pruefung . '</dd>';
    //echo '<dt>' . __('Link zum Studiengang', self::textdomain) . '</dt><dd>' . $fach . '</dd>';
    echo '<dt>' . __('Studiengangskoordination', self::textdomain) . '</dt><dd>' . $studiengangskoordination . '</dd>';
    echo '</dl>';
}
echo '</div>';
