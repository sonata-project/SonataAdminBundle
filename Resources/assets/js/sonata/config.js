import merge from 'sonata/util/merge';

const {Sonata} = window;

const defaults = {
    confirmExit: true,
    useSelect2: true,
    useStickyForms: true,
};

export default merge({}, defaults, Sonata.config || {});
