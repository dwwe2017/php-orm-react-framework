/* global google */
/**
 * --------------------------------------------------------------------------
 * CoreUI Pro Boostrap Admin Template (3.0.0-beta.1): google-maps.js
 * Licensed under MIT (https://coreui.io/license)
 * --------------------------------------------------------------------------
 */

/* eslint-disable no-magic-numbers, no-unused-vars */
const InitMap = () => {
  const locations = [
    {
      lat: 37.431489,
      lng: -122.163719,
      label: 'S',
      draggable: false,
      title: 'Stanford',
      www: 'https://www.stanford.edu/'
    },
    {
      lat: 37.394694,
      lng: -122.150333,
      label: 'T',
      draggable: false,
      title: 'Tesla',
      www: 'https://www.tesla.com/'
    },
    {
      lat: 37.331681,
      lng: -122.030100,
      label: 'A',
      draggable: false,
      title: 'Apple',
      www: 'https://www.apple.com/'
    },
    {
      lat: 37.484722,
      lng: -122.148333,
      label: 'F',
      draggable: false,
      title: 'Facebook',
      www: 'https://www.facebook.com/'
    }
  ]
  const map = new google.maps.Map(document.getElementById('map'), {
    center: {
      lat: 37.431489,
      lng: -122.163719
    },
    zoom: 11
  })
  const markers = locations.map((location, i) => {
    const contentString = `<a href="${location.www}" target="_blank"><strong>${location.title}</strong></a>`
    const infoWindow = new google.maps.InfoWindow({
      content: contentString,
      maxWidth: 200
    })
    const marker = new google.maps.Marker({
      position: location,
      label: location.label,
      map,
      title: location.title,
      contentString
    })
    marker.addListener('click', () => {
      infoWindow.open(map, marker)
    })
    return marker
  })
}

if (window.google && window.google.maps) {
  InitMap()
}
