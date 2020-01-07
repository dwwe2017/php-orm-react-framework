/* global FullCalendar */

/**
 * --------------------------------------------------------------------------
 * CoreUI Pro Boostrap Admin Template (3.0.0-beta.1): calendar.js
 * Licensed under MIT (https://coreui.io/license)
 * --------------------------------------------------------------------------
 */

const calendarEl = document.getElementById('calendar')
const calendar = new FullCalendar.Calendar(calendarEl, {
  plugins: ['dayGrid']
})
calendar.render()
