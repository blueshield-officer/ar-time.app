import dartSass from 'sass';
import gulpSass from 'gulp-sass';
import rename from 'gulp-rename'

import cleanCSS from 'gulp-clean-css';
import webpcss from 'gulp-webpcss';
import autoprefixer from 'gulp-autoprefixer';
import groupCssMediaQueries from 'gulp-group-css-media-queries';

const sass = gulpSass(dartSass);

function compile (files) {
    return files
        .pipe(app.plugins.plumber(
            app.plugins.notify.onError({
                title: 'SCSS',
                message: 'Error: <% error.message %>',
            })))
        .pipe(app.plugins.replace(/@img\//g, 'frontend/assets/img/'))
        .pipe(sass({
            outputStyle: 'expanded',
            quietDeps: true  // Suppress deprecation warnings from dependencies
        }))
        .pipe(groupCssMediaQueries())
        .pipe(webpcss(
            {
                webpClass: '.webp',
                noWebpClass: '.no-webp',
            }
        ))
        .pipe(autoprefixer({
            grid: true,
            overrideBrowserslist: ['last 3 versions'],
            cascade: true,
        }))
        // Uncomment for uncompressed css
        //.pipe(app.gulp.dest(app.path.build.css))
        .pipe(cleanCSS())
        .pipe(rename({
            extname: '.min.css',
        }))
        .pipe(app.gulp.dest(app.path.build.css))
}
export const scss = () => {
    console.log(app.path.src.scss); // Check if this is correct
    return compile(app.gulp.src(app.path.src.scss, { sourcemaps: true }))
}

export const d_theme_scss = () => {
    console.log(app.path.src.scss); // Check if this is correct
    return compile(app.gulp.src(app.path.src.d_scss, { sourcemaps: true }))
}
export const l_theme_scss = () => {
    console.log(app.path.src.scss); // Check if this is correct
    return compile(app.gulp.src(app.path.src.l_scss, { sourcemaps: true }))
}