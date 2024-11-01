=== TransFinanz ===
Contributors: KWMSources
Tags: Finanzen, Gruppen, Politik, Transparenz, Basisdemokratie, Finanzverwaltung
Donate link: http://www.kay-wilhelm.de
Requires at least: 3.3.0
Tested up to: 4.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Einfache, transparente und basisdemokratische Finanzverwaltung

== Description ==
Die TransFinanz ist ein WordPress-PlugIn zur Transparenten Finanzverwaltung. Hier haben die Schatzmeister*innen, Kassenwärter*innen oder sonstige Finanzbeauftragten die Möglichkeit, die Gelder der jeweiligen Gruppe oder Organisation auf dem Papier zu verwalten. Sie dient als Ersatz für die Verwaltung von Vorgängen und Geldern, jedoch nicht für Überweisungen oder die Quittungsverwaltung.

Auf diese Weise werden die Finanzen zentral verwaltet und können jenachdem dritten zugänglich gemacht werden. Wichtige Informationen müssen nicht aufwendig ermittelt werden. Sie sind sofort verfügbar. Als Beispiel für die transparente Finanzverwaltung lässt sich die GRÜNE JUGEND Bonn nennen, die die TransFinanz schon seit mehr als einem Jahr nutzt.

Die TransFinanz kann aus einer WordPress-Installation im PlugIn-Manager heraus installiert werden. Die Verwaltung wird im Backend der jeweiligen Wordpress-Installation vorgenommen. Wichtige Informationen sowie Formulare können dabei im Frontend sichtbar gemacht werden.

== Installation ==
Bitte http://www.kay-wilhelm.de/wiki/index.php?title=Installation_%28TransFinanz%29 beachten.

== Frequently Asked Questions ==
Bitte http://www.kay-wilhelm.de/wiki/ beachten

== Screenshots ==


== Changelog ==
= 0.0.1 =
* Erste Veröffentlichung

= 0.1.2 =
Bugfixing, Performance & neue Features
* BF: Funktion Geldstand: Nachkommastellen wurden in bestimmten Fällen geschluckt
	* funktionen.php
* BF: Anzeige Antrag im Frontend: Berücksichtigung generelle Ansichts-Einstellung
	* funktionen.php
* BF: Korrektur der Sortierung: Kleinste Ordnung wird nun als erstes angezeigt
	* funktionen.php
* PER: Geldquellen Wert: Datentypumstellung auf Double
	* funktionen.php
	* Transfinanz.php
	* geldquelle.php
* PER: Einheitliche Zahlenbearbeitung
	* funktionen.php
	* antraege.php
	* statistik.php
	* vorgaenge.php
* PER: Einheitliche Anzeige von Zahlen
	* funktionen.php
	* absetzbaremittel.php
	* antraege.php 
	* geldquelle.php
	* planung.php
	* statistik.php
	* vorgaenge.php
* BF: Löschung des bisherigen Filter bei Vorgangsbearbeitung
	* vorgaenge.php
* BF: In der Übersicht konnten Buchungen einzeln gelöscht bzw. beim Namen einzelnt geändert werden. Nun werden beide gelöscht oder geändert
	* vorgaenge.php
* PER: Aufruf von Namen in Funktionen auslegen
	* funktionen.php
	* vorgaenge.php
* BF: Seitenaufruf, URL verlängerte sich nach jedem Seitenwechsel
	* funktionen.php
* NF: Anzeige von Auszügen - Erklärung im Handbuch www.kay-wilhelm.de
	* funktionen.php
	* planung.php

= 0.2.3 =
Bugfixing, Performance & neue Features
* BF: Anzeige Geldstand bei erwarteten Geldstand 0 - Kein Geldbetrag wurde angezeigt
        * funktionen.php
* PER: Nur noch Funktionen laden und ausführen, wenn sie auch tatsächlich gebraucht werden
        * Transfinanz.php
        * absetzbaremittel.php
        * antraege.php
        * einstellungen.php
        * formularengine.php
        * funktionen.php
        * geldquelle.php
        * init.php
        * kategorien.php
        * konten.php
        * kontokategorien.php
        * planung.php
        * vorgaenge.php
* PER: Verwendung von zeitsparenden Operatoren
        * Transfinanz.php
        * absetzbaremittel.php
        * antraege.php
        * einstellungen.php
        * formularengine.php
        * funktionen.php
        * geldquelle.php
        * init.php
        * kategorien.php
        * konten.php
        * kontokategorien.php
        * planung.php
        * vorgaenge.php
* BF: War einer Konto-Kategorie kein Konto zugeordnet, wurden alle Konten der Konto-Kategorie zugeordnet. Dieser Fehler wurde behoben
        * funktionen.php
* BF: Einzelne Quittungs-IDs wurden doppelt genutzt. In Zukunft werden Q-IDs nicht mehr doppelt vergeben
        * funktionen.php
* NF: Excel-Export: Vorgänge können nun via Excel-Export exportiert werden
        * funktionen.php
        * vorgaenge.php
        * transfinanz.php

= 0.3.4 =
HOT FIX: Bugfixing, Neue Features
* NF: E-Mail Versand bei neuen Anträgen
        * funktionen.php
        * antraege.php
* NF: Anzeige zu bearbeitende Anträge im Menü
        * funktionen.php
        * init.php
* NF: In den Einstellungen kann nun angegeben werden, ab welcher Rolle die TransFinanz genutzt werden kann
        * einstellungen.php
        * init.php 
* BF: Anträge von nicht angemeldeten Usern waren nicht möglich
        * antraege.php
* BF: Durch die Änderung der Anzeige, keine Zahlen mehr unter 0,01 anzuzeigen, wurden Ausgaben im Haushalt nicht mehr angezeigt
        * funktionen.php
* BF: Durch die Änderungen der Operationen war keine Deaktivierung mehr möglich
        * absetzbaremittel.php
        * geldquellen.php
        * kategorien.php
        * konten.php

= 0.4.5 = 
Bugfixing, neue Features
* BF: Auswahl Absetzbare Mittel bei Anträge sind im Backend nun wieder auswählbar
        * antraege.php
* NF: Zeitbudgets, Budgets sind nun über einen bestimmten Zeitraum hinweg verfügbar
        * funktionen.php
        * geldquelle.php
        * planung.php
        * vorgaenge.php

== Upgrade Notice ==
= 0.0.1 =
* Erste Veröffentlichung

= 0.1.2 =
Bugfixing, Performance & neue Features
* BF: Funktion Geldstand: Nachkommastellen wurden in bestimmten Fällen geschluckt
	* funktionen.php
* BF: Anzeige Antrag im Frontend: Berücksichtigung generelle Ansichts-Einstellung
	* funktionen.php
* BF: Korrektur der Sortierung: Kleinste Ordnung wird nun als erstes angezeigt
	* funktionen.php
* PER: Geldquellen Wert: Datentypumstellung auf Double
	* funktionen.php
	* Transfinanz.php
	* geldquelle.php
* PER: Einheitliche Zahlenbearbeitung
	* funktionen.php
	* antraege.php
	* statistik.php
	* vorgaenge.php
* PER: Einheitliche Anzeige von Zahlen
	* funktionen.php
	* absetzbaremittel.php
	* antraege.php 
	* geldquelle.php
	* planung.php
	* statistik.php
	* vorgaenge.php
* BF: Löschung des bisherigen Filter bei Vorgangsbearbeitung
	* vorgaenge.php
* BF: In der Übersicht konnten Buchungen einzeln gelöscht bzw. beim Namen einzelnt geändert werden. Nun werden beide gelöscht oder geändert
	* vorgaenge.php
* PER: Aufruf von Namen in Funktionen auslegen
	* funktionen.php
	* vorgaenge.php
* BF: Seitenaufruf, URL verlängerte sich nach jedem Seitenwechsel
	* funktionen.php
* NF: Anzeige von Auszügen - Erklärung im Handbuch www.kay-wilhelm.de
	* funktionen.php
	* planung.php

= 0.2.3 =
Bugfixing, Performance & neue Features
* BF: Anzeige Geldstand bei erwarteten Geldstand 0 - Kein Geldbetrag wurde angezeigt
        * funktionen.php
* PER: Nur noch Funktionen laden und ausführen, wenn sie auch tatsächlich gebraucht werden
        * Transfinanz.php
        * absetzbaremittel.php
        * antraege.php
        * einstellungen.php
        * formularengine.php
        * funktionen.php
        * geldquelle.php
        * init.php
        * kategorien.php
        * konten.php
        * kontokategorien.php
        * planung.php
        * vorgaenge.php
* PER: Verwendung von zeitsparenden Operatoren
        * Transfinanz.php
        * absetzbaremittel.php
        * antraege.php
        * einstellungen.php
        * formularengine.php
        * funktionen.php
        * geldquelle.php
        * init.php
        * kategorien.php
        * konten.php
        * kontokategorien.php
        * planung.php
        * vorgaenge.php
* BF: War einer Konto-Kategorie kein Konto zugeordnet, wurden alle Konten der Konto-Kategorie zugeordnet. Dieser Fehler wurde behoben
        * funktionen.php
* BF: Einzelne Quittungs-IDs wurden doppelt genutzt. In Zukunft werden Q-IDs nicht mehr doppelt vergeben
        * funktionen.php
* NF: Excel-Export: Vorgänge können nun via Excel-Export exportiert werden
        * funktionen.php
        * vorgaenge.php
        * transfinanz.php

= 0.3.4 =
HOT FIX: Bugfixing, Neue Features
* NF: E-Mail Versand bei neuen Anträgen
        * funktionen.php
        * antraege.php
* NF: Anzeige zu bearbeitende Anträge im Menü
        * funktionen.php
        * init.php
* NF: In den Einstellungen kann nun angegeben werden, ab welcher Rolle die TransFinanz genutzt werden kann
        * einstellungen.php
        * init.php 
* BF: Anträge von nicht angemeldeten Usern waren nicht möglich
        * antraege.php
* BF: Durch die Änderung der Anzeige, keine Zahlen mehr unter 0,01 anzuzeigen, wurden Ausgaben im Haushalt nicht mehr angezeigt
        * funktionen.php
* BF: Durch die Änderungen der Operationen war keine Deaktivierung mehr möglich
        * absetzbaremittel.php
        * geldquellen.php
        * kategorien.php
        * konten.php

= 0.4.5 = 
Bugfixing, neue Features
* BF: Auswahl Absetzbare Mittel bei Anträge sind im Backend nun wieder auswählbar
        * antraege.php
* NF: Zeitbudgets, Budgets sind nun über einen bestimmten Zeitraum hinweg verfügbar
        * funktionen.php
        * geldquelle.php
        * planung.php
        * vorgaenge.php

= 1.0.0 =
Neue TransFinanz