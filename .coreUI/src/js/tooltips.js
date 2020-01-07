/* global coreui */

/**
 * --------------------------------------------------------------------------
 * CoreUI Free Boostrap Admin Template (v3.0.0-beta.1): tooltips.js
 * Licensed under MIT (https://coreui.io/license)
 * --------------------------------------------------------------------------
 */

document.querySelectorAll('[data-toggle="tooltip"]').forEach(element => {
  // eslint-disable-next-line no-new
  new coreui.Tooltip(element)
})
