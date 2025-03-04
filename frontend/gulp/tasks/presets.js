import cleanCSS from "gulp-clean-css";
import rename from "gulp-rename";

export const bootstrap_js = () => {
    return app.gulp.src(app.path.src.bootstrap_js)
        .pipe(app.gulp.dest(app.path.build.js))
}

export const bootstrap_css = () => {
    return app.gulp.src(app.path.src.bootstrap_css)
        .pipe(app.gulp.dest(app.path.build.css))
}

export const overlayscrollbars_css = () => {
    return app.gulp.src(app.path.src.overlayscrollbars_css)
        .pipe(app.gulp.dest(app.path.build.css))
}

export const overlayscrollbars_js = () => {
    return app.gulp.src(app.path.src.overlayscrollbars_js)
        .pipe(app.gulp.dest(app.path.build.js))
}

export const normalize = () => {
    return app.gulp.src(app.path.src.normalize)
        .pipe(cleanCSS())
        .pipe(rename({
            extname: '.min.css',
        }))
        .pipe(app.gulp.dest(app.path.build.css))
}