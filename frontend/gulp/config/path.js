import * as nodePath from 'path';
const rootFolder = nodePath.basename(nodePath.resolve());

const buildFolder = `./dist`;
const srcFolder = `./src`;

export const path = {
    build: {
        js: `${buildFolder}/js/`,
        css: `${buildFolder}/css/`,
        files: `${buildFolder}/files/`,
    },
    src: {
        js: `${srcFolder}/js/**/*.js`,
        scss: `${srcFolder}/scss/**/monster.scss`,
        d_scss: `${srcFolder}/scss/**/theme-dark.scss`,
        l_scss: `${srcFolder}/scss/**/theme-light.scss`,
        files: `${srcFolder}/files/**/*.*`,
        normalize: `${srcFolder}/css/normalize.css`,
        bootstrap_css: `${srcFolder}/../node_modules/bootstrap/dist/css/bootstrap.min.css`,
        bootstrap_js: `${srcFolder}/../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js`,
        overlayscrollbars_css: `${srcFolder}/../node_modules/overlayscrollbars/styles/overlayscrollbars.min.css`,
        overlayscrollbars_js: `${srcFolder}/../node_modules/overlayscrollbars/browser/overlayscrollbars.browser.es5.min.js`,
    },
    watch: {
        js: `${srcFolder}/js/**/*.js`,
        js_modules: `${srcFolder}/modules/js/**/*.js`,
        scss: `${srcFolder}/scss/**/*.scss`,
        files: `${srcFolder}/files/**/*.*`,
        d_scss: `${srcFolder}/scss/**/theme-dark.scss`,
        l_scss: `${srcFolder}/scss/**/theme-light.scss`,
    },
    clean: buildFolder,
    buildFolder: buildFolder,
    srcFolder: srcFolder,
    rootFolder: rootFolder,
    ftp: ``
}