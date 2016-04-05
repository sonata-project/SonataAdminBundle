import $ from 'jquery';


export default function getListItem (objectId, container = document) {
    return $(container).find(`.sonata-ba-list__item[data-object-id="${objectId}"]`);
}
