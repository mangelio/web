import { createApp } from 'vue'
import { createI18n } from 'vue-i18n'
import moment from 'moment'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {
  library as FontawesomeLibrary,
  config as FontawesomeConfig
} from '@fortawesome/fontawesome-svg-core'
import {
  faPlus, faPencil, faTrash, // CRUD
  faUserAlt, faQuestionCircle, faEnvelopeOpen, // navigation
  faStar, faUserCheck, // issue states (toggle off)
  faFilter, faSort, // table
  faUser // craftsman link
} from '@fortawesome/pro-light-svg-icons'
import {
  faPlusCircle as faPlusCircleRegular, faDotCircle as faDotCircleRegular, // issue icons
  faExclamationCircle as faExclamationCircleRegular, faCheckCircle as faCheckCircleRegular // issue icons
} from '@fortawesome/pro-regular-svg-icons'

import {
  faStar as faStarSolid, faUserCheck as faUserCheckSolid, // issue states (toggle on)
  faFilter as faFilterSolid, faSearch as faSearchSolid, faSortUp as faSortUpSolid, faSortDown as faSortDownSolid // table
} from '@fortawesome/pro-solid-svg-icons'
import '@fortawesome/fontawesome-svg-core/styles.css'

// languages
import de from './localization/de.json'
import it from './localization/it.json'

// directives
import { clickOutside, focus } from './services/directives'

// views
import Dashboard from './Dashboard'
import Dispatch from './Dispatch'
import Edit from './Edit'
import Foyer from './Foyer'
import Register from './Register'
import Resolve from './Resolve'
import Switch from './Switch'

// settings
const locale = document.documentElement.lang.substr(0, 2)

// configure fontawesome
FontawesomeConfig.autoAddCss = false
FontawesomeLibrary.add(
  faPlus, faPencil, faTrash,
  faUserAlt, faQuestionCircle, faEnvelopeOpen,
  faStar, faUserCheck,
  faFilter, faSort,
  faUser,
  faPlusCircleRegular, faDotCircleRegular, faExclamationCircleRegular, faCheckCircleRegular,
  faStarSolid, faUserCheckSolid,
  faFilterSolid, faSearchSolid, faSortUpSolid, faSortDownSolid
)

// configure moment
moment.locale(locale)

// configure i18n
const i18n = createI18n({
  locale,
  fallbackLocale: 'de',
  messages: {
    de,
    it
  }
})

// configure vue
function createVue (app) {
  const vue = createApp(app)

  vue.config.productionTip = false
  vue.use(i18n)
  vue.component('FontAwesomeIcon', FontAwesomeIcon)
  vue.directive('click-outside', clickOutside)
  vue.directive('focus', focus)

  return vue
}

if (document.getElementById('dashboard') != null) {
  createVue(Dashboard)
    .mount('#dashboard')
}

if (document.getElementById('dispatch') != null) {
  createVue(Dispatch)
    .mount('#dispatch')
}

if (document.getElementById('edit') != null) {
  createVue(Edit)
    .mount('#edit')
}

if (document.getElementById('foyer') != null) {
  createVue(Foyer)
    .mount('#foyer')
}

if (document.getElementById('register') != null) {
  createVue(Register)
    .mount('#register')
}

if (document.getElementById('resolve') != null) {
  createVue(Resolve)
    .mount('#resolve')
}

if (document.getElementById('switch') != null) {
  createVue(Switch)
    .mount('#switch')
}
