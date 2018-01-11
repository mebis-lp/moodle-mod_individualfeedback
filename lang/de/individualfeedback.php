<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'individualfeedback', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package mod_individualfeedback
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['add_item'] = 'Frage hinzuf�gen';
$string['add_pagebreak'] = 'Seitenumbruch hinzuf�gen';
$string['adjustment'] = 'Ausrichtung';
$string['after_submit'] = 'Nach der Abgabe';
$string['allowfullanonymous'] = 'v�llige Anonymit�t erlauben';
$string['analysis'] = 'Auswertung';
$string['anonymous'] = 'anonym';
$string['anonymous_edit'] = 'Anonym ausf�llen';
$string['anonymous_entries'] = 'Anonymoe Eintr�ge ({$a})';
$string['anonymous_user'] = 'Anonymer Nutzer';
$string['answerquestions'] = 'Frage beantworten';
$string['append_new_items'] = 'Neue Elemente hinzuf�gen';
$string['autonumbering'] = 'Automatische Nummerierung';
$string['autonumbering_help'] = 'Diese Option aktiviert die automatische Nummerierung aller Fragen';
$string['average'] = 'Mittelwert';
$string['bold'] = 'Fett';
$string['calendarend'] = 'Individuelles Feedback {$a} endet';
$string['calendarstart'] = 'Individuelles Feedback {$a} beginnt';
$string['cannotaccess'] = 'Sie k�nnen auf dieses Feedback nur aus einem Kurs zugreifen';
$string['cannotsavetempl'] = 'Vorlagen speichern ist nicht gestattet';
$string['captcha'] = 'Captcha';
$string['captchanotset'] = 'Captcha wurde nicht gew�hlt.';
$string['closebeforeopen'] = 'Sie haben das Enddatum fr�her als das Anfangsdatum angegeben.';
$string['completed_individualfeedbacks'] = 'Ausgef�lltes individuelles Feedback';
$string['complete_the_form'] = 'Formular ausf�llen...';
$string['completed'] = 'Abgeschlossen';
$string['completedon'] = 'Abgeschlossen am {$a}';
$string['completionsubmit'] = 'Als abgeschlossen ansehen, wenn das Feedback abgegeben wurde.';
$string['configallowfullanonymous'] = 'Wenn diese Option aktiviert ist, kann ein Feedback ohne vorhergehende Anmeldung abgegeben werden. Dies betrifft aber ausschlie�lich Feedbacks auf der Startseite.';
$string['confirmdeleteentry'] = 'M�chten Sie den Eintrag wirklich l�schen';
$string['confirmdeleteitem'] = 'M�chten Sie dieses Element wirklich l�schen?';
$string['confirmdeletetemplate'] = 'M�chten Sie diese Vorlage wirklich l�schen?';
$string['confirmusetemplate'] = 'M�chten Sie diese Vorlage wirklich nutzen?';
$string['continue_the_form'] = 'Beantwortung der Fragen fortsetzen';
$string['count_of_nums'] = 'Anzahl von Werten';
$string['courseid'] = 'Kurs-ID';
$string['creating_templates'] = 'Diese Frage als neue Vorlage speichern';
$string['delete_entry'] = 'Eintrag l�schen';
$string['delete_item'] = 'Element l�schen';
$string['delete_old_items'] = 'Alte Elemente l�schen';
$string['delete_pagebreak'] = 'Seitenumbruch l�schen';
$string['delete_template'] = 'Vorlage l�schen';
$string['delete_templates'] = 'Vorlagen l�schen...';
$string['depending'] = 'Abh�ngige Elemente';
$string['depending_help'] = 'Abh�ngige Elemente erlauben es Ihnen zu zeigen, wie Elemente mit den Werten anderer Elemente zusammenh�ngen<br />
<strong>Beispiel f�r abh�ngige Elemente</strong><br />
<ul>
<li>Zuerst legen Sie das Element an, von dem andere Elemente abh�ngen sollen.</li>
<li>Jetzt f�gen Sie den Seitenumbruch hinzu.</li>
<li>Dann f�gen Sie das Element hinzu, die von dem vorherigen Elementewert abh�ngen sollen. W�hlen Sie bei der Erstellung das Format "Abh�ngiges Element" und setzen Sie den notwenigen Wert auf "Abh�ngiger Wert"</li>
</ul>
<strong>Die Struktur sollte folgenderma�en aussehen:</strong>
<ol>
<li>Element -  Frage: Haben Sie ein Auto? Antwort: ja/nein</li>
<li>Seitenumbruch</li>
<li>Element - Frage: Welche Farbe hat Ihr Auto?<br />
(Dieses Element bezieht sich auf den Wert \ �ja\�des Elementes 1)</li>
<li>Element - Frage: Warum haben Sie kein Auto?<br />
(Dieses Element bezieht sich auf den Wert \�nein\ des Elementes 1)</li>
<li> ... weitere Elemente</li>
</ol>';
$string['dependitem'] = 'Abh�ngiges Element';
$string['dependvalue'] = 'Abh�ngiger Wert';
$string['description'] = 'Beschreibung';
$string['do_not_analyse_empty_submits'] = 'Leere Abgaben ignorieren';
$string['dropdown'] = 'Einzelene Antwort - Dropdown';
$string['dropdownlist'] = 'Einzelene Antwort - Dropdown';
$string['dropdownrated'] = 'Dropdown (skaliert))';
$string['dropdown_values'] = 'Antworten';
$string['drop_individualfeedback'] = 'Entferne von diesem Kurs';
$string['edit_item'] = 'Element bearbeiten';
$string['edit_items'] = 'Elemente bearbeiten';
$string['email_notification'] = 'Mitteliung bei Abgabe senden';
$string['email_notification_help'] = 'Wenn diese Option aktiviert ist, bekommen  die Trainer/innen bei Feedback-Abgaben eine Mitteilung';
$string['emailteachermail'] = '{$a->username} hat das Feedback: \'{$a->individualfeedback}\' abgeschlossen

You can view it here:

{$a->url}';
$string['emailteachermailhtml'] = '<p>{$a->username} hat das individuelle Feedback: <i>\'{$a->individualfeedback}\'</i>.ageschlossen</p>
<p>DAs Feedback ist <a href="{$a->url}">auf der Website</a> verf�gbar.</p>';
$string['entries_saved'] = 'Eintr�ge wurden gespeichert';
$string['export_questions'] = 'Fragen exportieren';
$string['export_to_excel'] = 'Nach Excel exportieren';
$string['eventresponsedeleted'] = 'Antwort gel�scht';
$string['eventresponsesubmitted'] = 'Antwort abgegeben';
$string['individualfeedbackcompleted'] = '{$a->username} abgeschlossen {$a->individualfeedbackname}';
$string['individualfeedback:addinstance'] = 'Feedback hinzuf�gen';
$string['individualfeedbackclose'] = 'Antworten erlauben bis';
$string['individualfeedback:complete'] = 'Ausf�llen eines Feedbacks';
$string['individualfeedback:createprivatetemplate'] = 'Erstellen eines kursinternen Templates';
$string['individualfeedback:createpublictemplate'] = 'Erstellen eines �ffentlichen Templates';
$string['individualfeedback:deletesubmissions'] = 'Vollst�ndige Eintr�ge l�schen';
$string['individualfeedback:deleteprivatetemplate'] = 'L�sche private Templates';
$string['individualfeedback:deletepublictemplate'] = 'L�sche �ffentliche Templates';
$string['individualfeedback:edititems'] = 'Fragen bearbeiten';
$string['individualfeedback_is_not_for_anonymous'] = 'Das Feedbacks ist f�r anonyme Teilnehmer nicht m�glich';
$string['individualfeedback_is_not_open'] = 'Feedback ist zu diesem Zeitpunkt nicht m�glich';
$string['individualfeedback:mapcourse'] = 'Kurse globalen Feedbacks zuordnen';
$string['individualfeedbackopen'] = 'Antworten erlauben ab';
$string['individualfeedback:receivemail'] = 'E-MAil-Mitteilungen empfangen';
$string['individualfeedback:view'] = 'Feedback anzeigenk';
$string['individualfeedback:viewanalysepage'] = 'Analyseseite nach der Abgabe anzeigen';
$string['individualfeedback:viewreports'] = 'Auswertungen anzeigen';
$string['file'] = 'Datei';
$string['filter_by_course'] = 'Kursfilter';
$string['handling_error'] = 'Fehler beim Module-Action-Handling';
$string['hide_no_select_option'] = 'Option \�Nicht ausgew�hlt\ verbergen';
$string['horizontal'] = 'nebeneinander';
$string['check'] = 'Multiple choice - multiple answers';
$string['checkbox'] = 'Multiple choice - mehrere Antworten sind erlaubt (check boxes)';
$string['check_values'] = 'M�gliche L�sungen';
$string['choosefile'] = 'W�hle eine datei';
$string['chosen_individualfeedback_response'] = 'gew�hlte individuelle Feedback-Antwort';
$string['downloadresponseas'] = 'Alle Antworten herunterladen als:';
$string['importfromthisfile'] = 'Von ausgew�hlter Datei importieren';
$string['import_questions'] = 'Fragen importieren';
$string['import_successfully'] = 'Erfolgreich importiert';
$string['info'] = 'Information';
$string['infotype'] = 'Information';
$string['insufficient_responses_for_this_group'] = 'Es gibt unzug�ngliche Antworten f�r diese Gruppe';
$string['insufficient_responses'] = 'unzug�ngliche Antworten';
$string['insufficient_responses_help'] = 'Um das Feedback anonym zu halten, m�ssen mindestens zwei Antworten abgegeben werden.';
$string['item_label'] = 'Bezeichnung';
$string['item_name'] = 'Fragetext';
$string['label'] = 'Textfeld';
$string['labelcontents'] = 'Inhalte';
$string['mapcourseinfo'] = 'Dies ist ein globales Feedback. Es ist in allen Kursen verf�gbar, die den Feedback-Block nutzen. Die Kurse in denen das Feedback erscheinen sollen, k�nnen begrenzt werden durch explizites zuordnen. Dazu muss der Kurs gesucht und diesem Feedback zugeordnet werden. ';
$string['mapcoursenone'] = 'Keinem Kurs zugeordnet. Diese Feedback ist in allen Kursen verf�gbar.';
$string['mapcourse'] = 'Diesem Feedback Kurse zuordnen';
$string['mapcourse_help'] = 'Sobald Sie relevante Kurse ausgew�hlt haben, k�nnen Sie diese einem Feedback zuordnen. Mehrere Kurse k�nnen ausgew�hlt werden, indem Sie die Taste Crtl bzw. Cmd w�hrend der Mausauswahl dr�cken. Ein Kurs kann jederzeit wieder von einem Feedback gel�st werden.';
$string['mapcourses'] = 'Diesem Feedback Kurse zuordnen';
$string['mappedcourses'] = 'Zugeordnete Kurse';
$string['mappingchanged'] = 'Kursstrucktur wurde ge�ndert';
$string['minimal'] = 'minimal';
$string['maximal'] = 'maximal';
$string['messageprovider:message'] = 'Erinnerung zum Feedback';
$string['messageprovider:submission'] = 'Mitteilung bei Feedback';
$string['mode'] = 'Modus';
$string['modulename'] = 'feedback';
$string['modulename_help'] = 'Mit dem Feedback-Modul k�nnen Sie eigene Umfragen oder Evaluationsformulare anlegen, wof�r Ihnen eine Reihe von Fragetypen zur Verf�gung stehen.

Die Antworten k�nnen Personen zugeordnet oder anonym erfolgen. Zeigen Sie den Teilnehmer/innen die Ergebnisse und/oder exportieren Sie die Daten sp�ter.

Legen Sie Feedback-Frageb�gen zentral an und setzen Sie sie in ausgew�hlten Kursen ein.';
$string['modulename_link'] = 'mod/individualfeedback/view';
$string['modulenameplural'] = 'Individuelles Feedback';
$string['move_item'] = 'Element verschieben';
$string['multichoice'] = 'Multiple choice';
$string['multichoicerated'] = 'Multiple choice (skaliert)';
$string['multichoicetype'] = 'Multiple choice Typ';
$string['multichoice_values'] = 'Multiple choice Werte';
$string['multiplesubmit'] = 'Mehrfache Abgaben';
$string['multiplesubmit_help'] = 'Wenn die Option f�r anonyme Auswertung aktiviert ist, d�rfen Teilnehmerinnen und Teilnehmer das Feedback beliebig oft abgegeben..';
$string['name'] = 'Name';
$string['name_required'] = 'Name ben�tigt';
$string['next_page'] = 'N�chste Seite';
$string['no_handler'] = 'Keine Aktion gefunden';
$string['no_itemlabel'] = 'Keine Bezeichnung';
$string['no_itemname'] = 'Keine BEzeichnung des Eintrags';
$string['no_items_available_yet'] = 'Noch keine Elemente definiert';
$string['non_anonymous'] = 'nicht anonym';
$string['non_anonymous_entries'] = 'Nicht-anonyme Eintr�ge({$a})';
$string['non_respondents_students'] = 'Teilnehmer/innen ohne Antwort ({$a})';
$string['not_completed_yet'] = 'Noch nicht ausgef�llt';
$string['not_started'] = 'Nicht begonnen';
$string['no_templates_available_yet'] = 'Noch keine Vorlagen definiert';
$string['not_selected'] = 'Nicht ausgew�hlt';
$string['numberoutofrange'] = 'Zahl au�erhalb des Bereichs';
$string['numeric'] = 'Numerische Antwort';
$string['numeric_range_from'] = 'Bereich von';
$string['numeric_range_to'] = 'Bereich bis';
$string['of'] = 'von';
$string['oldvaluespreserved'] = 'Alle alten Fragen und eingegebenen Werte werden aufbewahrt';
$string['oldvalueswillbedeleted'] = 'Die aktuellen Fragen und alle Antworten werden gel�scht.';
$string['only_one_captcha_allowed'] = 'Im Feedback ist nur eine Captcha erlaubt';
$string['overview'] = '�berblick';
$string['page'] = 'Seite';
$string['page-mod-individualfeedback-x'] = 'Jede Feedback Seite ';
$string['page_after_submit'] = 'Abschluss Nachricht';
$string['pagebreak'] = 'Seitenumbruch';
$string['pluginadministration'] = 'Feedback administration';
$string['pluginname'] = 'Individuelles Feedback';
$string['position'] = 'Position';
$string['previous_page'] = 'vorherige Seite';
$string['public'] = '�ffentlich';
$string['question'] = 'Frage';
$string['questionandsubmission'] = 'Einstellungen f�r Fragen und Eintr�ge';
$string['questions'] = 'Fragen';
$string['questionslimited'] = 'Nur die ersten {$a} Fragen werden angezeigt. Um alles zu sehen, lassen  sie sich die Individuellen Antworten anzeigen oder laden sie die gesamte Tabelle herunter';
$string['radio'] = 'Multiple choice - eien Antwort';
$string['radio_values'] = 'Antworten';
$string['ready_individualfeedbacks'] = 'R�ckmeldungen';
$string['required'] = 'Erforderlich';
$string['resetting_data'] = 'Feedback-Antworten zur�cksetzen. ';
$string['resetting_individualfeedbacks'] = 'Feedbacks werden zur�ckgesetzt';
$string['response_nr'] = 'Antwort Nr.';
$string['responses'] = 'Antworten';
$string['responsetime'] = 'Antwortzeit';
$string['save_as_new_item'] = 'Als neue Frage speichern';
$string['save_as_new_template'] = 'Als neue Vorlage speichern';
$string['save_entries'] = 'Eintr�ge speichern';
$string['save_item'] = 'Element speichern';
$string['saving_failed'] = 'Fehler beim speichern';
$string['search:activity'] = 'Individuelle R�ckmeldung - Aktivit�tsinformationen';
$string['search_course'] = 'Kurs suchen';
$string['searchcourses'] = 'Kurse suchen';
$string['searchcourses_help'] = 'Nach Codes oder Namen von Kursen suchen, die Sie in diesem Feedback zuordnen m�chten';
$string['selected_dump'] = 'Dump der ausgew�hlten Indexe der Variable $SESSION:';
$string['send'] = 'Senden';
$string['send_message'] = 'Nachrichten senden';
$string['show_all'] = 'Alle anzeigen';
$string['show_analysepage_after_submit'] = 'Analysesite nach der Abgabe anzeigen';
$string['show_entries'] = 'Eintr�ge anzeigen';
$string['show_entry'] = 'Eintrag zeigen';
$string['show_nonrespondents'] = 'Ohne Antwort';
$string['site_after_submit'] = 'Seite nach Eingabe';
$string['sort_by_course'] = 'Sortieren nach Kursen';
$string['started'] = 'begonnen';
$string['startedon'] = 'Begonnen am {$a}';
$string['subject'] = 'Thema';
$string['switch_item_to_not_required'] = 'Als nicht Notwenig setzen';
$string['switch_item_to_required'] = 'Als notwendig setzen';
$string['template'] = 'Vorlage';
$string['templates'] = 'Vorlagen';
$string['template_deleted'] = 'Vorlage l�schen';
$string['template_saved'] = 'Vorlage speichern';
$string['textarea'] = 'Eingabebereich';
$string['textarea_height'] = 'Anzahl der Zeilen';
$string['textarea_width'] = 'Breite des Textbereiches';
$string['textfield'] = 'Eingabezeile';
$string['textfield_maxlength'] = 'Maximale Zeichenzahl';
$string['textfield_size'] = 'Breite des Textfeldes';
$string['there_are_no_settings_for_recaptcha'] = 'Keine Einstellungen f�r diesen Chapta.';
$string['this_individualfeedback_is_already_submitted'] = 'Sie haben diese Aktivit�t bereits abgeschlossen.';
$string['typemissing'] = 'fehlender Wert "type"';
$string['update_item'] = '�nderungen speichern';
$string['url_for_continue'] = 'Url f�r den Knopf weiter';
$string['url_for_continue_help'] = 'Nach der Abgabe des Feedbacks wird ein Knopf "Weiter" gezeigt. Standardm��ig ist die Kursseite als Ziel eingestellt. Falls Sie auf eine andere URL verlinken m�chten, so k�nnen Sie hier das Ziel daf�r angeben.';
$string['use_one_line_for_each_value'] = 'Benutzen Sie f�r jede Antwort eine neue Zeile!';
$string['use_this_template'] = 'Benutzen Sie diese Vorlage';
$string['using_templates'] = 'Benutzen Sie diese Vorlagen';
$string['vertical'] = 'untereinander';
// Deprecated since Moodle 3.1.
$string['cannotmapindividualfeedback'] = 'Datenbankproblem: individuelle R�ckmeldungen k�nnen nicht dem Kurs zugeordnet werden.';
$string['line_values'] = 'Rating';
$string['mapcourses_help'] = 'Once you have selected the relevant course(s) from your search,
you can associate them with this individual feedback using map course(s). Multiple courses may be selected by holding down the Apple or Ctrl key whilst clicking on the course names. A course may be disassociated from a individual feedback at any time.';
$string['max_args_exceeded'] = 'Max 6 Argumente k�nnen behandelt werden, zu viele Argumente f�r';
$string['cancel_moving'] = 'Abbrechen';
$string['movedown_item'] = 'Frage nach unten verschieben.';
$string['move_here'] = 'Hierher bewegen.';
$string['moveup_item'] = 'Frage nach oben verschieben.';
$string['notavailable'] = 'Dieses individuelle Feedback ist nicht verf�gbar.';
$string['saving_failed_because_missing_or_false_values'] = 'Speichern fehlgeschlagen wegen fehlender oder falscher Werte';
$string['cannotunmap'] = 'Datenbankproblem, kann die Zuordnung nicht aufheben';
$string['viewcompleted'] = 'Abgeschlossene feedbacks';
$string['viewcompleted_help'] = 'Sie k�nnen ausgef�llte individuelle Feedback-Formulare einsehen, nach Kurs und / oder nach Frage suchen.
Individuelle R�ckmeldungen k�nnen nach Excel exportiert werden.';
$string['parameters_missing'] = 'Parameter fehlen von';
$string['picture'] = 'Bild';
$string['picture_file_list'] = 'Liste von Bildern';
$string['picture_values'] = 'W�hle ein oder mehrer Bilder aus der Liste:';
$string['preview'] = 'Vorschau';
$string['preview_help'] = 'In der Vorschau k�nnen Sie die Reihenfolge der Bilder �ndern.';
$string['switch_group'] = 'Gruppe wechseln';
$string['separator_decimal'] = '.';
$string['separator_thousand'] = ',';
$string['relateditemsdeleted'] = 'Alle Antworten f�r diese Frage werden gel�scht';
$string['radiorated'] = 'Radiobutton (skaliert)';
$string['radiobutton'] = 'Multiple choice - single answer allowed (radio buttons)';
$string['radiobutton_rated'] = 'Radiobutton (rated)';
// Deprecated since Moodle 3.2.
$string['start'] = 'Start';
$string['stop'] = 'End';

$string['fourlevelapproval'] = '4-stufige Antwort (Zustimmung)';
$string['fourlevelapprovaltype'] = '4-stufige Antwort (Zustimmung) Typ';
$string['fourlevelapproval_options'] = 'stimme nicht zu
stimme eher nicht zu
stimme eher zu
stimme zu';
$string['fourlevelfrequency'] = '4-stufige Antwort (H�ufigkeit)';
$string['fourlevelfrequencytype'] = '4-stufige Antwort (H�ufigkeit) Typ';
$string['fourlevelfrequency_options'] = 'nie
manchmal
oft
immer';
$string['fivelevelapproval'] = '5-stufige Antwort (Zustimmung)';
$string['fivelevelapprovaltype'] = '5-stufige Antwort (Zustimmung) Typ';
$string['fivelevelapproval_options'] = 'stimme nicht zu
stimme eher nicht zu
teils, teils
stimme eher zu
stimme zu';
$string['questiongroup'] = 'Gruppenfrage';
$string['questiongroup_name'] = 'Name der Gruppenfrage';
$string['edit_questiongroup'] = 'Bearbeite die Gruppenfrage';
$string['delete_questiongroup'] = 'L�sche die Gruppenfrage';
$string['end_of_questiongroup'] = 'Ende der Gruppenfrage';
$string['confirmdeleteitem_questiongroup'] = 'Sind Sie sich sicher, dass Sie die Fragen l�schen m�chten?
Please note: Alle Fragen in dieser Gruppe werden gel�scht.';
$string['move_questiongroup'] = 'Verschieben Sie diese Fragengruppe';
$string['evaluations'] = 'Bewertungen';
$string['detail_questions'] = 'Detail (Fragen';
$string['detail_groups'] = 'Detail (Gruppen)';
$string['overview_questions'] = '�berblick (Fragen)';
$string['overview_groups'] = '�berblick (Gruppen)';
$string['comparison_questions'] = 'Vergleich (Fragen)';
$string['comparison_groups'] = 'Vergleich (Gruppen)';
$string['error_subtab'] = 'Kein g�ltiger Unterreiter ausgew�hlt, kann diese Seite nicht laden.';
$string['all_results'] = 'Alle Ergebnisse';
$string['filter_questiongroups'] = 'Fragengruppe filtern:';
$string['individualfeedback:selfassessment'] = 'Selbsteinsch�tzung';
$string['no_questions_in_group'] = 'Keine Fragen in dieser Gruppe';
$string['error_calculating_averages'] = 'In dieser Gruppe gibt es Fragen mit unterschiedlicher Anzahl von Antworten. Es konnten keine Durchschnittswerte berechnet werden.';
$string['analysis_questiongroup'] = 'Fragengruppe mit {$a} Fragen.';
$string['selfassessment'] = 'Selbsteinsch�tzung';
$string['average_given_answer'] = 'Durchschnitt der gegeben Antworten';
$string['duplicate_and_link'] = 'Aktivit�t duplizieren und verkn�pfen';
$string['error_duplicating'] = 'Beim Duplizieren der Aktivit�t ist es zu einem Fehler gekommen. Versuchen Sie es erneut oder wenden Sie sich an Ihren Systemadministrator.';
$string['individualfeedback_cloned_and_linked'] = 'Die Aktivit�t Individuelles Feedback wurde dupliziert und verkn�pft.';
$string['individualfeedback_is_linked'] = 'Diese Aktivit�t Individuelles Feedback ist mit anderen Aktivit�ten verkn�pft und kann daher nicht bearbeitet werden.';
$string['individualfeedback_not_linked'] = 'Diese Aktivit�t Individuelles Feedback ist nicht mit anderen Aktivit�ten verkn�pft.';
$string['individualfeedback_questions_not_equal'] = 'Die Fragen der verkn�pften Aktivit�t Individuelles Feedback sind nicht gleich und k�nnen daher nicht verglichen werden.';
