import $ from 'jquery';


export default function getFormGroup (fieldId, context) {
    return $(`#sonata-ba-field-container-${fieldId}`, context || document);
}
