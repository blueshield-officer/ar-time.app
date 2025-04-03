import gulp from "gulp";
import minify from "gulp-minify";
import path from "path";

export const js_sw = () => {
    return gulp.src(app.path.src.js_modules)
        .pipe(minify())
        .pipe(gulp.dest(app.path.build.root));
};