'use strict';

const
    {src, dest, watch, series, parallel} = require('gulp'),
    sass = require('gulp-sass')(require('sass')),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
    uglify = require('gulp-uglify'),
    babel = require('gulp-babel'),
    bump = require('gulp-bump'),
    semver = require('semver'),
    info = require('./package.json'),
    wpPot = require('gulp-wp-pot'),
    touch = require('gulp-touch-cmd'),
    header = require('gulp-header'),
    cssnano = require('cssnano'),
    concat = require('gulp-concat'),
    replace = require('gulp-replace'),
    rename = require('gulp-rename'),
    yargs = require('yargs');

/**
 * Template for banner to add to file headers
 */

var banner = [
    '/*!',
    'Plugin Name: <%= info.name %>',
    'Version: <%= info.version %>',
    'Requires at least: <%= info.compatibility.wprequires %>',
    'Tested up to: <%= info.compatibility.wptestedup %>',
    'Requires PHP: <%= info.compatibility.phprequires %>',
    'Description: <%= info.description %>',
    'Theme URI: <%= info.repository.url %>',
    'GitHub Theme URI: <%= info.repository.url %>',
    'GitHub Issue URL: <%= info.repository.issues %>',
    'Author: <%= info.author.name %>',
    'Author URI: <%= info.author.url %>',
    'License: <%= info.license %>',
    'License URI: <%= info.licenseurl %>',
    'Tags: <%= info.tags %>',
    'Text Domain: <%= info.textdomain %>',
    '*/'].join('\n');



function css() {
    var plugins = [
        autoprefixer(),
        cssnano()
    ];
  return src([info.source.sass + 'fau-studienangebot.scss'])
 //  .pipe(header(banner, { info : info }))
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss(plugins))
    .pipe(rename(info.maincss))
    .pipe(dest(info.target.css))
    .pipe(touch());
}


function cssdev() {
    var plugins = [
        autoprefixer()
    ];
  return src([info.source.sass + 'fau-studienangebot.scss'])
  // .pipe(header(banner, { info : info }))
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss(plugins))
    .pipe(rename(info.maincss))
    .pipe(dest(info.target.css))
    .pipe(touch());
}

function js() {
    return src([info.source.js + 'studienangebot.js'])
//    .pipe(concat(info.adminjs))
    .pipe(uglify())
    .pipe(rename(info.mainjs))
    .pipe(dest(info.target.js))
    .pipe(touch());
}

function patchPackageVersion() {
    var newVer = semver.inc(info.version, 'patch');
    return src(['./package.json', './' + info.main])
        .pipe(bump({
            version: newVer
        }))
        .pipe(dest('./'))
	.pipe(touch());
};
function prereleasePackageVersion() {
    var newVer = semver.inc(info.version, 'prerelease');
    return src(['./package.json', './' + info.main])
        .pipe(bump({
            version: newVer
        }))
	.pipe(dest('./'))
	.pipe(touch());;
};

function updatepot()  {
  return src("**/*.php")
  .pipe(
      wpPot({
        domain: info.textdomain,
        package: info.name,
	team: info.author.name,
	bugReport: info.repository.issues,
	ignoreTemplateNameHeader: true
 
      })
    )
  .pipe(dest(`languages/${info.textdomain}.pot`))
  .pipe(touch());
};


function startWatch() {
    watch('./src/sass/*.scss', css);
    watch('./src/js/*.js', js);
}

exports.css = css;
exports.js = js;
exports.dev = series(js, cssdev, prereleasePackageVersion);
exports.build = series(js, css, patchPackageVersion);
exports.pot = updatepot;

exports.default = startWatch;
