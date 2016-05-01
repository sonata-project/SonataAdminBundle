import raf from './index';

export default fn => new Promise(resolve => raf(() => resolve(fn())));
