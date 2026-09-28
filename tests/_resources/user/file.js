const js = __('js');
const jsF = __f('%s js', 'a');
const jsT = __t('js %t', { '%t': 'one' });
const nJs = __n('js', 'jss', 1);
const nJsF = __nf('%d js', '%d jss', 1, 123);
const nJsT = __nt('%v js', '%v jss', 1, { '%v': 123 });
const pfs = Obj._s('Obj.s');
const pfn = Obj._n('Obj.n', 'Obj.n(s)', 1);