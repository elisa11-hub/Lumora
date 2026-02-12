# <Projektname> – Webspiel gegen Depression (XAMPP)

Webspiel "Lumora" als Webanwendung mit Frontend (HTML/CSS/JavaScript) und Backend (PHP + MySQL).
⚠️ Hinweis: Dieses Projekt ersetzt keine Therapie oder medizinische Behandlung.

## Inhalt
- Voraussetzungen
- Installation (XAMPP)
- Datenbank einrichten
- Projekt starten
- Manuelle Tests (Funktionsprüfung)
- Ordnerstruktur
- Troubleshooting
---

## Voraussetzungen
- XAMPP (Apache + MySQL/MariaDB)
- Browser (Chrome/Firefox/Edge)
- Optional: phpMyAdmin (ist in XAMPP enthalten)

> Getestet mit: Windows + XAMPP
---

## Installation (XAMPP)

1. Repository herunterladen/klonen und Projektordner in den XAMPP Webroot legen:

**Windows**
- `C:\xampp\htdocs\<projektordner>`

2. Prüfen, dass die Startdatei existiert, z. B.:
- `startpage.html` (Frontend Einstieg) **oder**
- `startpage.php` (wenn PHP die Startseite liefert)
---

## Datenbank einrichten

### Import per phpMyAdmin (empfohlen)
1. XAMPP Control Panel öffnen
2. **MySQL** starten
3. Im Browser öffnen:
   - `http://localhost/phpmyadmin`
4. Datenbank anlegen:
   - Name: `<db_name>` (z. B. `Lumora`)
5. Import:
   - Tab **Importieren** → Datei auswählen: `database/<dein_dump>.sql` (oder `sql/schema.sql`)
   - **OK** / **Los** klicken

---

## Konfiguration (DB-Zugang)

Falls du eine Konfigurationsdatei hast (Beispiele):
- `backend/config.php`
- `config/db.php`
- `.env` (selten bei klassischem PHP ohne Framework)

Beispiel für `config.php`:
```php
<?php
$DB_HOST = "localhost";
$DB_NAME = "<db_name>";
$DB_USER = "root";
$DB_PASS = ""; // Standard in XAMPP oft leer
?>
