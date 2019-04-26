export default {
  actions: {
    save: 'speichern',
    save_all: 'alle speichern',
    remove: 'entfernen',
    remove_all: 'alle entfernen',
    remove_selected: 'ausgewählte entfernen',
    abort: 'abbrechen',
    print: 'drucken'
  },
  construction_site: {
    _name: 'Baustelle',
    name: 'Name',
    address: 'Adresse',
    street_address: 'Strasse / Nr.',
    locality: 'Ort',
    postal_code: 'PLZ',
    created_at: 'erstellt am'
  },
  craftsman: {
    _plural: 'Handwerker',
    name: 'Name',
    trade: 'Funktion',
    contact_name: 'Kontaktperson',
    email: 'E-Mail',
    company: 'Firma',
    not_responded_issues_count: 'Unbeantwortete Pendenzen',
    not_read_issues_count: 'Ungelesene Pendenzen',
    open_issues_count: 'Offene Pendenzen',
    next_response_limit: 'Nächste Frist',
    last_email_sent: 'Letzte versandte E-Mail',
    last_online_visit: 'Letzter Webseitenbesuch'
  },
  issue: {
    description: 'Beschreibung',
    craftsman: 'Firma',
    response_limit: 'Frist',
    map: 'Karte',
    status: 'Status',
    number: 'Nummer',
    trade: 'Funktion',
    no_response_limit: 'nicht gesetzt',
    no_craftsman: 'nicht gesetzt',
    craftsman_not_found: 'Firma nicht gefunden',
    status_values: {
      read: 'gelesen',
      registered: 'registriert',
      responded: 'beantwortet',
      reviewed: 'geschlossen'
    }
  },
  map: {
    _name: 'Bauplan',
    _plural: 'Baupläne',
    name: 'Name',
    open_issues_count: 'Offene Pendenzen',
    reviewed_issues_count: 'Erledigte Pendenzen',
    next_response_limit: 'Nächste Frist',
    parent: 'Zuordnung'
  },
  map_file: {
    _name: 'Bauplanversion',
    _plural: 'Bauplanversionen',
    name: 'Name',
    created_at: 'hinzugefügt am'
  },
  messages: {
    success: {
      removed_entries: 'Erfolgreich entfernt.',
      saved_changes: 'Änderungen gespeichert.'
    },
    danger: {
      unrecoverable: 'Es ist ein Fehler aufgetreten. Laden Sie die Seite neu, und versuchen Sie es erneut.'
    }
  },
  validation: {
    required: 'Dieses Feld kann nicht leer gelassen werden.'
  },
  view: {
    more: 'mehr',
    less: 'weniger'
  }
}