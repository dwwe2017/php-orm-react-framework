#!/usr/bin/env node
'use strict'

const fs = require('fs')
const glob = require('glob')
const path = require('path')
const mkdirp = require('mkdirp')
const pug = require('pug')
const vendorsInjector = require('@coreui/vendors-injector')
const beautify = require('js-beautify').html
const jsbOptions = {
  /* eslint-disable camelcase */
  indent_size: 2,
  indent_inner_html: true,
  unformatted: [''],
  content_unformatted: ['textarea'],
  extra_liners: ['']
  /* eslint-enable camelcase */
}
const argv = require('minimist')(process.argv.slice(2), {
  boolean: ['injectVendors']
})
console.dir(argv)
const { src } = argv
const { dest } = argv
const injectVendors = argv.injectVendors ? argv.injectVendors : false

const { basename, dirname, resolve } = path
const extension = path.extname

// Get all pug files
const getAllFiles = src => {
  // const cwd = 'pug/'
  const pattern = `${src}/**/*.pug`
  const options = {
    ignore: `${src}/_*/**`
  }
  return new glob.sync(pattern, options)
}

const isPug = filename => extension(filename) === '.pug'

const compile = (filename, basedir) => {
  const levels = basedir.split(`${path.sep}`).filter(el => el !== '').length
  // eslint-disable-next-line unicorn/consistent-function-scoping
  const base = levels => {
    let path = './'
    while (levels > 0) {
      levels -= 1
      path += '../'
    }

    return path
  }

  const fn = pug.compileFile(filename, {
    basedir: './pug/',
    pretty: true
  })
  const html = fn({
    base: base(levels)
  })
  return html
}

const checkPath = (src, dest, injectVendors) => {
  // Check if path is file or directory
  if (fs.statSync(src).isDirectory()) {
    const files = getAllFiles(src)
    files.forEach(file => {
      if (isPug(file)) {
        compilePugToHtml(resolve(file), dest, injectVendors)
      }
    })
  } else if (isPug(src)) {
    compilePugToHtml(resolve(src), dest, injectVendors)
  }
}

// Build html files
const compilePugToHtml = (src, dest, injectVendors) => {
  const dir = dirname(src).replace('pug', '')
  const file = basename(src).replace('.pug', '.html')
  const relative = path.relative(resolve(__dirname, '..'), dir)
  let html = compile(src, `${relative}`)

  mkdirp.sync(resolve(__dirname, '..', dest, relative))

  if (injectVendors === true) {
    html = vendorsInjector(html)
  }

  fs.writeFile(resolve(__dirname, '..', dest, relative, file), beautify(html, jsbOptions), err => {
    if (err) {
      throw err
    }

    console.log(`${resolve(__dirname, '..', dest, relative, file)} file was saved!`)
  })
}

checkPath(src, dest, injectVendors)
