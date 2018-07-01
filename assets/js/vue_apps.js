import Vue from 'vue'

Vue.config.productionTip = false;

// plugins
import VueI18n from 'vue-i18n'
import Vuex from 'vuex'
import Lightbox from 'vue-pure-lightbox'

Vue.use(VueI18n);
Vue.use(Vuex);
Vue.use(Lightbox);

// components
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import {library} from '@fortawesome/fontawesome-svg-core'

Vue.component('font-awesome-icon', FontAwesomeIcon);

import DispatchMessages from './localization/dispatch'
import Dispatch from './apps/dispatch'

if (document.getElementById("dispatch") != null) {
    const messagesDispatch = DispatchMessages;

    const lang = document.documentElement.lang.substr(0, 2);
    const i18nConfirm = new VueI18n({
        locale: lang,
        messages: messagesDispatch,
    });

    library.add(
        require('@fortawesome/fontawesome-pro-solid/faSortUp'),
        require('@fortawesome/fontawesome-pro-solid/faSortDown'),
        require('@fortawesome/fontawesome-pro-light/faSort')
    );

    new Vue({
        i18n: i18nConfirm,
        el: '#dispatch',
        template: '<Dispatch/>',
        components: {Dispatch}
    });
}


import FoyerMessages from './localization/foyer'
import Foyer from './apps/foyer'

if (document.getElementById("foyer") != null) {
    const messagesFoyer = FoyerMessages;

    const lang = document.documentElement.lang.substr(0, 2);
    const i18nConfirm = new VueI18n({
        locale: lang,
        messages: messagesFoyer,
    });

    library.add(
        require('@fortawesome/fontawesome-pro-solid/faSortUp'),
        require('@fortawesome/fontawesome-pro-solid/faSortDown'),
        require('@fortawesome/fontawesome-pro-light/faSort'),
        require('@fortawesome/fontawesome-pro-solid/faStar'),
        require('@fortawesome/fontawesome-pro-light/faStar')
    );

    new Vue({
        i18n: i18nConfirm,
        el: '#foyer',
        template: '<Foyer/>',
        components: {Foyer}
    });
}