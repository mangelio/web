export default {
  register: {
    name: 'Pendenzen',
    status_actions: {
      open: 'Offen',
      overdue: 'Frist überschritten',
      to_inspect: 'Zur Inspektion',
      marked: 'Markiert'
    }
  },
  dialog: {
    new_issues_in_foyer: 'Eine neue Pendenz | {count} neue Pendenzen',
    add_to_register: 'Jetzt zum Verzeichnis hinzufügen'
  },
  feed: {
    name: 'Feed',
    show_more: 'Mehr anzeigen',
    no_entries_yet: 'Keine Aktivitäten auf der Baustelle zur Zeit. Fügen Sie Pendenzen hinzu, damit hier etwas angezeigt wird.',
    entries: {
      response_received: '{craftsman} hat eine Pendenz beantwortet. | {craftsman} hat {count} Pendenzen beantwortet.',
      visited_webpage: '{craftsman} hat die Pendenzen angeschaut.',
      overdue_limit: '{craftsman} hat die Frist vom %limit% bei {count} Pendenzen überschritten.'
    }
  },
  notes: {
    name: 'Notizen',
    no_entries_yet: 'Noch keine Notizen. Notieren Sie sich hier, was Sie ungern vergessen möchten.',
    actions: {
      add_new: 'Neu erfassen'
    }
  }
};
