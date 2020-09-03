/**
 *
 * @type {function(...[*]): string}
 * Returns a new GET request params string from an object with a new query string and an old query string (optional)
 */
let buildGetRequestParams = ((...args) => {

    let old_query, new_query;
    let params = new Map();
    let params_strings = [];
    let symbol_and, symbol_equal;
    args[0]['symbol_and'] ? (symbol_and = args[0]['symbol_and']) : (symbol_and = `&`);
    args[0]['symbol_equal'] ? (symbol_equal = args[0]['symbol_equal']) : (symbol_equal = `=`);

    // Get old params
    args[0]['old_query'] ? (old_query = args[0]['old_query']) : (old_query = window.location.search.substr(1));
    old_query && (old_query = old_query.split(symbol_and));
    old_query.length && old_query.forEach((element, index) => {
        if (element) {
            let el = element.split(symbol_equal, 2);
            params.set(el[0], el[1]);
        }
    });

    // Get new params
    args[0]['new_query'] && (new_query = args[0]['new_query'].split(symbol_and));
    new_query.length && new_query.forEach((element, index) => {
        if (element) {
            let el = element.split(symbol_equal, 2);
            params.set(el[0], el[1]);
        }
    });

    // Join names and values of params
    params.size && params.forEach((element, index) => {
        params_strings.push(index + symbol_equal + element);
    });

    return params_strings.join(symbol_and);
});

/**
 *
 * @type {function(*=): string}
 * Reload page with new params
 */
let reloadPageWithGetParams = (params => {

    return window.location.search = buildGetRequestParams({
        new_query: params,
        old_query: window.location.search.substr(1),
    });
});

/**
 *
 * @type
 * BX-Panel {
 */
document.addEventListener(`DOMContentLoaded`, event => {
    const path = document.location.pathname;
    const isAdminPath = RegExp('^\/bitrix\/admin\/(.*)').test(path);
    if (!isAdminPath) {
        (el => {
            if (el === undefined || el === null) {
                return;
            }

            el.parentElement.style.setProperty(`display`, `block`);
            el.parentElement.style.setProperty(`transition`, `all 0s`);
            el.style.setProperty(`position`, `fixed`, `important`);
            el.style.setProperty(`transition`, `all 0s`);

            if (BX.getCookie(el.id + `-position-top`) !== undefined
                && BX.getCookie(el.id + `-position-top`) <= document.documentElement.clientHeight - el.offsetHeight
                && BX.getCookie(el.id + `-position-top`) >= 0) {
                el.style.setProperty(`top`, BX.getCookie(el.id + `-position-top`) + `px`);
            }
            if (BX.getCookie(el.id + `-position-left`) !== undefined
                && BX.getCookie(el.id + `-position-left`) <= document.documentElement.clientWidth - el.offsetWidth
                && BX.getCookie(el.id + `-position-left`) >= 0) {
                el.style.setProperty(`left`, BX.getCookie(el.id + `-position-left`) + `px`);
            }

            el.onmousedown = e => {
                let start_pos_x = e.clientX;
                let start_pos_y = e.clientY;
                document.onmouseup = () => {
                    document.onmouseup = null;
                    document.onmousemove = null;
                    return false;
                };
                document.onmousemove = e => {
                    let new_pos_x = start_pos_x - e.clientX;
                    let new_pos_y = start_pos_y - e.clientY;
                    start_pos_x = e.clientX;
                    start_pos_y = e.clientY;
                    if (el.offsetTop - new_pos_y <= document.documentElement.clientHeight - el.offsetHeight
                        && el.offsetTop - new_pos_y >= 0) {
                        el.style.setProperty(`top`, (el.offsetTop - new_pos_y) + `px`);
                        BX.setCookie(el.id + `-position-top`, (el.offsetTop - new_pos_y), {
                            expires: 3600 * 24,
                            path: `/`
                        });
                    }
                    if (el.offsetLeft - new_pos_x <= document.documentElement.clientWidth - el.offsetWidth
                        && el.offsetLeft - new_pos_x >= 0) {
                        el.style.setProperty(`left`, (el.offsetLeft - new_pos_x) + `px`);
                        BX.setCookie(el.id + `-position-left`, (el.offsetLeft - new_pos_x), {
                            expires: 3600 * 24,
                            path: `/`
                        });
                    }
                    return false;
                };
                return false;
            };
        })(document.getElementById(`bx-panel`));
    }
});
