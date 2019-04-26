export default {
  edit_maps: {
    title: 'Baupläne',
    help: 'Ändern Sie die Struktur der Baupläne.',
    default_map_name: 'Neuer Bauplan',
    actions: {
      add_map: 'Bauplan hinzufügen',
      reorder: 'neu sortieren',
      add_map_files: 'Bauplanversionen hinzufügen',
      hide_map_files: 'Bauplanversionen verstecken',
      save_changes: '{pendingChangesCount} Änderungen speichern'
    }
  },
  edit_construction_site: {
    title: 'Baustelle',
    help: 'Ändern Sie Adresse & Titelbild.',
    drag_files_to_upload: 'Ziehen Sie ein .jpg oder .png Bild in diesen Bereich um dieses als neues Titelbild zu setzen.',
    actions: {
      save: 'Änderungen speichern'
    }
  },
  edit_map_files: {
    title: 'Bauplanversionen',
    help: 'Ordnen Sie die Bauplanversionen einem Bauplan zu.',
    drag_files_to_upload: 'Ziehen Sie .pdf Dateien in diesen Bereich um diese als neue Bauplanversionen hinzuzufügen',
    performing_upload_check: 'Upload wird geprüft...',
    identical_content_than: 'Der Inhalt dieser Datei ist identitisch zu {files}.',
    identical_name: 'Eine Bauplanversion mit diesem Namen existiert bereits. Die Bauplanversion wird zu {new_name} umbenannt.',
    upload_active: 'wird hochgeladen... ({percentage}%)',
    actions: {
      abort_upload: 'Hochladen abbrechen',
      confirm_upload: 'Fortfahren'
    }
  },
  edit_craftsmen: {
    help: 'Aktualisieren Sie die Daten der Handwerker.',
    actions: {
      add_craftsman: 'Handwerker hinzufügen',
      save_changes: '{pendingChangesCount} Änderungen speichern',
      show_import: 'Aus Excel importieren',
      hide_import: 'Import verstecken'
    },
    defaults: {
      contact_name: '',
      email: '',
      company: '',
      trade: ''
    },
    placeholders: {
      contact_name: 'Max Muster',
      email: 'max@musterag.ch',
      company: 'Muster AG',
      trade: 'Elektriker'
    }
  },
  import_craftsmen: {
    title: 'Importieren',
    help: 'Importieren Sie die Handwerker aus einem Excel',
    copy_paste_from_excel: 'Markieren Sie alle gewünschten Zellen (inklusive Überschriften) im Excel und drücken Sie Ctrl-C zum kopieren. Fügen Sie die Auswahl dann in das Textfeld unten mit Ctrl-V ein.',
    copy_paste_area_placeholder: 'Hier Excel Felder hineinkopieren',
    content_types_valid: 'Alle benötigten Felder wurden zugeordnet.',
    invalid_content_types: 'Bitte korrigieren Sie die Zuordnungen für {invalidContentTypes}. Jeder Typ muss genau einmal zugeordnet werden.',
    change_preview: 'Änderungsvorschau',
    changes_detected: 'Es wurden Änderungen entdeckt. Bestätigen Sie diese mit einem Klick auf "Ausführen".',
    no_more_changes_detected: 'Es wurden keine weiteren Änderungen entdeckt. Sie können den Import nun schliessen.',
    content_types: {
      email: 'E-Mail',
      company: 'Firma',
      contact_name: 'Kontaktperson',
      trade: 'Gewerbe / Arbeitsgattung'
    },
    changes: {
      title: 'Änderung',
      add: 'wird erstellt',
      update: 'wird aktualisiert',
      remove: 'wird entfernt'
    },
    actions: {
      apply_import: 'Ausführen',
      abort: 'Abbrechen',
      close: 'Schliessen'
    }
  },
  set_automatically: 'automatisch festlegen',
  issue_count: 'Anzahl Pendenzen'
};
