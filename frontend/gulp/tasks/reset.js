import {deleteAsync} from 'del';
import gulp from "gulp";
export const reset = () => {
    return deleteAsync(app.path.clean);
}
// export const reset = () => {
//     return deleteAsync(app.path.clean,
//         '!js/*bootstrap*.js',
//         '!js/*overlayscrollbars*.js',
//         '!css/*bootstrap*.css',
//         '!css/*overlayscrollbars*.css'
//         );
// }