//
// Events documentation
// ----------------------------------------------------------------------------------------------------------------


//
// sonata:domready
// --------------------------------------------------------
/**
 * Fires whenever sonata loads an html page via an AJAX request.
 * @see sonataDomReadyCallback
 *
 * @event sonata:domready
 * @type {jQuery.Event}
 * @property {jQuery} target The root element of the newly inserted DOM.
 */
/**
 * @callback sonataDomReadyCallback
 * @param {jQuery.Event} event
 */

//
// sonata:list-item-*
// --------------------------------------------------------
/**
 * Fires before an admin list item is shown in a dialog.
 * Cancellable by calling event.preventDefault()
 * @see sonataListItemShowCallback
 *
 * @event sonata:list-item-show
 * @type {jQuery.Event}
 * @property {jQuery} target The list field.
 */
/**
 * @callback sonataListItemShowCallback
 * @param {jQuery.Event} event
 * @param {string} objectId The identifier of the shown entity.
 * @param {string} url The url about to be shown in the dialog.
 */

/**
 * Fires before an admin list item is removed.
 * Cancellable by calling event.preventDefault()
 * @see sonataListItemDeleteCallback
 *
 * @event sonata:list-item-delete
 * @type {jQuery.Event}
 * @property {jQuery} target The list field (the table row in list mode, or the mosaic tile).
 */

/**
 * Fires after an admin list item is removed.
 * Cancellable by calling event.preventDefault(), e.g. to override the default exit animation.
 * @see sonataListItemDeleteCallback
 *
 * @event sonata:list-item-delete
 * @type {jQuery.Event}
 * @property {jQuery} target The list field (the table row in list mode, or the mosaic tile).
 */

/**
 * @callback sonataListItemDeleteCallback
 * @param {jQuery.Event} event
 * @param {string} objectId The identifier of the entity to remove.
 */

//
// sonata:association-update
// --------------------------------------------------------

/**
 * Fires after the value of an association was updated,
 * in a `sonata_type_model` or `sonata_type_model_list` form.
 *
 * @event sonata:association-update
 * @type {jQuery.Event}
 * @property {jQuery} target The form field that was updated.
 * @see sonataAssociationUpdateCallback
 */

/**
 * @callback sonataAssociationUpdateCallback
 * @param {jQuery.Event} event
 * @param {string} objectId The id of the associated object. Can be the empty string if association was deleted.
 * @param {FieldDescriptionType} fieldDescription
 */


//
// sonata:collection-*
// --------------------------------------------------------

/**
 * Fires when a new empty row is added to a `sonata_type_collection` form.
 *
 * Cancellable by calling event.preventDefault()
 *
 * @event sonata:collection-item-add
 * @type {jQuery.Event}
 * @property {jQuery} target The form widget that will receive the new field.
 * @see sonataCollectionUpdateCallback
 */

/**
 * Fires when a new empty row has been added to a `sonata_type_collection` form.
 *
 * @event sonata:collection-item-added
 * @type {jQuery.Event}
 * @property {jQuery} target The field that was just added.
 * @see sonataCollectionUpdateCallback
 */
/**
 * @callback sonataCollectionUpdateCallback
 * @param {jQuery.Event} event
 * @param {FieldDescriptionType} fieldDescription
 */


//
// sonata:native-collection-*
// --------------------------------------------------------
/**
 * Fires before a new item is added to a `sonata_type_native_collection` form.
 *
 * Cancellable by calling event.preventDefault()
 *
 * @event sonata:native-collection-item-add
 * @type {jQuery.Event}
 * @property {jQuery} target The form widget that will receive the new item.
 * @see sonataNativeCollectionItemAddCallback
 */
/**
 * Fires after a new item was added to a `sonata_type_native_collection` form.
 *
 * @event sonata:native-collection-item-added
 * @type {jQuery.Event}
 * @property {jQuery} target The form widget that will receive the new item.
 * @see sonataNativeCollectionItemAddCallback
 */
/**
 * @callback sonataNativeCollectionItemAddCallback
 * @param {jQuery.Event} event
 * @param {jQuery} newRow The new row
 */


/**
 * Fires before a new item is removed from a `sonata_type_native_collection` form.
 *
 * Cancellable by calling event.preventDefault()
 *
 * @event sonata:native-collection-item-delete
 * @type {jQuery.Event}
 * @property {jQuery} target The form widget from which a row will be removed.
 * @see sonataNativeCollectionDeleteCallback
 */
/**
 * Fires after a new item was removed from a `sonata_type_native_collection` form.
 *
 * @event sonata:native-collection-item-deleted
 * @type {jQuery.Event}
 * @property {jQuery} target The form widget from which a row was removed.
 * @see sonataNativeCollectionDeleteCallback
 */
/**
 * @callback sonataNativeCollectionDeleteCallback
 * @param {jQuery.Event} event
 * @param {jQuery} row The removed row
 */

