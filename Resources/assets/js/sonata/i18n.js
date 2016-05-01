import merge from 'sonata/util/merge';

const {Sonata} = window;

const defaults = {
    confirmExit: 'You have unsaved changes. Do you really want to leave ?',
    loadingInformation: 'Loading...',
};

export default merge({}, defaults, Sonata.i18n || {});
