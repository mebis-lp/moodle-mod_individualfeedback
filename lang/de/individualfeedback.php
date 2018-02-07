﻿<?php
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

$string['add_item'] = 'Frage hinzufügen';
$string['add_pagebreak'] = 'Seitenumbruch hinzufügen';
$string['adjustment'] = 'Ausrichtung';
$string['after_submit'] = 'Nach der Abgabe';
$string['allowfullanonymous'] = 'völlige Anonymität erlauben';
$string['analysis'] = 'Auswertung';
$string['anonymous'] = 'anonym';
$string['anonymous_edit'] = 'Anonym ausfüllen';
$string['anonymous_entries'] = 'Anonyme Einträge ({$a})';
$string['anonymous_user'] = 'Anonymer Nutzer';
$string['answerquestions'] = 'Frage beantworten';
$string['append_new_items'] = 'An bestehende Elemente anhängen';
$string['autonumbering'] = 'Automatische Nummerierung';
$string['autonumbering_help'] = 'Diese Option aktiviert die automatische Nummerierung aller Fragen';
$string['average'] = 'Mittelwert';
$string['bold'] = 'Fett';
$string['calendarend'] = 'Schüler-Feedback {$a} endet';
$string['calendarstart'] = 'Schüler-Feedback {$a} beginnt';
$string['cannotaccess'] = 'Sie können auf dieses Feedback nur aus einem Kurs zugreifen';
$string['cannotsavetempl'] = 'Vorlagen speichern ist nicht gestattet';
$string['captcha'] = 'Captcha';
$string['captchanotset'] = 'Captcha wurde nicht gewählt.';
$string['closebeforeopen'] = 'Sie haben das Enddatum früher als das Anfangsdatum angegeben.';
$string['completed_individualfeedbacks'] = 'Ausgefüllte Fragebögen';
$string['complete_the_form'] = 'Selbsteinschätzung abgeben';
$string['completed'] = 'Abgeschlossen';
$string['completedon'] = 'Abgeschlossen am {$a}';
$string['completionsubmit'] = 'Als abgeschlossen ansehen, wenn das Feedback abgegeben wurde.';
$string['configallowfullanonymous'] = 'Wenn diese Option aktiviert ist, kann ein Feedback ohne vorhergehende Anmeldung abgegeben werden. Dies betrifft aber ausschließlich Feedbacks auf der Startseite.';
$string['confirmdeleteentry'] = 'Möchten Sie den Eintrag wirklich löschen?';
$string['confirmdeleteitem'] = 'Möchten Sie dieses Element wirklich löschen?';
$string['confirmdeletetemplate'] = 'Möchten Sie diese Vorlage wirklich löschen?';
$string['confirmusetemplate'] = 'Bitte wählen Sie einzelne Teile (Fragen oder Fragegruppen) zum Verwenden aus oder übernehmen Sie den gesamten Fragebogen.';
$string['continue_the_form'] = 'Feedback fortsetzen';
$string['count_of_nums'] = 'Anzahl von Werten';
$string['courseid'] = 'Kurs-ID';
$string['creating_templates'] = 'Diesen Fragebogen als neue Vorlage speichern';
$string['delete_entry'] = 'Eintrag löschen';
$string['delete_item'] = 'Element löschen';
$string['delete_old_items'] = 'Bestehende Elemente überschreiben';
$string['delete_pagebreak'] = 'Seitenumbruch löschen';
$string['delete_template'] = 'Vorlage löschen';
$string['delete_templates'] = 'Vorlagen löschen...';
$string['depending'] = 'Abhängige Elemente';
$string['depending_help'] = 'Abhängige Elemente erlauben es Ihnen zu zeigen, wie Elemente mit den Werten anderer Elemente zusammenhängen<br />
<strong>Beispiel für abhängige Elemente</strong><br />
<ul>
<li>Zuerst legen Sie das Element an, von dem andere Elemente abhängen sollen.</li>
<li>Jetzt fügen Sie den Seitenumbruch hinzu.</li>
<li>Dann fügen Sie das Element hinzu, die von dem vorherigen Elementewert abhängen sollen. Wählen Sie bei der Erstellung das Format "Abhängiges Element" und setzen Sie den notwenigen Wert auf "Abhängiger Wert"</li>
</ul>
<strong>Die Struktur sollte folgendermaßen aussehen:</strong>
<ol>
<li>Element -  Frage: Haben Sie ein Auto? Antwort: ja/nein</li>
<li>Seitenumbruch</li>
<li>Element - Frage: Welche Farbe hat Ihr Auto?<br />
(Dieses Element bezieht sich auf den Wert \ ´ja\´des Elementes 1)</li>
<li>Element - Frage: Warum haben Sie kein Auto?<br />
(Dieses Element bezieht sich auf den Wert \´nein\ des Elementes 1)</li>
<li> ... weitere Elemente</li>
</ol>';
$string['dependitem'] = 'Abhängiges Element';
$string['dependvalue'] = 'Abhängiger Wert';
$string['description'] = 'Beschreibung';
$string['do_not_analyse_empty_submits'] = 'Leere Abgaben ignorieren';
$string['dropdown'] = 'Einzelne Antwort - Dropdown';
$string['dropdownlist'] = 'Einzelne Antwort - Dropdown';
$string['dropdownrated'] = 'Dropdown (skaliert))';
$string['dropdown_values'] = 'Antworten';
$string['drop_individualfeedback'] = 'Entferne von diesem Kurs';
$string['edit_item'] = 'Element bearbeiten';
$string['edit_items'] = 'Elemente bearbeiten';
$string['email_notification'] = 'Mitteilung bei Abgabe senden';
$string['email_notification_help'] = 'Wenn diese Option aktiviert ist, bekommen  die Trainer/innen bei Feedback-Abgaben eine Mitteilung.';
$string['emailteachermail'] = '{$a->username} hat das Feedback: \'{$a->individualfeedback}\' abgeschlossen.

Sie können es hier einsehen:

{$a->url}';
$string['emailteachermailhtml'] = '<p>{$a->username} hat das individuelle Feedback: <i>\'{$a->individualfeedback}\'</i> abgeschlossen.</p>
<p>Das Feedback ist <a href="{$a->url}">auf der Website</a> verfügbar.</p>';
$string['entries_saved'] = 'Vielen Dank für die Teilnahme am Feedback!';
$string['export_questions'] = 'Fragen exportieren';
$string['export_to_excel'] = 'Nach Excel exportieren';
$string['eventresponsedeleted'] = 'Antwort gelöscht';
$string['eventresponsesubmitted'] = 'Antwort abgegeben';
$string['individualfeedbackcompleted'] = '{$a->username} abgeschlossen {$a->individualfeedbackname}';
$string['individualfeedback:addinstance'] = 'Feedback hinzufügen';
$string['individualfeedbackclose'] = 'Antworten erlauben bis';
$string['individualfeedback:complete'] = 'Ausfüllen eines Feedbacks';
$string['individualfeedback:createprivatetemplate'] = 'Erstellen eines kursinternen Templates';
$string['individualfeedback:createpublictemplate'] = 'Erstellen eines öffentlichen Templates';
$string['individualfeedback:deletesubmissions'] = 'Vollständige Einträge löschen';
$string['individualfeedback:deleteprivatetemplate'] = 'Lösche private Templates';
$string['individualfeedback:deletepublictemplate'] = 'Lösche öffentliche Templates';
$string['individualfeedback:edititems'] = 'Fragen bearbeiten';
$string['individualfeedback_is_not_for_anonymous'] = 'Das Feedback ist für anonyme Teilnehmer nicht möglich.';
$string['individualfeedback_is_not_open'] = 'Feedback ist zu diesem Zeitpunkt nicht möglich.';
$string['individualfeedback:mapcourse'] = 'Kurse globalen Feedbacks zuordnen';
$string['individualfeedbackopen'] = 'Antworten erlauben ab';
$string['individualfeedback:receivemail'] = 'E-Mail-Mitteilungen empfangen';
$string['individualfeedback:view'] = 'Feedback anzeigens';
$string['individualfeedback:viewanalysepage'] = 'Analyseseite nach der Abgabe anzeigen';
$string['individualfeedback:viewreports'] = 'Auswertungen anzeigen';
$string['file'] = 'Datei';
$string['filter_by_course'] = 'Kursfilter';
$string['handling_error'] = 'Fehler beim Module-Action-Handling';
$string['hide_no_select_option'] = 'Option \´Nicht ausgewählt\ verbergen';
$string['horizontal'] = 'nebeneinander';
$string['check'] = 'Multiple choice';
$string['checkbox'] = 'Multiple choice - mehrere Antworten sind erlaubt (check boxes)';
$string['check_values'] = 'Mögliche Lösungen';
$string['choosefile'] = 'Wähle eine Datei';
$string['chosen_individualfeedback_response'] = 'gewählte individuelle Feedback-Antwort';
$string['downloadresponseas'] = 'Alle Antworten herunterladen als:';
$string['importfromthisfile'] = 'Von ausgewählter Datei importieren';
$string['import_questions'] = 'Fragen importieren';
$string['import_successfully'] = 'Erfolgreich importiert';
$string['info'] = 'Information';
$string['infotype'] = 'Information';
$string['insufficient_responses_for_this_group'] = 'Es gibt unzulängliche Antworten für diese Gruppe';
$string['insufficient_responses'] = 'unzulängliche Antworten';
$string['insufficient_responses_help'] = 'Um das Feedback anonym zu halten, müssen mindestens zwei Antworten abgegeben werden.';
$string['item_label'] = 'Bezeichnung (optional)';
$string['item_name'] = 'Fragetext';
$string['label'] = 'Textfeld';
$string['labelcontents'] = 'Inhalte';
$string['mapcourseinfo'] = 'Dies ist ein globales Feedback. Es ist in allen Kursen verfügbar, die den Feedback-Block nutzen. Die Kurse, in denen das Feedback erscheinen sollen, können begrenzt werden durch explizites Zuordnen. Dazu muss der Kurs gesucht und diesem Feedback zugeordnet werden.';
$string['mapcoursenone'] = 'Keinem Kurs zugeordnet. Diese Feedback ist in allen Kursen verfügbar.';
$string['mapcourse'] = 'Diesem Feedback einen Kurs zuordnen';
$string['mapcourse_help'] = 'Sobald Sie relevante Kurse ausgewählt haben, können Sie diese einem Feedback zuordnen. Mehrere Kurse können ausgewählt werden, indem Sie die Taste Crtl bzw. Cmd während der Mausauswahl drücken. Ein Kurs kann jederzeit wieder von einem Feedback gelöst werden.';
$string['mapcourses'] = 'Diesem Feedback Kurse zuordnen';
$string['mappedcourses'] = 'Zugeordnete Kurse';
$string['mappingchanged'] = 'Kursstruktur wurde geändert.';
$string['minimal'] = 'minimal';
$string['maximal'] = 'maximal';
$string['messageprovider:message'] = 'Erinnerung zum Feedback';
$string['messageprovider:submission'] = 'Mitteilung bei Feedback';
$string['mode'] = 'Modus';
$string['modulename'] = 'Schüler-Feedback';
$string['modulename_help'] = 'Die Aktivität Schüler-Feedback ist eine weiterentwickelte Version der Feedback-Aktivität. 

Sie erlaubt es Lehrkräften, Individualfeedback von Schülern einzuholen und die Ergebnisse mit einer Selbsteinschätzung zu vergleichen. Befragungen können wiederholt und ihre Ergebnisse verglichen werden, um eine zeitliche Entwicklung darstellen zu können.
Dazu kann die Lehrkraft aus wissenschaftlich erarbeiteten Fragebogen-Vorlagen zum Therma Unterrichtsfeedback auswählen, diese - wenn gewünscht - auf die eigenen Bedürfnisse anpassen oder eigene neue Fragebögen erstellen.

Die Teilnahme an einem Schüler-Feedback ist stets anonym, die Ergebnisse stehen nur Nutzern in der Lehrerolle des jeweiligen Kurses zur Verfügung.

Die Schüler-Feedback-Aktivität kann genutzt werden für:

* Unterrichtsfeedback (vor allem in Form der Fragebogen-Vorlagen)
* Feedback zu den Kursinhalten, wenn die Befragung wiederholt werden soll
* Feedback, bei denen verschiedene Themen verglichen werden sollen
* Befragungen, bei denen Anonymität sicherzustellen ist';
$string['modulename_link'] = 'mod/individualfeedback/view';
$string['modulenameplural'] = 'Schüler-Feedback';
$string['move_item'] = 'Element verschieben';
$string['multichoice'] = 'Multiple Choice';
$string['multichoicerated'] = 'Multiple Choice (skaliert)';
$string['multichoicetype'] = 'Multiple Choice: Typ';
$string['multichoice_values'] = 'Multiple Choice: Werte';
$string['multiplesubmit'] = 'Mehrfache Abgaben';
$string['multiplesubmit_help'] = 'Wenn die Option für anonyme Auswertung aktiviert ist, dürfen Teilnehmerinnen und Teilnehmer das Feedback beliebig oft abgegeben..';
$string['name'] = 'Name';
$string['name_required'] = 'Name benötigt';
$string['next_page'] = 'Nächste Seite';
$string['no_handler'] = 'Keine Aktion gefunden';
$string['no_itemlabel'] = 'Keine Bezeichnung';
$string['no_itemname'] = 'Keine Bezeichnung des Eintrags';
$string['no_items_available_yet'] = 'Noch keine Elemente definiert';
$string['non_anonymous'] = 'nicht anonym';
$string['non_anonymous_entries'] = 'Nicht-anonyme Einträge({$a})';
$string['non_respondents_students'] = 'Teilnehmer/innen ohne Antwort ({$a})';
$string['not_completed_yet'] = 'Noch nicht ausgefüllt';
$string['not_started'] = 'Nicht begonnen';
$string['no_templates_available_yet'] = 'Noch keine Vorlagen definiert';
$string['not_selected'] = 'Nicht ausgewählt';
$string['numberoutofrange'] = 'Zahl außerhalb des Bereichs';
$string['numeric'] = 'Numerische Antwort';
$string['numeric_range_from'] = 'Bereich von';
$string['numeric_range_to'] = 'Bereich bis';
$string['of'] = 'von';
$string['oldvaluespreserved'] = 'Alle alten Fragen und eingegebenen Werte werden aufbewahrt.';
$string['oldvalueswillbedeleted'] = 'Die aktuellen Fragen und alle Antworten werden gelöscht.';
$string['only_one_captcha_allowed'] = 'Im Feedback ist nur eine Captcha erlaubt.';
$string['overview'] = 'Überblick';
$string['page'] = 'Seite';
$string['page-mod-individualfeedback-x'] = 'Jede Feedback Seite';
$string['page_after_submit'] = 'Abschluss-Nachricht';
$string['pagebreak'] = 'Seitenumbruch';
$string['pluginadministration'] = 'Schüler-Feedback-Administration';
$string['pluginname'] = 'Schüler-Feedback';
$string['position'] = 'Position';
$string['previous_page'] = 'vorherige Seite';
$string['public'] = 'öffentlich';
$string['question'] = 'Frage';
$string['questionandsubmission'] = 'Einstellungen für Fragen und Einträge';
$string['questions'] = 'Fragen';
$string['questionslimited'] = 'Nur die ersten {$a} Fragen werden angezeigt. Um alles zu sehen, lassen  sie sich die Individuellen Antworten anzeigen oder laden sie die gesamte Tabelle herunter.';
$string['radio'] = 'Multiple choice - eine Antwort';
$string['radio_values'] = 'Antworten';
$string['ready_individualfeedbacks'] = 'Rückmeldungen';
$string['required'] = 'erforderlich';
$string['resetting_data'] = 'Feedback-Antworten zurücksetzen.';
$string['resetting_individualfeedbacks'] = 'Feedbacks werden zurückgesetzt.';
$string['response_nr'] = 'Antwort Nr.';
$string['responses'] = 'Antworten';
$string['responsetime'] = 'Antwortzeit';
$string['save_as_new_item'] = 'Als neues Element speichern';
$string['save_as_new_template'] = 'Als neue Vorlage speichern';
$string['save_entries'] = 'Einträge speichern';
$string['save_item'] = 'Element speichern';
$string['saving_failed'] = 'Fehler beim Speichern';
$string['search:activity'] = 'Individuelle Rückmeldung - Aktivitätsinformationen';
$string['search_course'] = 'Kurs suchen';
$string['searchcourses'] = 'Kurse suchen';
$string['searchcourses_help'] = 'Nach Codes oder Namen von Kursen suchen, die Sie in diesem Feedback zuordnen möchten';
$string['selected_dump'] = 'Dump der ausgewählten Indizes der Variable $SESSION:';
$string['send'] = 'Senden';
$string['send_message'] = 'Nachrichten senden';
$string['show_all'] = 'Alle anzeigen';
$string['show_analysepage_after_submit'] = 'Analyseseite nach der Abgabe anzeigen';
$string['show_entries'] = 'Einträge anzeigen';
$string['show_entry'] = 'Eintrag zeigen';
$string['show_nonrespondents'] = 'Ohne Antwort';
$string['site_after_submit'] = 'Seite nach Eingabe';
$string['sort_by_course'] = 'Sortieren nach Kursen';
$string['started'] = 'begonnen';
$string['startedon'] = 'begonnen am {$a}';
$string['subject'] = 'Thema';
$string['switch_item_to_not_required'] = 'Als nicht notwenig setzen';
$string['switch_item_to_required'] = 'Als notwendig setzen';
$string['template'] = 'Vorlage';
$string['templates'] = 'Vorlagen';
$string['template_deleted'] = 'Vorlage löschen';
$string['template_saved'] = 'Vorlage speichern';
$string['textarea'] = 'Eingabebereich';
$string['textarea_height'] = 'Anzahl der Zeilen';
$string['textarea_width'] = 'Breite des Textbereiches';
$string['textfield'] = 'Eingabezeile';
$string['textfield_maxlength'] = 'Maximale Zeichenzahl';
$string['textfield_size'] = 'Breite des Textfeldes';
$string['there_are_no_settings_for_recaptcha'] = 'Keine Einstellungen für dieses Captcha.';
$string['this_individualfeedback_is_already_submitted'] = 'Sie haben diese Aktivität bereits abgeschlossen.';
$string['typemissing'] = 'fehlender Wert "type"';
$string['update_item'] = 'Änderungen speichern';
$string['url_for_continue'] = 'URL für den Knopf weiter';
$string['url_for_continue_help'] = 'Nach der Abgabe des Feedbacks wird ein Knopf "Weiter" gezeigt. Standardmäßig ist die Kursseite als Ziel eingestellt. Falls Sie auf eine andere URL verlinken möchten, so können Sie hier das Ziel dafür angeben.';
$string['use_one_line_for_each_value'] = 'Benutzen Sie für jede Antwort eine neue Zeile!';
$string['use_this_template'] = 'Diese Vorlage nutzen';
$string['using_templates'] = 'Eine Vorlage auswählen';
$string['vertical'] = 'untereinander';
// Deprecated since Moodle 3.1.
$string['cannotmapindividualfeedback'] = 'Datenbankproblem: individuelle Rückmeldungen können nicht dem Kurs zugeordnet werden.';
$string['line_values'] = 'Rating';
$string['mapcourses_help'] = 'Once you have selected the relevant course(s) from your search,
you can associate them with this individual feedback using map course(s). Multiple courses may be selected by holding down the Apple or Ctrl key whilst clicking on the course names. A course may be disassociated from a individual feedback at any time.';
$string['max_args_exceeded'] = 'Max. 6 Argumente können behandelt werden, zu viele Argumente für';
$string['cancel_moving'] = 'Abbrechen';
$string['movedown_item'] = 'Frage nach unten verschieben.';
$string['move_here'] = 'Hierher bewegen.';
$string['moveup_item'] = 'Frage nach oben verschieben.';
$string['notavailable'] = 'Dieses individuelle Feedback ist nicht verfügbar.';
$string['saving_failed_because_missing_or_false_values'] = 'Speichern fehlgeschlagen wegen fehlender oder falscher Werte';
$string['cannotunmap'] = 'Datenbankproblem, kann die Zuordnung nicht aufheben';
$string['viewcompleted'] = 'Abgeschlossene Fragebögen';
$string['viewcompleted_help'] = 'Sie können ausgefüllte individuelle Feedback-Formulare einsehen, nach Kurs und / oder nach Frage suchen.
Individuelle Rückmeldungen können nach Excel exportiert werden.';
$string['parameters_missing'] = 'Parameter fehlen von';
$string['picture'] = 'Bild';
$string['picture_file_list'] = 'Liste von Bildern';
$string['picture_values'] = 'Wähle ein oder mehrere Bilder aus der Liste:';
$string['preview'] = 'Vorschau';
$string['preview_help'] = 'In der Vorschau können Sie die Reihenfolge der Bilder ändern.';
$string['switch_group'] = 'Gruppe wechseln';
$string['separator_decimal'] = '.';
$string['separator_thousand'] = ',';
$string['relateditemsdeleted'] = 'Alle Antworten für diese Frage werden gelöscht.';
$string['radiorated'] = 'Radiobutton (skaliert)';
$string['radiobutton'] = 'Multiple Choice - Einzelne Antwort ("Radiobutton")';
$string['radiobutton_rated'] = 'Radiobutton (skaliert)';
// Deprecated since Moodle 3.2.
$string['start'] = 'Start';
$string['stop'] = 'End';

$string['fourlevelapproval'] = '4-stufig ("stimme nicht zu" bis "stimme zu")';
$string['fourlevelapprovaltype'] = 'Typ: 4-stufig (Zustimmung)';
$string['fourlevelapproval_options'] = 'stimme nicht zu
stimme eher nicht zu
stimme eher zu
stimme zu';
$string['fourlevelfrequency'] = '4-stufig ("nie" bis "immer")';
$string['fourlevelfrequencytype'] = 'Typ: 4-stufig (Häufigkeit)';
$string['fourlevelfrequency_options'] = 'nie
manchmal
oft
immer';
$string['fivelevelapproval'] = '5-stufig ("stimme nicht zu" bis "stimme zu")';
$string['fivelevelapprovaltype'] = 'Typ: 5-stufig (Zustimmung)';
$string['fivelevelapproval_options'] = 'stimme nicht zu
stimme eher nicht zu
teils, teils
stimme eher zu
stimme zu';
$string['questiongroup'] = 'Fragengruppe';
$string['questiongroup_name'] = 'Name der Fragengruppe';
$string['edit_questiongroup'] = 'Fragengruppe bearbeiten';
$string['delete_questiongroup'] = 'Fragengruppe löschen';
$string['end_of_questiongroup'] = 'Ende der Fragengruppe';
$string['confirmdeleteitem_questiongroup'] = 'Sind Sie sicher, dass Sie die Fragengruppe löschen wollen?
Bitte beachten: Alle Fragen in dieser Gruppe werden gelöscht.';
$string['move_questiongroup'] = 'Fragengruppe verschieben';
$string['evaluations'] = 'Bewertungen';
$string['detail_questions'] = 'Details (Fragen)';
$string['detail_groups'] = 'Details (Gruppen)';
$string['overview_questions'] = 'Überblick (Fragen)';
$string['overview_groups'] = 'Überblick (Gruppen)';
$string['comparison_questions'] = 'Vergleich (Fragen)';
$string['comparison_groups'] = 'Vergleich (Gruppen)';
$string['error_subtab'] = 'Kein gültiges Tab ausgewählt, kann diese Seite nicht laden.';
$string['all_results'] = 'Alle Ergebnisse';
$string['filter_questiongroups'] = 'Fragengruppe filtern';
$string['individualfeedback:selfassessment'] = 'Selbsteinschätzung';
$string['no_questions_in_group'] = 'Keine Fragen in dieser Gruppe';
$string['error_calculating_averages'] = 'In dieser Gruppe gibt es Fragen mit unterschiedlicher Anzahl von Antworten. Es konnten keine Durchschnittswerte berechnet werden.';
$string['analysis_questiongroup'] = 'Fragengruppe mit {$a} Fragen.';
$string['selfassessment'] = 'Selbsteinschätzung';
$string['average_given_answer'] = 'Durchschnittswert der gegebenen Antworten';
$string['duplicate_and_link'] = 'Duplizieren und verknüpfen';
$string['error_duplicating'] = 'Beim Duplizieren der Aktivität ist es zu einem Fehler gekommen. Versuchen Sie es erneut oder wenden Sie sich an Ihren Systemadministrator.';
$string['individualfeedback_cloned_and_linked'] = 'Die Aktivität Schüler-Feedback wurde dupliziert und verknüpft.';
$string['individualfeedback_is_linked'] = 'Diese Aktivität ist mit anderen Aktivitäten verknüpft. Der Fragebogen kann daher nicht bearbeitet werden.';
$string['individualfeedback_not_linked'] = 'Diese Aktivität ist nicht mit anderen Aktivitäten verknüpft.';
$string['individualfeedback_questions_not_equal'] = 'Die Fragen der verknüpften Aktivitäten sind nicht gleich. Diese können daher nicht verglichen werden.';
$string['negative_formulated'] = 'Kontrollfrage';
$string['negative_formulated_help'] = 'Kontrollfragen sind semantisch gedrehte Fragen, d. h. negativ formuliert. Sie werden in Fragebögen eingebaut, um dem Verfälschen von Ergebnissen durch eine Ja-Sage-Tendenz entgegenzuwirken. <br> Werden Kontrollfragen in Fragengruppen integriert, werden die Antwortwerte bei der Mittelwertberechnung invertiert, damit postive oder negative Tendenzen korrekt dargestellt werden.';
