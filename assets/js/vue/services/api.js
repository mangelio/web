import axios from 'axios'
import Noty from 'noty'

const api = {
  setupErrorNotifications: function (instance) {
    axios.interceptors.response.use(
      response => {
        return response
      },
      error => {
        console.log(error)

        let errorText = error
        if (error.response) {
          const response = error.response
          if (response.data) {
            const data = response.data
            errorText = data['hydra:title'] + ': ' + data['hydra:description']
          } else {
            errorText = response.status + ': ' + response.statusText
          }
        }

        new Noty({
          text: instance.$t('messages.danger.request_failed') + ' (' + errorText + ')',
          theme: 'bootstrap-v4',
          type: 'error'
        }).show()

        return Promise.reject(error)
      }
    )
  },
  _writeAllProperties: function (source, target) {
    for (const prop in source) {
      if (Object.prototype.hasOwnProperty.call(source, prop) && Object.prototype.hasOwnProperty.call(target, prop)) {
        target[prop] = source[prop]
      }
    }
  },
  _getConstructionSiteIriFromLocation: function () {
    const urlArray = window.location.pathname.split('/')
    urlArray.splice(3)
    return '/api' + urlArray.join('/')
  },
  _getIdFromIri: function (object) {
    const iri = object['@id']
    return iri.substr(iri.lastIndexOf('/') + 1)
  },
  getMe: function (instance) {
    axios.get('/api/me')
      .then(response => {
        this._writeAllProperties(response.data, instance)
      })
  },
  getConstructionSite: function (instance) {
    const constructionSiteUrl = this._getConstructionSiteIriFromLocation()
    return new Promise(
      function (resolve) {
        axios.get(constructionSiteUrl)
          .then(response => {
            instance.constructionSite = response.data
            resolve()
          })
      }
    )
  },
  getConstructionSites: function (instance) {
    axios.get('/api/construction_sites')
      .then(response => {
        instance.constructionSites = response.data['hydra:member']
      })
  },
  getConstructionManagers: function (instance) {
    axios.get('/api/construction_managers')
      .then(response => {
        instance.constructionManagers = response.data['hydra:member']
      })
  },
  getIssuesSummary: function (instance, constructionSite) {
    const queryString = '?constructionSite=' + this._getIdFromIri(constructionSite)
    axios.get('/api/issues/summary' + queryString)
      .then(response => {
        instance.issuesSummary = response.data
      })
  },
  patch: function (instance, patch) {
    axios.patch(instance['@id'], patch, { headers: { 'Content-Type': 'application/merge-patch+json' } })
      .then(response => {
        this._writeAllProperties(response.data, instance)
      })
  },
  post: function (collectionUrl, post, collection) {
    axios.post(collectionUrl, post)
      .then(response => {
        collection.push(response.data)
      })
  }
}

export { api }
