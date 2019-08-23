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
        new_query : params,
        old_query : window.location.search.substr(1),
    });
});