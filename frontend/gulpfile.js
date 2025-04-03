// base module
import gulp from 'gulp';
// path import
import { path } from './gulp/config/path.js';
// plugins import
import { plugins } from './gulp/config/plugins.js';

global.app = {
    path: path,
    gulp: gulp,
    plugins: plugins
}

// tasks import
import { copy } from "./gulp/tasks/copy.js"
import { reset } from "./gulp/tasks/reset.js"
import {scss, l_theme_scss, d_theme_scss} from "./gulp/tasks/scss.js"
import { js } from "./gulp/tasks/js.js"
import { js_sw } from "./gulp/tasks/js_modules.js"
import {normalize, bootstrap_js, bootstrap_css, overlayscrollbars_css, overlayscrollbars_js} from "./gulp/tasks/presets.js"


// Watcher on file changes
function  watcher() {
    gulp.watch(path.watch.files, copy)
    gulp.watch(path.watch.scss, scss)
    gulp.watch(path.watch.l_scss, l_theme_scss)
    gulp.watch(path.watch.d_scss, d_theme_scss)
    gulp.watch(path.watch.js, js)
    gulp.watch(path.watch.js_modules, js_sw)
}

const mainTasks = gulp.parallel(
    copy,
    scss,
    js,
    js_sw,
    bootstrap_js, bootstrap_css,
    overlayscrollbars_js, overlayscrollbars_css,
    normalize,
    l_theme_scss, d_theme_scss);

// Scenario builds of tasks
const dev = gulp.series(reset, mainTasks, watcher);

// Scenario complete by default
gulp.task('default', dev);