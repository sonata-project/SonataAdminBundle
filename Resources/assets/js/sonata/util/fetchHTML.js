import ajaxPromise from './ajaxPromise';

export default url => ajaxPromise({url, dataType: 'html'});
