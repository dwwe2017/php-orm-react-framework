/**
* --------------------------------------------------------------------------
* CoreUI Pro Boostrap Admin Template (3.0.0-beta.1): toasts.js
* License (https://coreui.io/license)
* --------------------------------------------------------------------------
*/
/* eslint-disable no-magic-numbers */
$('.toast').toast('show')
$('#toast-1').on('hidden.coreui.toast', () => {
  setTimeout(() => {
    $('#toast-1').toast('show')
  }, 3000)
})
$('#toast-2').on('hidden.coreui.toast', () => {
  setTimeout(() => {
    $('#toast-2').toast('show')
  }, 2500)
})
