import gulp from "gulp";
import concat from 'gulp-concat';
import minify from 'gulp-minify';

export const js = () => {
    return gulp.src(app.path.src.js)
        .pipe(concat('monster.js'))
        .pipe(minify())
        .pipe(gulp.dest(app.path.build.js))
    }